<?php
/**
 * Front end hook for OAuth2 Provider for WordPress
 * 
 * @author Justin Greer
 */
global $wp_query;

/**
* Require OAuth Storage
*/
require_once( dirname(__FILE__) . '/admin/IOAuth2Storage.php' );

/**
* @var Set the object
*/
$oauth = new OAuth2(new IOAuth2StorageWP());

/**
* @var Clean the method from the query up a bit if needed
*/
$method = $wp_query->get('oauth');
$allowed = array(
				'authorize', 		// Authorize a user
				'request_token',	// Request a Token
				'request_access',	// Request Access
				'login'				// This is for the authorization login screen
				);
				
	
/**
 * Check to make sure only parameters defined are used and nothing else
 */				
if (!in_array($method,$allowed)){
	header("Content-Type: application/json");
	header("Cache-Control: no-store");
	$error = json_encode(array('error' => 'Paramter method', 'error_description' => 'The method parameter is required and seems to be missing'));
	echo $error;
	exit;
	}
	
/**
* Check and run the right method based on the method passed in the query
*/
switch($method){
	
	case 'authorize':
	
		header('X-Frame-Options: DENY');
		error_reporting(0);
		
		if (!isset($_GET['client_id']) || empty($_GET['client_id'])){
			header("Content-Type: application/json");
			header("Cache-Control: no-store");
			$error = json_encode(array('error' => 'Parameter client_id', 'error_description' => 'The client_id parameter is required and seems to be missing'));
			echo $error;
			exit;
			}

		if(!isset($_GET['state']) || empty($_GET['state'])){
			header("Content-Type: application/json");
			header("Cache-Control: no-store");
			$error = json_encode(array('error' => 'Parameter state', 'error_description' => 'The state parameter is required and seems to be missing'));
			echo $error;
			exit;
			}
		
		if ( !is_user_logged_in() ) {
			wp_redirect( site_url() . '/oauth/login?sso_redirect='.$_GET['client_id'].'&state='.$_GET['state']);
			exit();
		}
		
		/**
		* @var Get the current user
		*/
		$current_user = wp_get_current_user();
		
		/**
		* @var Set the current users ID
		*/
		$userId = $current_user->ID;
		
		// @todo Not too sure what this is doing but we need to look at it.
		if($userId != ''){
			$oauth->finishClientAuthorization(TRUE, $userId, $_GET); // AUTO AUTHORIZE
		}
		
		try {
			$auth_params = $oauth->getAuthorizeParams();
		} catch (OAuth2ServerException $oauthError) {
			$oauthError->sendHttpResponse();
		}
	
		break;
	
	case 'request_token':
	
		header('X-Frame-Options: DENY');
		error_reporting(0);

		try {
			$oauth->grantAccessToken();
		} catch (OAuth2ServerException $oauthError) {
			$oauthError->sendHttpResponse();
		}
		
		break;
	
	case 'request_access':
	
	error_reporting(0);
	
	try {
		$token = $oauth->getBearerToken();
		$data = $oauth->verifyAccessToken($token);
		
		// GET THE USER ID FROM THE TOKEN AND NOT THE REQUESTING PARTY
		$user_id = $data['user_id'];
		
		global $wpdb;
		$info = $wpdb->get_row("SELECT * FROM wp_users WHERE ID = ".$user_id."");

		// don't send sensitive info accross the wire.
		unset($info->user_pass);
		unset($info->user_activation_key);

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		print_r(json_encode($info));
		
	} catch (OAuth2ServerException $oauthError) {
		$oauthError->sendHttpResponse();
	}
	
	break;
	// RETURN EVERYTHING ABOUT THE CURRENT USER
	
	/**
	 * Login Redirect
	 */
	case 'login':
		oauth2LoginLayout();
		exit();
		
	break;
	
	
	

}// END SWITCH OF METHOD

/**
 * Contains the HTML layout for the authorization login page
 */
function oauth2LoginLayout(){
	
	/**
	 * Form handler
	 */
		
	// CHECK IF THE LOGIN FORM HAS BEEN SUBMITTED
	if(isset($_POST['login'])) {
		
		$user 	= $_POST['user'];
		$pwd	= $_POST['pwd'];
		$creds = array();
		$creds['user_login'] 		= $user;
		$creds['user_password'] 	= $pwd;
		$user = wp_signon( $creds, false );
		
			if ( !is_wp_error($user) ){
			
			/*
				HOOK FOR SINGLE SIGN ON
				
				Description:
				
					If the sso_redirect if present then the script will look for the client_id (which has been encrypted with AES);
					We need to take that encryption and decrypt it and to get the client ID.
					
					NOW we will wait until the user has entered the credinials that is needed to log in. If the login is good and the sso_redirect is present we will redirect then back through the OAuth process which will then authinicate and send the 
					user with a token to the clients site. The client can then take the token and use it to get all the users information for database (AS IF THEY ARE LOGGED IN HERE). 
			*/
			if (isset($_GET['sso_redirect']) && $_GET['sso_redirect'] != ''){
				wp_redirect(site_url() .'/oauth/authorize/?client_id='.$_GET['sso_redirect'].'&state='.$_GET['state'].'&response_type=code');
				
			}else{
				$error = '<div class="login_message" style="margin:0 auto;width:50%; font-weight:bold;font-size:14px;color:red;">Incorrect Information</div>';
				} 
		 
	}
	}
	?>
	<!DOCTYPE html>
	<!--[if lt IE 7 ]> <html lang="en" class="ie6 ielt8"> <![endif]-->
	<!--[if IE 7 ]>    <html lang="en" class="ie7 ielt8"> <![endif]-->
	<!--[if IE 8 ]>    <html lang="en" class="ie8"> <![endif]-->
	<!--[if (gte IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
	<head>
	<meta charset="utf-8">
	<title><?php print bloginfo('name'); ?> - Authorization Login</title>
	<!-- <link rel="stylesheet" type="text/css" href="style.css" /> -->
	<style>
	/* Reset CSS */
	html, body, div, span, applet, object, iframe,
	h1, h2, h3, h4, h5, h6, p, blockquote, pre,
	a, abbr, acronym, address, big, cite, code,
	del, dfn, em, font, img, ins, kbd, q, s, samp,
	small, strike, strong, sub, sup, tt, var,
	b, u, i, center,
	dl, dt, dd, ol, ul, li,
	fieldset, form, label, legend,
	table, caption, tbody, tfoot, thead, tr, th, td {
		margin: 0;
		padding: 0;
		border: 0;
		outline: 0;
		font-size: 100%;
		vertical-align: baseline;
		background: transparent;
	}
	body {
		background: #DCDDDF;
		color: #000;
		font: 14px Arial;
		margin: 0 auto;
		padding: 0;
		position: relative;
	}
	h1{ font-size:28px;}
	h2{ font-size:26px;}
	h3{ font-size:18px;}
	h4{ font-size:16px;}
	h5{ font-size:14px;}
	h6{ font-size:12px;}
	h1,h2,h3,h4,h5,h6{ color:#563D64;}
	small{ font-size:10px;}
	b, strong{ font-weight:bold;}
	a{ text-decoration: none; }
	a:hover{ text-decoration: underline; }
	.left { float:left; }
	.right { float:right; }
	.alignleft { float: left; margin-right: 15px; }
	.alignright { float: right; margin-left: 15px; }
	.clearfix:after,
	form:after {
		content: ".";
		display: block;
		height: 0;
		clear: both;
		visibility: hidden;
	}
	.container { margin: 25px auto; position: relative; width: 900px; }
	#content {
		background: #f9f9f9;
		background: -moz-linear-gradient(top,  rgba(248,248,248,1) 0%, rgba(249,249,249,1) 100%);
		background: -webkit-linear-gradient(top,  rgba(248,248,248,1) 0%,rgba(249,249,249,1) 100%);
		background: -o-linear-gradient(top,  rgba(248,248,248,1) 0%,rgba(249,249,249,1) 100%);
		background: -ms-linear-gradient(top,  rgba(248,248,248,1) 0%,rgba(249,249,249,1) 100%);
		background: linear-gradient(top,  rgba(248,248,248,1) 0%,rgba(249,249,249,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f8f8f8', endColorstr='#f9f9f9',GradientType=0 );
		-webkit-box-shadow: 0 1px 0 #fff inset;
		-moz-box-shadow: 0 1px 0 #fff inset;
		-ms-box-shadow: 0 1px 0 #fff inset;
		-o-box-shadow: 0 1px 0 #fff inset;
		box-shadow: 0 1px 0 #fff inset;
		border: 1px solid #c4c6ca;
		margin: 0 auto;
		padding: 25px 0 0;
		position: relative;
		text-align: center;
		text-shadow: 0 1px 0 #fff;
		width: 400px;
	}
	#content h1 {
		color: #7E7E7E;
		font: bold 25px Helvetica, Arial, sans-serif;
		
		margin: 10px 0 30px;
	}
	#content h1:before,
	#content h1:after {}
	#content h1:after {}
	#content h1:before {}
	#content:after,
	#content:before {}
	#content:after {}
	#content:before {}
	#content form { margin: 0 20px; position: relative }
	#content form input[type="text"],
	#content form input[type="password"] {
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		-ms-border-radius: 3px;
		-o-border-radius: 3px;
		border-radius: 3px;
		-webkit-box-shadow: 0 1px 0 #fff, 0 -2px 5px rgba(0,0,0,0.08) inset;
		-moz-box-shadow: 0 1px 0 #fff, 0 -2px 5px rgba(0,0,0,0.08) inset;
		-ms-box-shadow: 0 1px 0 #fff, 0 -2px 5px rgba(0,0,0,0.08) inset;
		-o-box-shadow: 0 1px 0 #fff, 0 -2px 5px rgba(0,0,0,0.08) inset;
		box-shadow: 0 1px 0 #fff, 0 -2px 5px rgba(0,0,0,0.08) inset;
		-webkit-transition: all 0.5s ease;
		-moz-transition: all 0.5s ease;
		-ms-transition: all 0.5s ease;
		-o-transition: all 0.5s ease;
		transition: all 0.5s ease;
		background: #eae7e7;
		border: 1px solid #c8c8c8;
		color: #777;
		font: 13px Helvetica, Arial, sans-serif;
		margin: 0 0 10px;
		padding: 15px 10px 15px 40px;
		width: 80%;
	}
	#content form input[type="text"]:focus,
	#content form input[type="password"]:focus {
		-webkit-box-shadow: 0 0 2px #ed1c24 inset;
		-moz-box-shadow: 0 0 2px #ed1c24 inset;
		-ms-box-shadow: 0 0 2px #ed1c24 inset;
		-o-box-shadow: 0 0 2px #ed1c24 inset;
		box-shadow: 0 0 2px #ed1c24 inset;
		background-color: #fff;
		border: 1px solid #ed1c24;
		outline: none;
	}
	#username { background-position: 10px 10px !important }
	#password { background-position: 10px -53px !important }
	#content form input[type="submit"] {
		background: rgb(254,231,154);
		background: -moz-linear-gradient(top,  rgba(254,231,154,1) 0%, rgba(254,193,81,1) 100%);
		background: -webkit-linear-gradient(top,  rgba(254,231,154,1) 0%,rgba(254,193,81,1) 100%);
		background: -o-linear-gradient(top,  rgba(254,231,154,1) 0%,rgba(254,193,81,1) 100%);
		background: -ms-linear-gradient(top,  rgba(254,231,154,1) 0%,rgba(254,193,81,1) 100%);
		background: linear-gradient(top,  rgba(254,231,154,1) 0%,rgba(254,193,81,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fee79a', endColorstr='#fec151',GradientType=0 );
		-webkit-border-radius: 30px;
		-moz-border-radius: 30px;
		-ms-border-radius: 30px;
		-o-border-radius: 30px;
		border-radius: 30px;
		-webkit-box-shadow: 0 1px 0 rgba(255,255,255,0.8) inset;
		-moz-box-shadow: 0 1px 0 rgba(255,255,255,0.8) inset;
		-ms-box-shadow: 0 1px 0 rgba(255,255,255,0.8) inset;
		-o-box-shadow: 0 1px 0 rgba(255,255,255,0.8) inset;
		box-shadow: 0 1px 0 rgba(255,255,255,0.8) inset;
		border: 1px solid #D69E31;
		color: #85592e;
		cursor: pointer;
		float: left;
		font: bold 15px Helvetica, Arial, sans-serif;
		height: 35px;
		margin: 20px 0 35px 15px;
		position: relative;
		text-shadow: 0 1px 0 rgba(255,255,255,0.5);
		width: 120px;
	}
	#content form input[type="submit"]:hover {
		background: rgb(254,193,81);
		background: -moz-linear-gradient(top,  rgba(254,193,81,1) 0%, rgba(254,231,154,1) 100%);
		background: -webkit-linear-gradient(top,  rgba(254,193,81,1) 0%,rgba(254,231,154,1) 100%);
		background: -o-linear-gradient(top,  rgba(254,193,81,1) 0%,rgba(254,231,154,1) 100%);
		background: -ms-linear-gradient(top,  rgba(254,193,81,1) 0%,rgba(254,231,154,1) 100%);
		background: linear-gradient(top,  rgba(254,193,81,1) 0%,rgba(254,231,154,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fec151', endColorstr='#fee79a',GradientType=0 );
	}
	#content form div a {
		color: #004a80;
	    float: right;
	    font-size: 12px;
	    margin: 30px 15px 0 0;
	    text-decoration: underline;
	}
	.button {
		background: rgb(247,249,250);
		background: -moz-linear-gradient(top,  rgba(247,249,250,1) 0%, rgba(240,240,240,1) 100%);
		background: -webkit-linear-gradient(top,  rgba(247,249,250,1) 0%,rgba(240,240,240,1) 100%);
		background: -o-linear-gradient(top,  rgba(247,249,250,1) 0%,rgba(240,240,240,1) 100%);
		background: -ms-linear-gradient(top,  rgba(247,249,250,1) 0%,rgba(240,240,240,1) 100%);
		background: linear-gradient(top,  rgba(247,249,250,1) 0%,rgba(240,240,240,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f7f9fa', endColorstr='#f0f0f0',GradientType=0 );
		-webkit-box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;
		-moz-box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;
		-ms-box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;
		-o-box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;
		box-shadow: 0 1px 2px rgba(0,0,0,0.1) inset;
		-webkit-border-radius: 0 0 5px 5px;
		-moz-border-radius: 0 0 5px 5px;
		-o-border-radius: 0 0 5px 5px;
		-ms-border-radius: 0 0 5px 5px;
		border-radius: 0 0 5px 5px;
		border-top: 1px solid #CFD5D9;
		padding: 15px 0;
	}
	.button a {
		color: #7E7E7E;
		font-size: 17px;
		padding: 2px 0 2px 40px;
		text-decoration: none;
		-webkit-transition: all 0.3s ease;
		-moz-transition: all 0.3s ease;
		-ms-transition: all 0.3s ease;
		-o-transition: all 0.3s ease;
		transition: all 0.3s ease;
	}
	.button a:hover {
		background-position: 0 -135px;
		color: #00aeef;
	}
	</style>
	</head>
	<body>
	<div class="container">
		<section id="content">
			<form action="" method="post">
				<h1>Authorization Login</h1>
				<div>
					<input name="user" type="text" placeholder="Username" id="username" />
				</div>
				<div>
					<input name="pwd" type="password" placeholder="Password" id="password" />
				</div>
				<div>
					<input type="submit" name="login" value="Log in" />
				</div>
			</form><!-- form -->
		</section><!-- content -->
	</div><!-- container -->
	</body>
	</html>


<?php
}
?>