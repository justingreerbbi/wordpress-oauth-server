<?php
/**
 * OAuth_Client.php
 *
 * OAuth Client Class. This is bare bones as of now and will be updated
 * as time goes on.
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @version 1.2.0
 * @copyright 2013 Justin Greer Interactive, LLC
 * @license GPL2
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * in your development cycle save you a lot of time by preventing you having to rewrite<br>
 * major documentation parts to generate some usable form of documentation.
 */

class OAuth_Client {
	
	// Client Version
	protected $Version = '1.2.0';
	
	// Client ID
	public $Client_ID = '';
	
	// Client Secret
	public $Client_Secret = '';
	
	// OAuth Server URL
	public $End_Point = '';
	
	/**
	 * Config Method
	 *
	 * MUST be ran FIRST. Sets client class options
	 *
	 * @param {Array} $Options Configuration options
	 * @return {N/A} Simply sets local configuration options for later use
	 *
	 * @todo Add required check to make sure everything needed was provided
	 */
	public function Config( $Options ){
		foreach( $Options as $Key => $Value ){
			$this->$Key = $Value;
			}	
		}

	/**
	 * Request Token Method
	 *
	 * After authenicating the user will be redirected back to ths client with a code
	 * in the url. This code us a unique key for authenticating this client and recent authenticated user
	 *
	 * @param $Code {String} The code returned when a user is redirected back after a successful authentication
	 * @return $this->Get_Response
	 */
	public function Request_Token( $Code ){
			$url = $this->End_Point .'/oauth/request_token/?code=' . $Code . '&grant_type=authorization_code&client_id='.$this->Client_ID.'&client_secret='. $this->Client_Secret;		
			return $this->Get_Response( $url );
		}
	
	/**
	 * Request Access Method
	 *
	 * Once the user is authenticated, the client server must be authenticated as well
	 * Request_Token returns a token if everything went ok so far.
	 *
	 * @param $Token {String} The token returned when the server return a token
	 * @return $this->Get_Response
	 */
	public function Request_Access( $Token ){
			$url = $this->End_Point .'/oauth/request_access/?access_token=' . $Token;
			return $this->Get_Response( $url );
		}
	
	/**
	 * Get Response Method
	 *
	 * Handles all the commincation with the OAuth server and client server
	 *
	 * @param $Url {String} URL being passed to use. Each method builds its own code.
	 * @return JSON decoded response from the OAuth 2.0 server
	 */
	protected function Get_Response( $Url ){
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $Url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			return json_decode($data);
		}
	
	
	}