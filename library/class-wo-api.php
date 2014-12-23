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
 * PASSWORD
 * curl -u 63Ag2O9Sr0DHwZZQwDcxxOjEdP6AUh:30kEhntqgVrTCYTCOqf0XNOgs16zC0 "http://fancychatter.bbidev.com/oauth/token" -d 'grant_type=password&username=blackbird&password=liamjack'
 *
 * CLIENT CREDENTIALS
 * curl -u 63Ag2O9Sr0DHwZZQwDcxxOjEdP6AUh:30kEhntqgVrTCYTCOqf0XNOgs16zC0 http://fancychatter.bbidev.com/oauth/token -d 'grant_type=client_credentials'
 *
 * AUTHORIZE AN ACCESS TOKEN
 * curl http://fancychatter.bbidev.com/oauth/me -d 'access_token=6d39c203c65687c939c34f4c0d48dc7df799ebfc'
 *
 * GET ACCESS TOKEN WITH AUTHORIZATION CODE
 * curl -u 63Ag2O9Sr0DHwZZQwDcxxOjEdP6AUh:30kEhntqgVrTCYTCOqf0XNOgs16zC0 http://fancychatter.bbidev.com/oauth/token -d 'grant_type=authorization_code&code=fa742ce7d15012c061790088a056f04b1166abea'
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

$storage = new OAuth2\Storage\Wpo();
$server = new OAuth2\Server($storage,
	array(
    'use_crypto_tokens'        => false,
    'store_encrypted_token_string' => true,
    'use_openid_connect'       => false,
    'id_lifetime'              => 3600,
    'access_lifetime'          => 3600,
    'www_realm'                => 'Service',
    'token_param_name'         => 'access_token',
    'token_bearer_header_name' => 'Bearer',
    'enforce_state'            => false,
    'require_exact_redirect_uri' => true,
    'allow_implicit'           => true,
    'allow_credentials_in_request_body' => true,
    'allow_public_clients'     => false,
    'always_issue_new_refresh_token' => false,
));
		
/**
 * SET THE GRANT TYPES AVALIABLE FOR THE SERVER
 *
 * Supported Grant Types
 * - Client Credentials
 * - Authorization Code
 * - User Credentials
 * - Refresh Token
 *
 * Currently NOT Supported
 * - Jwt Bearer (Signed Access to server using certificates)
 */
$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
$server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));

// configure your available scopes
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
 *
 * @todo Not Working Just Yet. There is something with the expires. May be becuase the authorization code is 
 * not being stored. Check out Wpo object
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

	// if the user is not logged in, redirect them to the WP login screen
	if(!is_user_logged_in())
		wp_redirect(wp_login_url(site_url().$_SERVER['REQUEST_URI']));
	
	// display an authorization form
	if (empty($_POST)) {
	  exit('
	<form method="post">
	  <label>Do You Authorize TestClient?</label><br />
	  <input type="submit" name="authorized" value="yes">
	  <input type="submit" name="authorized" value="no">
	</form>');
	}

	// print the authorization code if the user has authorized your client
	$is_authorized = ($_POST['authorized'] === 'yes');
	$server->handleAuthorizeRequest($request, $response, $is_authorized);
	if ($is_authorized) {
	  // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
	  $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);
	}
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
	echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));
}