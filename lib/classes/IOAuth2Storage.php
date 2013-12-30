<?php
/**
 * All storage engines need to implement this interface in order to use OAuth2 server
 * 
 * @author David Rochwerger <catch.dave@gmail.com>
 */
 
 /**
  * Storage Interface
  */
interface IOAuth2Storage {

	/**
	 * Make sure that the client credentials is valid.
	 * 
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $client_secret
	 * (optional) If a secret is required, check that they've given the right one.
	 *
	 * @return TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
	 * @endcode
	 *
	 *
	 */
	public function checkClientCredentials($client_id, $client_secret);

	/**
	 * Get client details corresponding client_id.
	 *
	 * OAuth says we should store request URIs for each registered client.
	 * Implement this function to grab the stored URI for a given client id.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 *
	 * @return array
	 * Client details. Only mandatory item is the "registered redirect URI", and MUST
	 * return FALSE if the given client does not exist or is invalid.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function getClientDetails($client_id);

	/**
	 * Look up the supplied oauth_token from storage.
	 *
	 * We need to retrieve access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be check with.
	 *
	 * @return
	 * An associative array as below, and return NULL if the supplied oauth_token
	 * is invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration in unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @ingroup oauth2_section_7
	 */
	public function getAccessToken($oauth_token);

	/**
	 * Store the supplied access token values to storage.
	 *
	 * We need to store access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $user_id
	 * User identifier to be stored.
	 * @param $expires
	 * Expiration to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL);

	/**
	 * Check restricted grant types of corresponding client identifier.
	 *
	 * If you want to restrict clients to certain grant types, override this
	 * function.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $grant_type
	 * Grant type to be check with, would be one of the values contained in
	 * OAuth2::GRANT_TYPE_REGEXP.
	 *
	 * @return
	 * TRUE if the grant type is supported by this client identifier, and
	 * FALSE if it isn't.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkRestrictedGrantType($client_id, $grant_type);
}