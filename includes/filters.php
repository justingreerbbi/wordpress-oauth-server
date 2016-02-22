<?php
/**
 * WordPress Mobile OAuth Filters
 * @author Justin Greer <justin@justin-greer.com>
 */


/**
 * WordPress OAuth Server Error Filter
 * @deprecated Schedule for removal. The PHP server handles all these now.
 */
add_filter("WO_API_Errors", "wo_api_error_setup", 1);
function wo_api_error_setup($errors) {
	$errors["invalid_access_token"] = "The access token is invalid or has expired";
	$errors["invalid_refresh_token"] = "The refresh token is invalid or has expired";
	$errors["invalid_credentials"] = "Invalid user credentials";

	return $errors;
}

/**
 * Server Key Locations Filter
 * @since  3.0.5
 */
add_filter('wo_server_keys', 'wo_server_key_location', 1);
function wo_server_key_location($keys) {
	$keys['public'] = WOABSPATH . '/library/keys/public_key.pem';
	$keys['private'] = WOABSPATH . '/library/keys/private_key.pem';
	return $keys;
}

/**
 * Tabs filter
 * Allows developers to extend the options page
 * @since 3.0.3
 *
 * @todo The tabs filter will work for adding additional tabs but we need to look into handling the save
 * method since it is not tied into the core and the core is hard coded. Maybe we need to add all tabs into
 * the filter and find a way to save the values.
 */
add_filter('wo_tabs', 'wo_extend_tabs');
function wo_extend_tabs ( $tabs ) {
	return $tabs;
}

/**
 * Default Method Filter for the resource server API calls
 *
 * @since  3.1.8 Endpoints now can accept public methods that bypass the token authorization
 */
add_filter('wo_endpoints', 'wo_default_endpoints', 1);
function wo_default_endpoints () {
	$endpoints = array(
		'me' => array(
			'func' => '_wo_method_me', 
			'public' => false 
		),
		'destroy' => array( 
			'func' => '_wo_method_destroy', 
			'public' => false 
		)
	);
	return $endpoints;
}

/**
 * Default Support Scopes
 *
 * Keep in mind that "basic" is automatically supported for the time being
 */
add_filter('wo_scopes', 'wo_default_scopes');
function wo_default_scopes () {
	$scopes = array(
		'openid',
		'profile',
		'email'
	);
	return $scopes;
}

/**
 * DEFAULT DESTROY METHOD
 * This method has been added to help secure installs that want to manually destroy sessions (valid access tokens).
 * @since  3.1.5
 */
function _wo_method_destroy ( $token = null ) {
	$access_token = &$token['access_token'];

	global $wpdb;
	$stmt = $wpdb->delete("{$wpdb->prefix}oauth_access_tokens", array( 'access_token' => $access_token ) );

	/** If there is a refresh token we need to remove it as well. */
	if( ! empty( $_REQUEST[ 'refresh_token' ] ) )
		$stmt = $wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array( 'refresh_token' => $_REQUEST['refresh_token'] ) );

	/** Prepare the return */
	$response = new OAuth2\Response( array(
		'status' =>   true,
		'description' => 'Session destroyed successfully'
		) );
	$response->send();
	exit;
}

/**
 * DEFAULT ME METHOD - DO NOT REMOVE DIRECTLY
 * This is the default resource call "/oauth/me". Do not edit or remove.
 */
function _wo_method_me ( $token = null ) {
	if ( ! isset( $token['user_id'] ) || $token['user_id'] == 0 ) {
		$response = new OAuth2\Response();
		$response->setError( 400, 'invalid_request', 'Missing or invalid access token' );
		$response->send();
		exit;
	}

	$user = get_user_by( 'id', $token['user_id'] );
	$me_data = (array) $user->data;

	unset( $me_data['user_pass'] );
	unset( $me_data['user_activation_key'] );
	unset( $me_data['user_url'] );

	/**
	 * @since  3.0.5 
	 * OpenID Connect looks for the field "email".
	 * Sooooo. We shall provide it. (at least for Moodle)
	 */
	$me_data['email'] = $me_data['user_email'];

	$response = new OAuth2\Response( $me_data );
	$response->send();
	exit;
}