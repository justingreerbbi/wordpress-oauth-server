<?php
/**
 * Main API Hook
 *
 * For now, you can read here to understand how this plugin works.
 * @link(Github, http://bshaffer.github.io/oauth2-server-php-docs/)
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

do_action( 'wo_before_api', array( $_REQUEST ) );
require_once dirname( __FILE__ ) . '/OAuth2/Autoloader.php';
OAuth2\Autoloader::register();

// Grab the options
$o = get_option("wo_options");
if ( 0 == $o["enabled"] ) {
	do_action('wo_before_unavailable_error');
	$response = new OAuth2\Response(array('error' => 'temporarily_unavailable'));
	$response->send();
	exit;
}

global $wp_query;
$method = $wp_query->get( 'oauth' );
$well_known = $wp_query->get( 'well-known' );
$storage = new OAuth2\Storage\Wordpressdb();
$config = array(
	'use_crypto_tokens' => false,
	'store_encrypted_token_string' => false,
	'use_openid_connect' => $o['use_openid_connect'] == '' ? false : $o['use_openid_connect'],
	'issuer' => site_url( null, 'https' ), // Must be HTTPS
	'id_lifetime' => $o['id_token_lifetime'] == '' ? 3600 : $o['id_token_lifetime'],
	'access_lifetime' => $o['access_token_lifetime'] == '' ? 3600 : $o['access_token_lifetime'],
	'refresh_token_lifetime' => $o['refresh_token_lifetime'] == '' ? 86400 : $o['refresh_token_lifetime'],
	'www_realm' => 'Service',
	'token_param_name' => 'access_token',
	'token_bearer_header_name' => 'Bearer',
	'enforce_state' => $o['enforce_state'] == '1' ? true : false,
	'require_exact_redirect_uri' => $o['require_exact_redirect_uri'] == '1' ? true : false,
	'allow_implicit' => $o['implicit_enabled'] == '1' ? true : false,
	'allow_credentials_in_request_body' => true, // Must be set to true for openID to work in most cases
	'allow_public_clients' => false,
	'always_issue_new_refresh_token' => true,
	'redirect_status_code' => 302
);
$server = new OAuth2\Server( $storage, $config );

/*
|--------------------------------------------------------------------------
| SUPPORTED GRANT TYPES
|--------------------------------------------------------------------------
|
| Authorization Code will always be on. This may be a bug or a f@#$ up on
| my end. None the less, these are controlled in the server settings page.
|
 */
$support_grant_types = array();
if ( '1' == $o['auth_code_enabled'] ) {
	$server->addGrantType( new OAuth2\GrantType\AuthorizationCode( $storage ) );
}
if ( '1' == $o['client_creds_enabled'] ) {
	$server->addGrantType(new OAuth2\GrantType\ClientCredentials( $storage ) );
}
if ( '1' == $o['user_creds_enabled'] ) {
	$server->addGrantType(new OAuth2\GrantType\UserCredentials( $storage ) );
}
if ( '1' == $o['refresh_tokens_enabled'] ) {
	$server->addGrantType( new OAuth2\GrantType\RefreshToken( $storage, $config ) );
}
if ( '1' == $o['use_openid_connect'] ) {
	$server->addGrantType( new OAuth2\OpenID\GrantType\AuthorizationCode( $storage, $config ) );
}

/*
|--------------------------------------------------------------------------
| DEFAULT SCOPES
|--------------------------------------------------------------------------
|
| Supported scopes can be added to the plugin by modifying the wo_scopes. 
| Until further notice, the default scope is 'basic'. Plans are in place to
| allow this scope to be adjusted.
|
 */
$defaultScope = 'basic';
$supportedScopes = apply_filters( 'wo_scopes', null, 20 );

$memory = new OAuth2\Storage\Memory( array(
	'default_scope' => $defaultScope,
	'supported_scopes' => $supportedScopes,
) );
$scopeUtil = new OAuth2\Scope( $memory );
$server->setScopeUtil( $scopeUtil );

/*
|--------------------------------------------------------------------------
| TOKEN CATCH
|--------------------------------------------------------------------------
|
| The following code is ran when a request is made to the server using the
| Authorization Code (implicit) Grant Type as well as request tokens
|
 */
if ( $method == 'token' ) {
	do_action( 'wo_before_token_method', array( $_REQUEST ) );
	$server->handleTokenRequest( OAuth2\Request::createFromGlobals() )->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| AUTHORIZATION CODE CATCH
|--------------------------------------------------------------------------
|
| The following code is ran when a request is made to the server using the
| Authorization Code (not implicit) Grant Type.
|
| 1. Check if the user is logged in (redirect if not)
| 2. Validate the request (client_id, redirect_uri)
| 3. Create the authorization request using the authentication user's user_id
|
*/
if ( $method == 'authorize' ) {
	do_action( 'wo_before_authorize_method', array( $_REQUEST ) );
	$request = OAuth2\Request::createFromGlobals();
	$response = new OAuth2\Response();
	if (! $server->validateAuthorizeRequest( $request, $response ) ) {
		$response->send();
		exit;
	}

	if (! is_user_logged_in() ) {
		wp_redirect( wp_login_url( $_SERVER['REQUEST_URI'] ) );
		exit;
	}

	$server->handleAuthorizeRequest($request, $response, true, get_current_user_id());
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| PUBLIC KEY
|--------------------------------------------------------------------------
|
| Presents the generic public key for signing.
|	@since 3.0.5
*/
if ( $well_known  == 'keys' ) {
	$keys = apply_filters( 'wo_server_keys', null);
	$publicKey = openssl_pkey_get_public( file_get_contents( $keys['public'] ) );
	$publicKey = openssl_pkey_get_details( $publicKey );
	$response = new OAuth2\Response( array(
		'keys' => array(
			array(
				'kty' => 'RSA',
				'alg' => 'RS256',
				'use' => 'sig',
				'n' =>  base64_encode( $publicKey['rsa']['n'] ),
				'e' =>  base64_encode( $publicKey['rsa']['e'] )
				)
			)
	));
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| OpenID Discovery
|--------------------------------------------------------------------------
|
*/
if ( $well_known == 'openid-configuration' ) {
	$openid_configuration = array(
		'issuer' => site_url( null, 'https' ),
	  'authorization_endpoint' => site_url( '/oauth/authorize' ),
	  'token_endpoint' => site_url( 'oauth/token' ),
	  'userinfo_endpoint' => site_url( '/oauth/me' ),
	  'jwks_uri' => site_url( '/.well-known/keys' ),
	  'response_types_supported' => array( 'code', 'id_token', 'token id_token', 'code id_token' ),
	  'subject_types_supported' => array( 'public' ),
		'id_token_signing_alg_values_supported' => array( 'RS256' )
	);
	$response = new OAuth2\Response( $openid_configuration );
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| EXTENDABLE RESOURCE SERVER METHODS
|--------------------------------------------------------------------------
|
| Below this line is part of the developer API. Do not edit directly.
| Refer to the developer documentation for extending the WordPress OAuth
| Server plugin core functionality.
|
| @todo Document and tighten up error messages. All error messages will soon be
| controlled through apply_filters so start planning for a filter error list to
| allow for developers to customize error messages.
|
*/
$ext_methods = apply_filters( "wo_endpoints", null );

// Check to see if the method exists in the filter
if ( array_key_exists( $method, $ext_methods ) ) {

	// If the method is is set to public, lets just run the method without
	if( isset( $ext_methods[$method]['public'] ) && $ext_methods[$method]['public'] ){
		call_user_func_array($ext_methods[$method]['func'], $_REQUEST);
		exit;
	}

	$response = new OAuth2\Response();
	if ( ! $server->verifyResourceRequest( OAuth2\Request::createFromGlobals() ) ) {
		$response->setError(400, 'invalid_request', 'Missing or invalid parameter(s)');
		$response->send();
		exit;
	}
	$token = $server->getAccessTokenData( OAuth2\Request::createFromGlobals() );
	if ( is_null( $token ) ) {
		$server->getResponse()->send();
		exit;
	}

	do_action('wo_endpoint_user_authenticated', array( $token ) );
	call_user_func_array( $ext_methods[$method]['func'], array( $token ) );

	exit;
}

/**
 * Server error response. End of line
 * @since 3.1.0
 */
$response = new OAuth2\Response();
$response->setError(400, 'invalid_request', 'Unknown request');
$response->send();
exit;