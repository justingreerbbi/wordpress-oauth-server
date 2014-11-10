<?php
if( defined("ABSPATH") === false )
	die("Illegal use of the API");
/**
 * WordPress Mobile Oauth Main API Hook
 * This file is used to validate, process and perform all actions
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress Mobile Oauth
 */
class WO_API extends WO_Server {

 	public $method = null;

 	public $state = null;

 	public $client_id = null;

 	public $redirect_uri = null;

 	public $error = null;

 	public $client_type = null;

 	/**
 	 * Init method for called when needed
 	 *
 	 * - If the API is not enabled then the server will show the "temporarily_unavailable" error.
 	 * - If the method is allowed and the method exists in the API then call the method.
 	 */
	public static function init ()
	{
		$o = get_option("wo_options");
		if($o["enabled"] == 0)
			new WO_Error("temporarily_unavailable");

		global $wp_query;
		$method = $wp_query->get("oauth");

		// @todo possible add a filter here for allowed methods
		$allowed = array("token", "authorize");

		if ( method_exists(__CLASS__, $method) && in_array($method, $allowed))
		{
			call_user_func( array(__CLASS__, $method));
		}
	}

	/**
	 * Token Endpiint for WP OAuth
	 * The token call is used to request a token from the wuthentication server.
	 *
	 * Grant ttye
	 * @uses  WO_Storage::verify_creditials to verify client_id
	 * @uses  WO_Storage::verify_code to validate code given by client
	 * @uses  WO_Storage::grant_access_token to generate an access_token for the client
	 */
	private function token ()
	{
		$args = $_REQUEST;

		if ( empty( $args ) )
			wp_die('No paramters given');

		if (empty($args["grant_type"]))
			new WO_Error("unsupported_response_type");

		$storage = new WO_Storage();
		if($args["grant_type"] !== "refresh_token")
		{
			$client = $storage::verify_creditials($args["client_id"]);
			if( $client == false )
				new WO_Error("unauthorized_client");
		}

		/**
		 * Grant Type - auhtorization_code
		 */
		if (strtolower($args["grant_type"]) === "authorization_code")
		{
			if( empty($args["code"]) )
				new WO_Error("invalid_request");

			$storage = new WO_Storage();
			if($storage::verify_code($args["client_id"], $args["code"]))
			{
				self::grant_access_token($client, $args);
			}

			new WO_Error("access_denied");
		}

		/**
		 * Grant Type - refresh_token
		 */
		if(strtolower($args['grant_type']) === "refresh_token")
		{
			$o = get_option("wo_options");
			if($o["refresh_tokens_enabled"] == 0)
				new WO_Error("server_error");

			if(empty($args["refresh_token"]))
				new WO_Error("invalid_request");
		
			$storage = new WO_Storage();
			$token = $storage::verify_refresh_token($args["refresh_token"]);
			if( $token === false )
				new WO_Error("access_denied");

			self::grant_access_token($token, $args, "code");
		}
	}

	/**
	 * Authorize Endpoint for WP OAuth
	 * @uses  WO_Storage::verify_creditials to verify client_id
	 * @uses  WO_Storage::grant_authorization_code to generate an authorization_code to client
	 */
	private function authorize ()
	{

		$args = $_REQUEST;
		if (empty($args))
			new WO_Error("invalid_request");

		// response_type is REQUIRED
		if (empty($args["response_type"]))
			new WO_Error("invalid_request");

		// client_id is REQUIRED
		if(empty($args["client_id"]))
			new WO_Error("invalid_request");
	
		// Validate the client_id
		$storage = new WO_Storage();
		$client = $storage::verify_creditials($args["client_id"]);
		if( $client == false )
			new WO_Error("unauthorized_client");

		// Authorization Code Grant Type
		if (strtolower($args["response_type"]) === "code")
			self::grant_authorization_code($client, $args, "code");

		// Implict Grant Type
		if (strtolower($args["response_type"]) === "token")
			self::grant_access_token($client, $args, "token");

		// Fallback. If they make it this far then we know there is something missing
		new WO_Error("invalid_request");
	}

	/**
	 * Generates an authorization code
	 * 
	 * @uses WO_Storage::create_code to generate a authorization code
	 * @uses WO_Storage::get_redirect_uri to generate the redirect link designaed for the client
	 * 
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 * @link http://tools.ietf.org/html/rfc6749#section-4.1.2
	 */
	private function grant_authorization_code($client, $args)
	{

		if (empty($args))
			new WO_Error("invalid_request");
		
		if ( is_user_logged_in() === false )
			wp_redirect(wp_login_url( site_url() . $_SERVER['REQUEST_URI']));

		$storage = new WO_Storage();
		$code = $storage::create_code($args["client_id"]);

		// code- REQUIRED ALL THE TIME
		$data = array(
			"code" => $code,
		);

		if(isset($args["state"]))
			$data["state"] = $args["state"];

		new WO_Output($data, "redirect");
	}

	/**
	 * Grant an access token
	 *
	 * @uses WO_Storage::create_token to generate an access_token
	 * 
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	private function grant_access_token ($client, $args, $type="code")
	{
		$o = get_option("wo_options");

		/**
		 * Throw Error if there is no arguments
		 */
		if (empty($args))
			new WO_Error("invalid_request");

		/**
		 * If the user is not authenticated, we need to redirect the
		 * user to the login screen.
		 */
		if ( is_user_logged_in() === false )
			wp_redirect(wp_login_url( site_url() . $_SERVER['REQUEST_URI']));
		
		/**
		 * Create a token and if auth_code is being called then we need to create a 
		 * refresh_token as well
		 */
		$storage = new WO_Storage();
		$tokens = $storage::create_token($client->client_id, $client->user_id, $type);
		$data = array(
			"access_token" 	=> $tokens->token,
			"token_type" 	=> "bearer",
			"expires_in"	=> null
			);

		if($o["access_token_lifespan"] != 0)
			$data["expires_in"] = $o["access_token_lifespan"];

		/**
		 * Handle the Implicit Grant Type
		 * Send the access_token back to the client using uri redirect with access 
		 * token in the url
		 */
		if($type === "token")
		{

			/**
			 * If the parameter "state" was in the orginal request we are required to return it.
			 */
			if(isset($args["state"]))
				$data["state"] = $args["state"];

			// Cnvert the data array into a query string for the return back to
			// the client via HTTP
			$data = http_build_query($data);
			new WO_Redirect($storage::get_redirect_uri($args["client_id"])."?".$data);
		}
		
		/**
		 * Add the refresh token to the return and send the 
		 * information back to the client via JSON
		 */
		if( $type === "code" )
		{
			if(isset($tokens->refresh_token))
				$data["refresh_token"] = $tokens->refresh_token;
			new WO_Output($data);
		}

	}

	/**
	 * [grant_refresh_token description]
	 * @param  [type] $client [description]
	 * @param  [type] $args   [description]
	 * @param  string $type   [description]
	 * @return [type]         [description]
	 */
	public static function grant_refresh_token ($client, $args, $type="code")
	{
		if (empty($args))
			new WO_Error("invalid_request");

		if ( is_user_logged_in() === false )
			wp_redirect(wp_login_url( site_url() . $_SERVER['REQUEST_URI']));

		$storage = new WO_Storage();
		$tokens = $storage::create_token($client->client_id, $client->user_id, $type);
		$data = array(
			"access_token" 	=> $tokens->token,
			"token_type" 	=> "bearer",
			"expires_in"	=> 3600
			);
	}

}
WO_API::init();