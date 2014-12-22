<?php
/**
 *  Example Authenticating using PHP cURL
 *  
 *  @author Justin Greer <justin@justin-greer.com>
 *  @version 1.0
 *  @package OAuth Client Examples
 */
class WP_OAuth_Authenticate {

	public $Headers;
	public $ResponseCode;

	private $_AuthorizeUrl = "";
	private $_AccessTokenUrl = "";

	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		$this->Headers = array();
		$this->ResponseCode = 0;
	}

	/**
	 * [RequestAuthenticationCode description]
	 * @param [type] $client_id    [description]
	 * @param [type] $redirect_uri [description]
	 */
	public function RequestAuthenticationCode ($client_id, $redirect_uri)
	{
		return($this->_AuthorizeUrl . "?client_id=" . $client_id . "&response_type=code&redirect_uri=" . $redirect_url);
	}

	/**
	 * [GetAccesToken description]
	 * @param [type] $client_id     [description]
	 * @param [type] $client_secret [description]
	 * @param [type] $auth_code     [description]
	 */
	public function GetAccesToken ( $client_id, $client_secret, $auth_code ) 
	{
		$init = $this->InitCurl($this->$_AccessTokenUrl);
	}

	/**
	 * [InitCurl description]
	 * @param [type] $endpoint [description]
	 */
	private function InitCurl ( $endpoint ) 
	{
		$curl = curl_init($endpoint);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HEADER,'Content-Type: application/x-www-form-urlencoded');

		// Remove comment if you have a setup that causes ssl validation to fail
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        return $curl;
	}

	/**
	 * [ExecRequest description]
	 * @param [type] $url          [description]
	 * @param [type] $access_token [description]
	 * @param [type] $get_params   [description]
	 */
    public function ExecRequest( $url, $access_token, $get_params ) {
        $full_url = http_build_query( $url, $get_params );
 
        $curl = $this->InitCurl($url);
 		
 		// Set the authorization header (required)
        curl_setopt($curl, CURLOPT_HTTPHEADER, array (
            "Authorization: Basic " . base64_encode( $access_token )
        ));
        $response = curl_exec($curl);
        if ($response == false) {
            die("curl_exec() failed. Error: " . curl_error( $curl ));
        }
        return json_decode( $response );        
    }

}

/**
 * cURL examples using PHP. Most cURL calls to the OAuth server need to have a basic header authorization
 * @var [type]
 */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$URL);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_USERPWD, "username:password"); // basic header authentication