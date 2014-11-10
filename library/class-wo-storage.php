<?php
/**
 * WP OAuth Storage Class
 */
class WO_Storage extends WO_API
{

	/**
	 * [__construct description]
	 *
	 * The WP Db settings will be deturmined here so that it is exstendable and in the future the 
	 * plugin could interacted with a different DB other than the DB that WP is using.
	 */
	function __construct ()
	{
		$this->DB_NAME = DB_NAME;
		$this->DB_USER = DB_USER;
		$this->DB_PASSWORD = DB_PASSWORD;
		$this->DB_HOST = DB_HOST;
	}

	/**
	 * Verify client creditials 
	 * @param  [type] $client_id     [description]
	 * @param  [type] $client_secret [description]
	 * @return [type]                [description]
	 *
	 * @todo Clean up the logic for client_secret. Current is just for development speed
	 */
	public function verify_creditials ( $client_id=null, $client_secret=null )
	{
		global $wpdb;

		if( null != $client_secret )
		{
			$query = $wpdb->prepare("SELECT client_id, user_id FROM {$wpdb->prefix}oauth_clients WHERE client_id=%s AND client_secret=%s", $client_id, $client_secret);
		}
		else
		{
			$query = $wpdb->prepare("SELECT client_id, user_id FROM {$wpdb->prefix}oauth_clients WHERE client_id=%s", $client_id);
		}
		
		$return = $wpdb->get_row($query);
		if( $wpdb->num_rows > 0 )
		{
			return $return;
		}else
		{
			return false;
		}
	}

	/**
	 * Verify an authorization code
	 * @param  [type] $client_id [description]
	 * @param  [type] $code      [description]
	 * @return [type]            [description]
	 *
	 * @todo Add WP setting for authorization code experation time (10 minutes max)
	 */
	public function verify_code ($client_id, $code)
	{
		global $wpdb;
		$o = get_option("wo_options");

		$query = $wpdb->prepare("SELECT code, expires FROM {$wpdb->prefix}oauth_codes WHERE client_id=%s AND code=%s", $client_id, $code);
		$return = $wpdb->get_results($query);
		if($wpdb->num_rows > 0)
		{
			$expires = strtotime($return->expires." + ".$o["auth_code_expiration_time"]." minute");
			$current_time = strtotime(current_time("mysql"));
			if($expires < $current_time)
			{
				$wpdb->delete("{$wpdb->prefix}oauth_codes", array( "code" => $code));
				return false;
			}
			$wpdb->delete("{$wpdb->prefix}oauth_codes", array( "code" => $code));
			return true;
		}
	}

	/**
	 * verify a refresh token
	 * @param  [type] $token [description]
	 * @return [type]        [description]
	 */
	public function verify_refresh_token( $refesh_token )
	{
		global $wpdb;
		$query = $wpdb->prepare("SELECT client_id, user_id, expires FROM {$wpdb->prefix}oauth_refresh_tokens WHERE refresh_token=%s", $refesh_token);
		$return = $wpdb->get_row($query);
		if($wpdb->num_rows > 0)
		{
			$o = get_option("wo_options");
			if($o["refresh_token_lifespan"] != "0")
			{
				$expires = strtotime($return->expires." + ".$o["refresh_token_lifespan"]." ".$o["refresh_token_lifespan_unit"]);
				$current_time = strtotime(current_time('mysql'));
				if($expires < $current_time)
				{
					$wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array( "refesh_token" => $refesh_token));
					new WO_Error("invalid_refresh_token");
				}
			}
			$wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array( "refesh_token" => $refesh_token));
			return $return;
		}

		new WO_Error("invalid_refresh_token");
	}

	/**
	 * [create_code description]
	 * @param  [type] $client_id [description]
	 * @return [type]            [description]
	 */
	public function create_code ( $client_id )
	{
		global $wpdb;
		$_code = self::generate_token();
		$data= array(
			"client_id" => $client_id,
			"code" 		=> $_code
			);
		$query = $wpdb->insert("{$wpdb->prefix}oauth_codes", $data);
		if(! $query )
			new WO_Error("server_error", "redirect");

		return $_code;
	}

	/**
	 * Create and access token for a client ID
	 * @param  [type] $client_id [description]
	 * @return [type]            [description]
	 *
	 * @todo add WP setting to only keep one active access_token alive all the time
	 */
	public function create_token ( $client_id, $user_id, $type=null )
	{
		global $wpdb;
		$o = get_option("wo_options");

		if( 1===1 )
		{
			$wpdb->delete("{$wpdb->prefix}oauth_access_tokens", array("user_id", $user_id));
			$wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array("user_id", $user_id));
		}

		$_token = self::generate_token();
		$data= array(
			"access_token" => $_token,
			"client_id" => $client_id,
			"user_id"	=> $user_id
			);
		$query = $wpdb->insert("{$wpdb->prefix}oauth_access_tokens", $data);
		if(! $query)
			new WO_Error("server_error");

		$return = new stdClass;
		$return->token = $_token;

		// If refresh token is enabled
		if ($type === "code" && $o["refresh_tokens_enabled"] == 1)
		{
			$_refresh_token = self::generate_token();
			$data= array(
				"refresh_token" => $_refresh_token,
				"client_id" 	=> $client_id,
				"user_id"		=> $user_id,
				);
			$query = $wpdb->insert("{$wpdb->prefix}oauth_refresh_tokens", $data);
			if(! $query)
				new WO_Error("server_error");

			$return->refresh_token = $_refresh_token;
		}

		return $return;
	}

	/**
	 * Return redirect URI
	 * @param  [type] $client_id [description]
	 * @return [type]            [description]
	 */
	public function get_redirect_uri ($client_id)
	{
		global $wpdb;
		$query = $wpdb->prepare("SELECT redirect_uri FROM {$wpdb->prefix}oauth_clients WHERE client_id=%s", $client_id);
		$query = $wpdb->get_row($query);
		return $query->redirect_uri;
	}

	/**
	 * [generate_token description]
	 * @param  integer $length [description]
	 * @return [type]          [description]
	 */
	private static function generate_token ( $length = 40 ) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
}