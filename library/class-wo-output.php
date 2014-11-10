<?php
/**
 * WP OAuth Output Class
 */
class WO_Output extends WO_API
{
	public function __construct($data, $type=null, $header=null)
	{
		$storage = new WO_Storage();
		$this->redirect_uri = $storage::get_redirect_uri($_REQUEST["client_id"]);

		if ($type === "redirect")
			$this->redirect($data);

		if (null === $type)
			$this->output($data, $header);
	}

	/**
	 * Output the reponse back to the client
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function output ($data, $header=null)
	{
		// OK 200 - Default
		if(null === $header || $header === 200)
			header("HTTP/1.1 200 OK");

		// Bad Request
		if( $header === 400 )
			header("HTTP/1.1 400 Bad Request");
		
		// Set other header
		// @todo add filter for additional header values to be sent
		header("Content-Type: application/json;charset=UTF-8");
		header("Cache-Control: no-store");
		header("Pragma: no-cache");

		// state is required if present
		if(isset($args['state']))
			$output["state"] = $args["state"];
		
		print json_encode($data);
		exit;
	}

	/**
	 * Redirect back to the clients redirect URI
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function redirect ($data)
	{
		$data = http_build_query($data);
		header("Location: ".$this->redirect_uri."?".$data);
		exit;
	}
}