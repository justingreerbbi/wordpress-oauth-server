<?php
/**
 * Main API Hook
 *
 * For now, you can read here to understand how this plugin works. 
 * @link(Github, http://bshaffer.github.io/oauth2-server-php-docs/)
 *
 * @todo Add adittional layer of security to API allow for a generic firewall
 * @todo  Find better way to clean up headers from server
 *
 * USER PASSWORD
 * curl -u 9yJeF4nmXfJZvvqKCdgiR9YMTM2JVX:f5wZjb4Hy1Xh1tNsdpFtIxCGkwsmfo "http://wordpress.dev/oauth/token" -d 'grant_type=password&username=admin&password=liamjack'
 *
 * CLIENT CREDENTIALS
 * curl -u 9yJeF4nmXfJZvvqKCdgiR9YMTM2JVX:f5wZjb4Hy1Xh1tNsdpFtIxCGkwsmfo http://wordpress.dev/oauth/token -d 'grant_type=client_credentials'
 *
 * AUTHORIZE AN ACCESS TOKEN
 * curl http://wordpress.dev/oauth/me -d 'access_token=6d39c203c65687c939c34f4c0d48dc7df799ebfc'
 *
 * GET ACCESS TOKEN WITH AUTHORIZATION CODE
 * curl -u 9yJeF4nmXfJZvvqKCdgiR9YMTM2JVX:f5wZjb4Hy1Xh1tNsdpFtIxCGkwsmfo http://wordpress.dev/oauth/token -d 'grant_type=authorization_code&code=fa742ce7d15012c061790088a056f04b1166abea'
 */
if( defined("ABSPATH") === false )
	die("Illegal use of the API");

$o = get_option("wo_options");
if($o["enabled"] == 0)
{
	header('Content-Type: application/json');
	print_r(json_encode(array('error' => 'temporarily_unavailable')));
	exit;
}

global $wp_query;
$method = $wp_query->get("oauth");

/** Setup the Autoloader for the OAuth Server */
require_once(dirname(__FILE__).'/OAuth2/Autoloader.php');
OAuth2\Autoloader::register();

$storage = new OAuth2\Storage\Wordpressdb();
$server = new OAuth2\Server($storage,
array(
    'use_crypto_tokens'        => false,
    'store_encrypted_token_string' => true,
    'use_openid_connect'       => false,
    'id_lifetime'              => 3600, // 1 Hour - Crypto (not supported yet)
    'access_lifetime'          => 3600, // 1 Hour
    'refresh_token_lifetime'	 => 2419200, // 14 Days
    'www_realm'                => 'Service',
    'token_param_name'         => 'access_token',
    'token_bearer_header_name' => 'Bearer',
    'enforce_state'            => false,
    'require_exact_redirect_uri' => $o['require_exact_redirect_uri'] == '1' ? true:false,
    'allow_implicit'           => $o['implicit_enabled'] == '1' ? true:false,
    'allow_credentials_in_request_body' => true,
    'allow_public_clients'     => false,
    'always_issue_new_refresh_token' => false,
));
		
/** Set the enabled Grant Types */
if($o['auth_code_enabled'] == '1')
{
	//$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
}

if($o['client_creds_enabled'] == '1')
{
	$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
}

if($o['user_creds_enabled'] == '1')
{
	$server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
}

if($o['refresh_tokens_enabled'] == '1')
{
	$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));
}

/**
 * Configure Scopes
 * 
 * @todo Need to add a filter that does this. This way a developer could hook into the scopes without modifying
 * the core plugin.
 */
$defaultScope = 'basic';
$supportedScopes = array(
  'basic',
  'postonwall',
  'accessphonenumber'
);
$memory = new OAuth2\Storage\Memory(array(
  'default_scope' => $defaultScope,
  'supported_scopes' => $supportedScopes
));
$scopeUtil = new OAuth2\Scope($memory);
$server->setScopeUtil($scopeUtil);

/**
 * TOKEN ENDPOINT
 * @var [type]
 */
if($method == 'token')
{
	$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
}

/**
 * BROWSER AUTHORIZE
 * The code checks to see if the user is logged in and authorize. If the user is not logged in, the user is 
 * presented with the WP login screen. Upon successfull log in, the user then aknoledges wether it not to
 * authorize the requester access to thier information.
 */
if($method == 'authorize')
{
	$request = OAuth2\Request::createFromGlobals();
	$response = new OAuth2\Response();

	// validate the authorize request
	if (!$server->validateAuthorizeRequest($request, $response)) {
	    $response->send();
	    die;
	}

	/**
	 * @todo Add hook here to allow developers to redirect to a new page.
	 */
	if(!is_user_logged_in())
		wp_redirect(wp_login_url(site_url().$_SERVER['REQUEST_URI']));
	
	/**
	 * @todo When this is displayed the plugin needs to grab the clients name and all the scopes with
	 * explinations. For now I will turn this off and auto authorize the requests.
	 */
	/*
	if (empty($_POST)) {
	  exit('
	<form method="post">
	  <label>Do You Authorize TestClient?</label><br />
	  <input type="submit" name="authorized" value="yes">
	  <input type="submit" name="authorized" value="no">
	</form>');
	}
	$is_authorized = ($_POST['authorized'] === 'yes');
	*/

	$server->handleAuthorizeRequest($request, $response, true);
	$response->send();
}

/**
 * DEFAULT PROTECTED RESOURCE ENDPOINT
 * @var [type]
 */
if($method == 'me')
{
	if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
	    $server->getResponse()->send();
	    die;
	}
	$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	print_r($token);
	echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));
}