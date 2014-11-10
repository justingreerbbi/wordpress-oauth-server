<?php

class WOS_AJAX {

	public static function init() {

		$ajax_events = array(
			'get_refreshed_fragments' => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) 
		{
			add_action( 'wp_ajax_wos_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) 
			{
				add_action( 'wp_ajax_nopriv_wos_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get a refreshed cart fragment
	 */
	public static function get_refreshed_fragments() 
	{

	}

}
WOS_AJAX::init();