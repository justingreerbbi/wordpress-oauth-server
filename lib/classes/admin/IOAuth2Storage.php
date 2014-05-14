<?php
/**
 * Require all the needed files
 */
require_once(WP_OAUTH2_PATH . 'lib/classes/OAuth2.php');
require_once(WP_OAUTH2_PATH . 'lib/classes/IOAuth2Storage.php');
require_once(WP_OAUTH2_PATH . 'lib/classes/IOAuth2GrantCode.php');
require_once(WP_OAUTH2_PATH . 'lib/classes/IOAuth2RefreshTokens.php');

/**
 * IOAuth2StorageWP class is used in for the admin functions in via the admin panel. 
 */
class IOAuth2StorageWP implements IOAuth2GrantCode, IOAuth2RefreshTokens{

	/**
	 * Custom Error Handle
	 * 
	 * @param string $e Message that will be displayed as the error
	 */
	private function handleException($e) {
		header("Content-Type: application/json");
			header("Cache-Control: no-store");
			$error = json_encode(array('Error' => $e));
			echo $error;
			exit;
	}

	/**
	 * Adds a new Client to the WP OAuth2 Complete Database
	 * Client_id and Client Secret are created un this function as well
	 *
	 * @param string $name Clients name as a slug
	 * @param string $client_redirect Client redirect URL that will be used after authorization
	 * 
	 * @return Bool - True if successfull
	 * 
	 * @todo Add Parameter "$client_name" to be more friendly in the WP OAuth2 Complete Dashboard
	 */
	public function addClient($mdop_name, $client_redirect) {
		$client_id 		= $this->generateKey();
		$client_secret 	= $this->generateSecret();
		
		global $wpdb;
		$addClient = $wpdb->insert($wpdb->prefix . 'oauth2_clients',array('name'=>$mdop_name, 'client_id' => trim(rtrim($client_id)), 'client_secret' => $client_secret, 'redirect_uri' => $client_redirect));
		if (!$addClient){
			$this->handleException('Could not add Client');
			}else{
				return TRUE;
				}
	}

	/**
	 * Checks if client_id and secret match in the database
	 * 
	 * @param int $client_id Client ID to be checked
	 * @param int $client_secret Clinet Secret to be checked
	 * 
	 * @return Bool - True if there is a match
	 * 
	 * @todo Add SHA1 Encryption to this feature for better security
	 */
	public function checkClientCredentials($client_id, $client_secret) {
		global $wpdb;
		$wpdb->query("SELECT client_id, client_secret FROM {$wpdb->prefix}oauth2_clients WHERE client_id = '$client_id' AND client_secret = '$client_secret'");
		if ($wpdb->num_rows > 0){
			return TRUE;
		}else{
			return FALSE;
			}		
	}

	/**
	 * Pulls the client details from the database
	 * 
	 * @param int $client_id Client ID to lookup
	 * 
	 * @return Array/Bool - If found then it will return a Array and if not found then will return false
	 */
	public function getClientDetails($client_id) {
		global $wpdb;
		$info = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}oauth2_clients WHERE client_id = '$client_id'", ARRAY_A );
		if ($wpdb->num_rows > 0){
			return $info[0];
			}else{
			return FALSE;
			}
	}

	/**
	 * Returns Access Token of a authorization token
	 * 
	 * @param string $oauth_token 
	 * 
	 * @return AccessToken
	 */
	public function getAccessToken($oauth_token) {
		return $this->getToken($oauth_token, FALSE);
	}

	/**
	 * Sets Access Token
	 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL) {
		$this->setToken($oauth_token, $client_id, $user_id, $expires, $scope, FALSE);
	}

	/**
	 * Gets Refresh Token
	 */
	public function getRefreshToken($refresh_token) {
		return $this->getToken($refresh_token, TRUE);
	}

	/**
	 * Sets refresh token
	 */
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL) {
		return $this->setToken($refresh_token, $client_id, $user_id, $expires, $scope, TRUE);
	}

	/**
	 * Deletes RefreshToken
	 * 
	 * @param string $refresh_token Refresh Token to delete from database
	 * 
	 * @return JSON error if failed
	 */
	public function unsetRefreshToken($refresh_token) {
		global $wpdb;
		$deleteToken = $wpd->query("DELETE FROM  {$wpdb->prefix}oauth2_refresh_tokens WHERE refresh_token = '$refresh_token'");
		if (!$deleteToken){
			$this->handleException('Could not delete refresh token'); // THROW ERROR
		}
	}

	/**
	 * Implements IOAuth2Storage::getAuthCode().
	 */
	public function getAuthCode($code) {
		global $wpdb;
			$select = $wpdb->get_results("SELECT code, client_id, user_id, redirect_uri, expires, scope FROM {$wpdb->prefix}oauth2_auth_codes  WHERE code = '$code'", ARRAY_A );
			
			if ($wpdb->num_rows > 0){
				return $select[0];
				}else{
					$this->handleException('Auth Code not found'); // THROW ERROR
					}
	}

	/**
	 * Sets Authorization code on a good user log in
	 * 
	 * @param string $code Token that was returned for authorization
	 * @param int $client_id Client ID
	 * @param int $user_id User id that autorized the log in.
	 * @param string $redirect_uri Redirect URI is required for security needs even if it is in the database already
	 * @param int $expires timestamp set by default settings
	 * @param $scope We are currently not using this 
	 */
	public function setAuthCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = NULL) {
		  global $wpdb;
		  $set = $wpdb->insert($wpdb->prefix.'oauth2_auth_codes',array('code' => $code, 'client_id' => $client_id, 'user_id' => $user_id, 'redirect_uri' => $redirect_uri, 'expires' => $expires, 'scope' => $scope ));
		  if (!$set){
			  $this->handleException('Failed to set token');
			  }
	}

	/**
	 * Check Grant type fo the request
	 */
	public function checkRestrictedGrantType($client_id, $grant_type) {
		return TRUE; // Not implemented
	}

	/**
	 * Creates a refresh or access token
	 * 
	 * @param string $token - Access or refresh token id
	 * @param string $client_id
	 * @param mixed $user_id
	 * @param int $expires
	 * @param string $scope
	 * @param bool $isRefresh
	 */
	protected function setToken($token, $client_id, $user_id, $expires, $scope, $isRefresh = TRUE) {
		global $wpdb;
		if ($isRefresh == TRUE){
			$tablename = $wpdb->prefix.'oauth2_refresh_tokens';
			}else{
				$tablename = $wpdb->prefix.'oauth2_access_tokens';
				}
		$set = $wpdb->insert($tablename,array('oauth_token' => $token, 'client_id' => $client_id, 'user_id' => $user_id, 'expires' => $expires, 'scope' => $scope ));
		if ($set){
			return TRUE;
			}
	}

	/**
	 * Retrieves an access or refresh token.
	 * 
	 * @param string $token
	 * @param bool $refresh
	 */
	protected function getToken($token, $isRefresh) {
		global $wpdb;
		if ($isRefresh == TRUE){
			$tablename = $wpdb->prefix.'oauth2_refresh_tokens';
			}else{
				$tablename = $wpdb->prefix.'oauth2_access_tokens';
				}
		$token = $wpdb->get_results("SELECT * FROM $tablename WHERE oauth_token = '$token'", ARRAY_A );
		return $token[0];	
	}

	/**
	 * String Encryption
	 * 
	 * @param string $client_secret 
	 * @param string $client_id
	 * @return string
	 */
	protected function hash($client_secret, $client_id) {
		return hash('blowfish', $client_id . $client_secret);
	}
	
	/**
	 * Creates a unquie key
	 * 
	 * @return 40 Char string
	 */
	protected function generateKey (){
		return substr(sha1(microtime()),0,40);
		}
	
	/**
	 * Creates a unquie secret
	 * 
	 * @return 20 Char string
	 */
	protected function generateSecret (){
		return substr(sha1(microtime().time()),0,20);
		}
}
