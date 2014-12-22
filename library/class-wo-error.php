<?php
/**
 * WP OAuth Error Class
 *
 * @todo Error descriptions are optional. Add a setting wether or not to include error description 
 * the admin area.
 */
class WO_Error
{
	public $errors;

	/**
	 * [__construct description]
	 * @param [type] $args [description]
	 * @param [type] $type [description]
	 */
	public function __construct( $args, $type=null )
	{
		$this->set_errors();
		$error['error']=$args;
		$error['error_description']=$this->errors[$args];	
		new WO_output($error, $type);
	}

	/** 
	 * OAuth Docs state the the following errors must be used.
	 * @uses filter WO_API_Errors
	 */
	private function set_errors ()
	{
		$errors = array(
			"invalid_request" => "The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed",
			"unauthorized_client" => "The client is not authorized to request an authorization code using this method",
			"access_denied" => "The resource owner or authorization server denied the request",
			"unsupported_response_type" => "The authorization server does not support obtaining an authorization code using this method",
			"invalid_scope" => "The requested scope is invalid, unknown, or malformed",
			"server_error" => "The authorization server encountered an unexpected condition that prevented it from fulfilling the request",
			"temporarily_unavailable" => "The authorization server is currently unable to handle the request due to a temporary overloading or maintenance of the server"
		);
		$errors = apply_filters("WO_API_Errors", $errors);
		$this->errors = $errors;
	}
}