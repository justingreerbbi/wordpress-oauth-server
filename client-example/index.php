<?php
/**
 * Example WP OAuth2 Client
 * 
 * @author Justin Greer
 * @copyright 2014 Justin Greer <justin@justin-greer.com>
 * @license GPL2
 */

/**
 * session_start
 * In this example we will use simple sessions
 * You can use cookies or any means to track user login status. It is up to you!
 */
session_start();

// Check if the user is logged in. This is simple for demonstration purposes
if( ! isset( $_SESSION['loggedIn'] ) )
	$_SESSION['loggedIn'] = false;
?>

<html>
	<head>
		<title> WP Oauth2 Client - Example</title>
	</head>
	<body>
		<?php if( $_SESSION['loggedIn'] === false  ): ?>
			<a href="http://development.dev/oauth/authorize/?client_id=b953042c39dc30f07004a54e916acc9aa0bc7751&state=someuidparameter&response_type=code" title="This will link to the WordPress site runing Oauth2 plugin"> Login </a>
		<?php else: ?>
			Welcome Back - <a href="/logout.php" title="The user will directed to a logout script that will simply unset the user sesion"> Logout </a>
		<?php endif; ?>


	</body>
</html>