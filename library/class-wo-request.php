<?php
/**
 * WP OAuth Request Class
 *
 * This class actually handles the request depending on the request
 */
class WO_Request extends WOS_API
{
	/**
	 * CONSTANTS
	 */
	const AUTHORIZATION_CODE = "code";

	// scope
	public $scope;

	/**
	 * [__construct description]
	 */
	public function __construct ()
	{}
}