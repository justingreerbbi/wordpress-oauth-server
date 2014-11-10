<?php
/**
 * WP OAuth Redirect Class
 */
class WO_Redirect extends WO_API
{

	function __construct ($redirect)
	{
		self::_redirect($redirect);
	}

	private static function _redirect($location)
	{
		wp_redirect($location, 302);
		exit;
	}

}