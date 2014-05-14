<?php
/**
 * Login Callback
 * 
 * Thsi file location is set in the providers dashboard. WP OAuth2 erelies on data input by the provider.
 * This keeps security tight and does nto allow a user to use a 3rd party location for an attack or to steal information
 * 
 * 1. The user will redirected here by the provider and the url will have some information as well.
 * 2. state - This is a paramter only used by the client as a way to track user progress during the authentication. IS reffer link to redirect back to after a succesfful login in made
 * 3. code - This is a token given by the provider that is used for the next step in the process of authenticating.
 * 
 * COLLECT ALL DATA SENT BACK FROM THE PROVIDER
 */

// You can populate these values how ever you want but they must be correct (DB, hardcoded)
$clientID = "b953042c39dc30f07004a54e916acc9aa0bc7751";
$clientSecret = "3982b878f6f0704e1045";

// I KNOW, I KNOW but this is just an example - I am cheap and tired
if( count($_GET) <= 0  ){
	die("unauthorized access.");
}

print "1. Passed simple GET check  <br>";
print "2. Preparing to Request Token  with token ".$_GET['code']."<br>";

/**
 * This is not ideal for production at all
 * 
 * Here you can take the code provided by provide after the authorize call and passit back
 * to request access. 
 * ( If the user is not logged in, they should be presented with a login screen )
 */

/////////////////////////////
//
// STEP 1 - REQUEST TOKEN
//
/////////////////////////////
$url = "http://development.dev/oauth/request_token?code=".$_GET['code']."&grant_type=authorization_code&client_id=".$clientID."&client_secret=".$clientSecret;
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode( $result );

// Handle the response as you see fit
print '3. Response from provider: <pre>';
print_r( $result );
print '</pre>';

if( isset( $result->error) )
	die("ERROR: There was an error present. This is where you would use [error_description] to your liking.");

// YOU COULD STOP HERE IS ALL YOU NEEDED WAS TO AUTHORIZE THE USER (SINGLE SIGN ON). IF YOU WANT TO GATHER THERE ACCOUNT INFORMATION YOU
// CAN GO TO STEP 4. 

////////////////////////////////////////////////////////
//
// STEP 2 - REQUEST ACCESS TO USER INFORMATION
//
////////////////////////////////////////////////////////

// Use the return from above to and do as you please but you will need the acces_token at a minimum
print '4. Preparing the acces_token call <br>';
$url = "http://development.dev/oauth/request_access?access_token=". $result->access_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode( $result );

print '5. Response from provider: <pre>';
print_r( $result );
print '</pre>';

if( isset( $result->error) )
	die("ERROR: There was an error present. This is where you would use [error_description] to your liking.");

/////////////////////////////////////
//
// STEP 3 - LOG USER IN ON YOUR SIDE
//
/////////////////////////////////////

// As long as everything went ok here, you 
if( !isset( $result->error) )
	print 'Here is where you can use the users information provided by the provider to set a user session and then redirect the user elsewhere';