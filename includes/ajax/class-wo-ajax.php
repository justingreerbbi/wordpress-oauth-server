<?php

class WO_Ajax {

	public static function init() {

		$ajax_events = array(
			'get_refreshed_fragments' => true,
			'create_new_client'	=> false,
			'remove_client' => false
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) 
		{
			add_action( 'wp_ajax_wo_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) 
			{
				add_action( 'wp_ajax_nopriv_wo_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get a refreshed cart fragment
	 */
	public static function get_refreshed_fragments() 
	{

	}

	/**
	 * Add a new client into the server
	 * @return [type] [description]
	 *
	 * @todo Figure out a way to include a user ID to the system. This is not required when the server is set to private but will be if it is set to public.
	 * lets do this right and never have to look at this agian!!!!
	 */
	public static function create_new_client ()
	{
		parse_str($_POST['data'], $params);
		extract($params);

		$new_client_id = wo_gen_key();
		$new_client_secret = wo_gen_key();

		/** validate the redirect URI */
		if(!filter_var($redirect_uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
		{
			print 'Error: Redirect URI is not Valid.';
			exit;
		}

		/** @todo Come on man. You can do better than this check */
		if(str_replace(" ", "", $client_name) == "")
		{
			print 'Error: Client Name is Required';
			exit;
		}

		global $wpdb;
		$insert_data = array(
			"client_id" => $new_client_id,
			"client_secret" => $new_client_secret,
			"name"	=> $client_name,
			"description"	=> $client_description,
			"redirect_uri"	=> $redirect_uri
			);
		$insert = $wpdb->insert("{$wpdb->prefix}oauth_clients", $insert_data);

		if(!$insert)
		{
			print 'System Error: Failed to add the client to the server.';
			exit;
		}
		print '1';
		exit;
	}

	/**
	 * Remove a client from the database
	 * @todo Add more of a check to make sure there is nothing fishing going on
	 */
	function remove_client ()
	{
		global $wpdb;
		$action = $wpdb->delete( "{$wpdb->prefix}oauth_clients", array( 'client_id' =>  $_POST['data']) );
		if($action)
		{
			print "1";
		}
		else
		{
			print "System Error: Could not remove the client from the server.";
		}
		exit;
	}

}
WO_Ajax::init();