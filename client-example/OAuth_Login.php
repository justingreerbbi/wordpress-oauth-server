<?php
/**
 * OAuth_Login.php
 *
 * The OAuth server will redirect to this file/url.
 * From here the script will communication between the client and OAuth server
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @version 1.2.0
 * @copyright 2013 Justin Greer Interactive, LLC
 * @license GPL2
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * in your development cycle save you a lot of time by preventing you having to rewrite<br>
 * major documentation parts to generate some usable form of documentation.
 */

/*
 |--------------------------------------------------------------------------
 | Look for the code param
 |--------------------------------------------------------------------------
 |
 | Check for the _GET param "code"
 | "code" will be returned by the OAuth Server. Do not run the script
 | if the param is missing. 
 | 
 | This is just an example. You should preform this check in a way that
 | meets your security standards. This snippt works for the example OAuth_Client
 |
*/
if( !isset($_GET['code']) || $_GET['code'] == '' ){
	die('Something went wrong');
}

/*
 |--------------------------------------------------------------------------
 | Require the OAuth_Client class
 |--------------------------------------------------------------------------
 |
 | A simple require will work
 |
*/
require_once( 'library/OAuth_Client.php' );

/*
 |--------------------------------------------------------------------------
 | Init the OAuth_Client class
 |--------------------------------------------------------------------------
 |
*/
$wpoa = new OAuth_Client();

/*
 |--------------------------------------------------------------------------
 | Configure OAuth Options
 |--------------------------------------------------------------------------
 |
 | There is 3 required parameters that need to be used for configuring
 | the OAuth Client.
 |
 | 1. End_Point : The full url of the OAuth server without a trailing slash
 | 2. Client_ID : Client id assigned when a client was added to WordPress OAuth 2.0 Plugin
 | 3. Client_Secret : Client secret assigned when a client was added to WordPress OAuth 2.0 Plugin
 |
*/
$wpoa->Config(array(
					'End_Point' => 'http://justin-greer.com',
					'Client_ID' => '****************************',
					'Client_Secret' => '*****************'
					));

/*
 |--------------------------------------------------------------------------
 | Request a token
 |--------------------------------------------------------------------------
 |
 | Take the code returned and request a token from the OAuth server
 |
*/
$Response = $wpoa->Request_Token( $_GET['code'] );

/*
 |--------------------------------------------------------------------------
 | Dump Entire Response
 |--------------------------------------------------------------------------
 |
 | Uncomment this to dump the response
 |
*/
 // var_dump( $response ); exit;

/*
 |--------------------------------------------------------------------------
 | Request Access to the OAuth server
 |--------------------------------------------------------------------------
 |
 | From the response above there will be a access token that is only valid
 | for a certian time period. The access token is good for one OAuth call.
 | 
 | Here is where the OAuth server returns information about the
 | authentication user.
 |
*/
$User_Data = $wpoa->request_access( $Response->access_token );

/*
 |--------------------------------------------------------------------------
 | Dump ALL user data
 |--------------------------------------------------------------------------
 |
 | Uncomment this to dump all the user data
 |
*/
 // var_dump( $User_Data ); exit;

/*
 |--------------------------------------------------------------------------
 | Log the user in and redirect back to the homepage
 |--------------------------------------------------------------------------
 |
 | This can be done many ways but here we will keep it simple. Assign the
 | entire user informtion for the OAuth server to a session.
 |
 | THIS IS NOT A RECOMMENDED WAY OF LOGGING A USER IN WITH PHP. DO IT RIGHT
 |
*/
if(isset($User_Data->ID)){
	session_start();
	$_SESSION['loggedin'] = $User_Data;
	header('Location: /');
	}