<?php
/**
 * Main API Hook
 *
 * For now, you can read here to understand how this plugin works.
 * @link(Github, http://bshaffer.github.io/oauth2-server-php-docs/)
 *
 * USER PASSWORD
 * curl -u 0mc4J1M7alZ4SuDu0kvHKZy3gHPIF2:eGoqK8rwfCSeGLIjfJELroKZr1HgKm "http://wordpress.dev/oauth/token" -d 'grant_type=password&username=admin&password=liamjack'
 *
 * CLIENT CREDENTIALS
 * curl -u 0mc4J1M7alZ4SuDu0kvHKZy3gHPIF2:eGoqK8rwfCSeGLIjfJELroKZr1HgKm http://wordpress.dev/oauth/token -d 'grant_type=client_credentials'
 *
 * AUTHORIZE AN ACCESS TOKEN
 * curl http://wordpress.dev/oauth/me -d 'access_token=6d39c203c65687c939c34f4c0d48dc7df799ebfc'
 *
 * GET ACCESS TOKEN WITH AUTHORIZATION CODE
 * curl -u 0mc4J1M7alZ4SuDu0kvHKZy3gHPIF2:eGoqK8rwfCSeGLIjfJELroKZr1HgKm http://wordpress.dev/oauth/token -d 'grant_type=authorization_code&code=fa742ce7d15012c061790088a056f04b1166abea'
 */
if ( defined("ABSPATH") === false ) {
	die("Illegal use of the API");
}

do_action('wo_before_api');
require_once dirname(__FILE__) . '/OAuth2/Autoloader.php';
OAuth2\Autoloader::register();

// Grab the options
$o = get_option("wo_options");
if ($o["enabled"] == 0) {
	do_action('wo_before_unavailable_error');
	$response = new OAuth2\Response(array('error' => 'temporarily_unavailable'));
	$response->send();
	exit;
}

global $wp_query;
$method = $wp_query->get("oauth");
$storage = new OAuth2\Storage\Wordpressdb();
$server = new OAuth2\Server($storage,
	array(
		'use_crypto_tokens' => false,
		'store_encrypted_token_string' => true,
		'use_openid_connect' => false,
		'id_lifetime' => 3600,
		'access_lifetime' => $o['access_token_lifetime'] == '' ? 3600 : $o['access_token_lifetime'],
		'refresh_token_lifetime' => $o['refresh_token_lifetime'] == '' ? 86400 : $o['refresh_token_lifetime'],
		'www_realm' => 'Service',
		'token_param_name' => 'access_token',
		'token_bearer_header_name' => 'Bearer',
		'enforce_state' => $o['enforce_state'] == '1' ? true : false,
		'require_exact_redirect_uri' => $o['require_exact_redirect_uri'] == '1' ? true : false,
		'allow_implicit' => $o['implicit_enabled'] == '1' ? true : false,
		'allow_credentials_in_request_body' => true,
		'allow_public_clients' => false,
		'always_issue_new_refresh_token' => true,
	));

/*
|--------------------------------------------------------------------------
| SUPPORTED GRANT TYPES
|--------------------------------------------------------------------------
|
| Authorization Code will always be on. This may be a bug or a f@#$ up on
| my end. None the less, these are controlled in the server settings page.
|
 */
if ($o['auth_code_enabled'] == '1') {
	$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
}
if ($o['client_creds_enabled'] == '1') {
	$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
}
if ($o['user_creds_enabled'] == '1') {
	$server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
}
if ($o['refresh_tokens_enabled'] == '1') {
	$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));
}

/*
|--------------------------------------------------------------------------
| DEFAULT SCOPES
|--------------------------------------------------------------------------
|
| For the time being, the plugin will not fully support scopes. This is where
| the scopes can be registered. This will be extended to be a filter in
| upcomming release. Modify at your own risk.. This will be wiped unpon
| a plugin update to newer versions.
|
 */
$defaultScope = 'basic';
$supportedScopes = array(
	'basic',
	'postonwall',
	'accessphonenumber',
);
$memory = new OAuth2\Storage\Memory(array(
	'default_scope' => $defaultScope,
	'supported_scopes' => $supportedScopes,
));
$scopeUtil = new OAuth2\Scope($memory);
$server->setScopeUtil($scopeUtil);

/*
|--------------------------------------------------------------------------
| TOKEN CATCH
|--------------------------------------------------------------------------
|
| The followng code is ran when a request is made to the server using the
| Authorization Code (implicit) Grant Type as well as request tokens
|
 */
if ($method == 'token') {
	do_action('wo_before_token_method');
	$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
}

/*
|--------------------------------------------------------------------------
| AUTHORIZATION CODE CATCH
|--------------------------------------------------------------------------
|
| The followng code is ran when a request is made to the server using the
| Authorization Code (not implicit) Grant Type.
|
| 1. Check if the user is logged in (redirect if not)
| 2. Validate the request (client_id, redirect_uri)
| 3. Create the authorization request using the authentication user's user_id
|
 */
if ($method == 'authorize') {
	$request = OAuth2\Request::createFromGlobals();
	$response = new OAuth2\Response();
	if (!$server->validateAuthorizeRequest($request, $response)) {
		$response->send();
		die;
	}
	do_action('wo_before_authorize_method');
	if (!is_user_logged_in()) {
		wp_redirect(wp_login_url(site_url() . $_SERVER['REQUEST_URI']));
		exit;
	}
	$server->handleAuthorizeRequest($request, $response, true, get_current_user_id());
	$response->send();
	exit;
}

/*
|--------------------------------------------------------------------------
| EXTENDABLE RESOURCE SERVER METHODS
|--------------------------------------------------------------------------
|
| Below this line is part of the developer API. Do not edit directly.
| Refer to the developer documentation for exstending the WordPress OAuth
| Server plugin core functionality.
|
| @todo Document and tighten up error messages. All error messages will soon be
| controlled through apply_filters so start planning for a filter error list to
| allow for developers to customize error messages.
|
 */
$ext_methods = apply_filters('wo_endpoints', null);
if (array_key_exists($method, $ext_methods)) {
	$response = new OAuth2\Response();
	if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
		$response->setError(400, 'invalid_request', 'Missinng or invalid paramter(s)');
		$response->send();
		die;
	}
	$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	if (is_null($token)) {
		$server->getResponse()->send();
		exit;
	}
	call_user_func_array($ext_methods[$method]['func'], array($token));
	exit;
}

// Loaner
exit;