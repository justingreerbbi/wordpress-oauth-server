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
	$keys['public'] = WOABSPATH . '/library/keys/id_rsa.pub';
	$keys['private'] = WOABSPATH . '/library/keys/id_rsa';

	return $keys;
}

/**
 * Tabs filter
 * Allows developers to extend the options page
 * @since 3.0.3
 *
 * @todo The tabs filter will work for adding additional tabs but we need to look into handling the save
 * method since it is not tied into the core and the core is hardcodeed. Maybe we need to add all tabs into
 * the filter and find a way to save the values.
 */
add_filter('wo_tabs', 'wo_extend_tabs');
function wo_extend_tabs($tabs) {
	return $tabs;
}

/**
 * Default Method Filter for the resource server API calls
 */
add_filter('wo_endpoints', 'wo_default_endpoints', 1);
function wo_default_endpoints() {
	$endpoints = array(
		'me' => array('func' => '_wo_method_me')
	);
	return $endpoints;
}

/**
 * DEFAULT ME METHOD - DO NOT REMOVE DIRECTLY
 * This is the default resource call "/oauth/me". Do not edit nor remove.
 */
function _wo_method_me($token = null) {

	/** 
	 * Added 3.0.2 to handle access tokens not asigned to user
	 */
	if (!isset($token['user_id']) || $token['user_id'] == 0) {
		$response = new OAuth2\Response();
		$response->setError(400, 'invalid_request', 'Missinng or invalid access token');
		$response->send();
		exit;
	}
	$user_id = &$token['user_id'];

	global $wpdb;
	$me_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}users WHERE ID=$user_id", ARRAY_A);

	/** prevent sensative data - makes me happy ;) */
	unset($me_data['user_pass']);
	unset($me_data['user_activation_key']);
	unset($me_data['user_url']);

	/**
	 * @since  3.0.5 
	 * OpenID Connect looks for the field "email".
	 * Sooooo. We shall provide it. (at least for Moodle)
	 */
	$me_data['email'] = $me_data['user_email'];

	$response = new OAuth2\Response($me_data);
	$response->send();
	exit;
}
