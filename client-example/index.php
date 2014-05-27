<?php
// Generic OAuth Example
// @author Justin Greer

// Set the OAuth Client_ID and the OAuth Link
// Replace and add your domain instead of http://justin-greer.com
$Client_ID = '************************';
$OAuth_Login = 'http://justin-greer.com/oauth/authorize/?client_id=' . $Client_ID. '&state=123&response_type=code';

// Session Start
session_start();
?>

<html>

	<head>
    	<title>Simple OAuth Client</title>
    </head>
    
   	<body>

	<?php 
    // If the person is not logged in then display the login link
    if(!isset($_SESSION['loggedin'])){
            print 'You are not logged in. <a href="'.$OAuth_Login.'">Login Here</a>';
        }else{
            print 'Welcome Back, '.$_SESSION['loggedin']->user_login . ' <a href="/logout.php">Logout</a>';
        }
    ?>
    
    
    <h3>
    	This is just an example and should be used as ONLY a reference and not production code.
    </h3>
    
    <p>
    	Although the OAuth server authenticates a users, it does not store user sessions. This is up to you however you decide to log a user in. Think of the OAuth server as a 3rd party login.
    </p>
    
    <p>
    	This is a very simple example of how to use the WordPress OAuth 2.0 Server plugin as a PHP client.
    </p>
    
	</body>
</html>