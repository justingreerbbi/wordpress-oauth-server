<?php
namespace OAuth2\Storage;

use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;

/**
 * Simple WordPress Database Object Layer
 *
 * NOTE: This class is a modified version of the PDO object by Brent Shaffer
 * 
 * @org-author Brent Shaffer <bshafs at gmail dot com>
 * @author Justin Greer <justin@justin-greer.com>
 */
class Wordpressdb implements 
    AuthorizationCodeInterface, 
    AccessTokenInterface, 
    ClientCredentialsInterface, 
    UserCredentialsInterface, 
    RefreshTokenInterface, 
    JwtBearerInterface, 
    ScopeInterface, 
    PublicKeyInterface, 
    UserClaimsInterface, 
    OpenIDAuthorizationCodeInterface
{
    protected $db;
    protected $config;
    
    /**
     * [__construct description]
     * @param array $config Configuration for the Wpo Stroage Object provided by the developer
     */
    public function __construct( $config=array() ) 
    {
        global $wpdb;
        $this->db = $wpdb;

        $this->config = array_merge(
            array(
                'client_table' => $this->db->prefix . 'oauth_clients', 
                'access_token_table' => $this->db->prefix . 'oauth_access_tokens', 
                'refresh_token_table' => $this->db->prefix . 'oauth_refresh_tokens', 
                'code_table' => $this->db->prefix . 'oauth_authorization_codes', 
                'user_table' => $this->db->prefix . 'oauth_users', 
                'jwt_table' => $this->db->prefix . 'oauth_jwt', 
                'scope_table' => $this->db->prefix . 'oauth_scopes', 
                'public_key_table' => $this->db->prefix . 'oauth_public_keys'
                ), 
            $config
            );
    }
    
    /**
     * [checkClientCredentials description]
     * @param  [type] $client_id     [description]
     * @param  [type] $client_secret [description]
     * @return [type]                [description]
     */
    public function checkClientCredentials( $client_id, $client_secret=null ) 
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}oauth_clients WHERE client_id = %s", array($client_id));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        return $stmt && $stmt['client_secret'] == $client_secret;
    }
    
    /**
     * [isPublicClient description]
     * @param  [type]  $client_id [description]
     * @return boolean            [description]
     */
    public function isPublicClient($client_id) 
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}oauth_clients WHERE client_id = %s", array($client_id));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        return empty($stmt['client_secret']);
    }
    
    /**
     * [getClientDetails description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getClientDetails($client_id) 
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}oauth_clients WHERE client_id = %s", array($client_id));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        return $stmt;
    }
    
    /**
     * [setClientDetails description]
     * @param [type] $client_id     [description]
     * @param [type] $client_secret [description]
     * @param [type] $redirect_uri  [description]
     * @param [type] $grant_types   [description]
     * @param [type] $scope         [description]
     * @param [type] $user_id       [description]
     */
    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null) 
    {
        if ($this->getClientDetails($client_id)) {
            $stmt = $this->db->prepare("UPDATE {$this->db->prefix}oauth_clients SET client_secret=%s, redirect_uri=%s, grant_types=%s, scope=%s, user_id=%s where client_id=%s", array($client_secret, $redirect_uri, $grant_types, $scope, $user_id, $client_id));
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->db->prefix}oauth_clients (client_id, client_secret, redirect_uri, grant_types, scope, user_id) VALUES (%s, %s, %s, %s, %s, $s)", array($client_secret, $redirect_uri, $grant_types, $scope, $user_id, $client_id));
        }
        
        return $this->db->query($stmt);
    }
    
    /**
     * [checkRestrictedGrantType description]
     * @param  [type] $client_id  [description]
     * @param  [type] $grant_type [description]
     * @return [type]             [description]
     */
    public function checkRestrictedGrantType($client_id, $grant_type) 
    {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);
            
            return in_array($grant_type, (array)$grant_types);
        }
        
        return true;
    }
    
    /**
     * [getAccessToken description]
     * @param  [type] $access_token [description]
     * @return [type]               [description]
     */
    public function getAccessToken($access_token) 
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}oauth_access_tokens WHERE access_token = %s", array($access_token));
        $token = $this->db->get_row($stmt, ARRAY_A);
        if (null != $token) {
            $token['expires'] = strtotime($token['expires']);
        }
        
        return $token;
    }
    
    /**
     * [setAccessToken description]
     * @param [type] $access_token [description]
     * @param [type] $client_id    [description]
     * @param [type] $user_id      [description]
     * @param [type] $expires      [description]
     * @param [type] $scope        [description]
     */
    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope=null) 
    {
        $expires = date('Y-m-d H:i:s', $expires);
        if ($this->getAccessToken($access_token)) {
            $stmt = $this->db->prepare("UPDATE {$this->db->prefix}oauth_access_tokens SET client_id=%s, expires=%s, user_id=%s, scope=%s where access_token=%s", array($client_id, $expires, $user_id, $scope, $access_token));
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->db->prefix}oauth_access_tokens (access_token, client_id, expires, user_id, scope) VALUES (%s, %s, %s, %s, %s)", array($access_token, $client_id, $expires, $user_id, $scope));
        }
        return $this->db->query($stmt);
    }
    
    /**
     * [getAuthorizationCode description]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function getAuthorizationCode($code) 
    {
        $stmt = $this->db->prepare("SELECT * from {$this->db->prefix}oauth_authorization_codes WHERE authorization_code = %s", array($code));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        if (null != $stmt) $stmt['expires'] = strtotime($stmt['expires']);
        
        /**
         * This seems to be an issue and not return correctly. For now, lets return the queried object
         * @todo This is messy and we need to look up PDO::FEATCH_BOTH
         */
        return $stmt;
    }
    
    /**
     * [setAuthorizationCode description]
     * @param [type] $code         [description]
     * @param [type] $client_id    [description]
     * @param [type] $user_id      [description]
     * @param [type] $redirect_uri [description]
     * @param [type] $expires      [description]
     * @param [type] $scope        [description]
     * @param [type] $id_token     [description]
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope=null, $id_token=null) 
    {
        if (func_num_args() > 6) {
            
            // we are calling with an id token
            return call_user_func_array(array($this, 'setAuthorizationCodeWithIdToken'), func_get_args());
        }
        
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        
        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $stmt = $this->db->prepare("UPDATE {$this->db->prefix}oauth_authorization_codes SET client_id=%s, user_id=%s, redirect_uri=%s, expires=%s, scope=%s where authorization_code=%s", array($client_id, $user_id, $redirect_uri, $expires, $code));
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->db->prefix}oauth_authorization_codes (authorization_code, client_id, user_id, redirect_uri, expires, scope) VALUES (%s, %s, %s, %s, %s, %s)", array($code, $client_id, $user_id, $redirect_uri, $expires, $scope));
        }
        return $this->db->query($stmt);
    }
    
    /**
     * [setAuthorizationCodeWithIdToken description]
     * @param [type] $code         [description]
     * @param [type] $client_id    [description]
     * @param [type] $user_id      [description]
     * @param [type] $redirect_uri [description]
     * @param [type] $expires      [description]
     * @param [type] $scope        [description]
     * @param [type] $id_token     [description]
     */
    private function setAuthorizationCodeWithIdToken($code, $client_id, $user_id, $redirect_uri, $expires, $scope=null, $id_token=null) 
    { 
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        
        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $stmt = $this->db->prepare("UPDATE {$this->db->prefix}oauth_authorization_codes SET client_id=%s, user_id=%s, redirect_uri=%s, expires=%s, scope=%s, id_token =%s where authorization_code=%s", array($client_id, $user_id, $redirect_uri, $expires, $scope, $id_token, $code) );
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->db->prefix}oauth_authorization_codes (authorization_code, client_id, user_id, redirect_uri, expires, scope, id_token) VALUES (%s, %s, %s, %s, %s, %s, %s)", array($code, $client_id, $user_id, $redirect_uri, $expires, $scope, $id_token) );
        }
        
        return $this->db->query( $stmt );
    }
    
    /**
     * [expireAuthorizationCode description]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function expireAuthorizationCode( $code ) 
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->db->prefix}oauth_authorization_codes WHERE authorization_code = %s", array($code));
        return $this->db->query( $stmt );
    }
    
    /**
     * [checkUserCredentials description]
     * @param  [type] $username [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public function checkUserCredentials( $username, $password ) 
    {
        if ($user = $this->getUser( $username) ) {
            return $this->checkPassword($user, $password);
        }
        
        return false;
    }
    
    /**
     * [getUserDetails description]
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function getUserDetails( $username ) 
    {
        return $this->getUser( $username );
    }
    
    /**
     * [getUserClaims description]
     * @param  [type] $user_id [description]
     * @param  [type] $claims  [description]
     * @return [type]          [description]
     *
     * @todo FIND DEVELOPER THAT UNDERSTANDS CLAIM IDENTIFICATION
     */
    public function getUserClaims($user_id, $claims) 
    {
        if (!$userDetails = $this->getUserDetails($user_id)) {
            return false;
        }
        
        $claims = explode(' ', trim($claims));
        $userClaims = array();
        
        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    
                    // address is an object with subfields
                    $userClaims['address'] = $this->getUserClaim($validClaim, $userDetails['address'] ? : $userDetails);
                } else {
                    $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
                }
            }
        }
        
        return $userClaims;
    }
    
    /**
     * [getUserClaim description]
     * @param  [type] $claim       [description]
     * @param  [type] $userDetails [description]
     * @return [type]              [description]
     *
     * @todo Check
     */
    protected function getUserClaim($claim, $userDetails) 
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);
        
        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        }
        
        return $userClaims;
    }
    
    /**
     * [getRefreshToken description]
     * @param  [type] $refresh_token [description]
     * @return [type]                [description]
     */
    public function getRefreshToken( $refresh_token ) 
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}oauth_refresh_tokens WHERE refresh_token = %s", array($refresh_token));
        $stmt = $this->db->get_row($stmt);
        
        if (null != $stmt) {
            $token['expires'] = strtotime($token['expires']);
        }
        
        return $token;
    }
    
    /**
     * [setRefreshToken description]
     * @param [type] $refresh_token [description]
     * @param [type] $client_id     [description]
     * @param [type] $user_id       [description]
     * @param [type] $expires       [description]
     * @param [type] $scope         [description]
     */
    public function setRefreshToken( $refresh_token, $client_id, $user_id, $expires, $scope=null) 
    {
        $expires = date('Y-m-d H:i:s', $expires);
        $stmt = $this->db->prepare("INSERT INTO {$this->db->prefix}oauth_refresh_tokens (refresh_token, client_id, user_id, expires, scope) VALUES (%s, %s, %s, %s, %s)", array($refresh_token, $client_id, $user_id, $expires, $scope));
        
        return $this->db->query($stmt);
    }
    
    /**
     * [unsetRefreshToken description]
     * @param  [type] $refresh_token [description]
     * @return [type]                [description]
     */
    public function unsetRefreshToken($refresh_token) {
        $stmt = $this->db->prepare("DELETE FROM {$this->db->prefix}oauth_refresh_tokens WHERE refresh_token = %s", array($refresh_token));
        
        return $this->db->query($stmt);
    }
    
    /**
     * Encrypt password
     * @param  [type] $user     [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     *
     * @todo Check for Removal
     */
    protected function checkPassword($user, $password) 
    {
        return wp_check_password( $password, $user['user_pass'], $user['ID']);
    }
    
    /**
     * Retrieve a user ID from the database
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public function getUser($username) 
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}users WHERE user_login=%s", array($username));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        if ( null == $stmt ) {
            return false;
        }
    
        $userInfo = $stmt;
        return array_merge(
            array(
                'user_id' => $userInfo['ID']), 
            $userInfo
            );
    }
    
    /**
     * Create a new user into in WordPress
     * @param [type] $username  [description]
     * @param [type] $password  [description]
     * @param [type] $firstName [description]
     * @param [type] $lastName  [description]
     *
     * @todo Check for Removal
     */
    public function setUser($username, $password, $firstName=null, $lastName=null) 
    {
        
        // do not store in plaintext
        $password = sha1($password);
        
        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where username=:username', $this->config['user_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (username, password, first_name, last_name) VALUES (:username, :password, :firstName, :lastName)', $this->config['user_table']));
        }
        
        return $stmt->execute(compact('username', 'password', 'firstName', 'lastName'));
    }
    
    /**
     * Check to see is a scope exists in the database
     * @param  [type] $scope [description]
     * @return [type]        [description]
     */
    public function scopeExists( $scope ) 
    {
        $scope = explode(' ', $scope);
        $whereIn = implode(',', array_fill(0, count($scope), '?'));
        $stmt = $this->db->prepare("SELECT count(scope) as count FROM {$this->db->prefix}oauth_scopes WHERE scope IN (%s)", array($whereIn) );
        $stmt = $this->db->query($stmt, ARRAY_A);
        
        if ( null != $stmt ) {
            return $stmt['count'] == count( $scope );
        }
        
        return false;
    }
    
    /**
     * Get the default scope from the database
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getDefaultScope( $client_id=null ) 
    {
        $stmt = $this->db->prepare("SELECT scope FROM {$this->db->prefix}oauth_scopes WHERE is_default=%s", array(true));
        $stmt = $this->db->get_results($stmt, ARRAY_A);
        
        if ($stmt) {
            $defaultScope = array_map(function ($row) {
                return $row['scope'];
            }, $result);
            
            return implode(' ', $defaultScope);
        }
        
        return null;
    }
    
    /**
     * [getClientKey description]
     * @param  [type] $client_id [description]
     * @param  [type] $subject   [description]
     * @return [type]            [description]
     */
    public function getClientKey($client_id, $subject) 
    {
        $stmt = $this->db->prepare("SELECT public_key from {$this->db->prefix}oauth_jwt where client_id=%s AND subject=%s", array($client_id, $subject));
        return $this->db->get_col($stmt);
    }
    
    /**
     * [getClientScope description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getClientScope($client_id) 
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }
        
        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }
        
        return null;
    }
    
    /**
     * [getJti description]
     * @param  [type] $client_id [description]
     * @param  [type] $subject   [description]
     * @param  [type] $audience  [description]
     * @param  [type] $expires   [description]
     * @param  [type] $jti       [description]
     * @return [type]            [description]
     *
     * @todo  Check for Removal
     */
    public function getJti($client_id, $subject, $audience, $expires, $jti) 
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT * FROM %s WHERE issuer=:client_id AND subject=:subject AND audience=:audience AND expires=:expires AND jti=:jti', $this->config['jti_table']));
        
        $stmt->execute(compact('client_id', 'subject', 'audience', 'expires', 'jti'));
        
        if ($result = $stmt->fetch()) {
            return array('issuer' => $result['issuer'], 'subject' => $result['subject'], 'audience' => $result['audience'], 'expires' => $result['expires'], 'jti' => $result['jti'],);
        }
        
        return null;
    }
    
    /**
     * [setJti description]
     * @param [type] $client_id [description]
     * @param [type] $subject   [description]
     * @param [type] $audience  [description]
     * @param [type] $expires   [description]
     * @param [type] $jti       [description]
     *
     * @todo  Check for removal
     */
    public function setJti($client_id, $subject, $audience, $expires, $jti) 
    {
        $stmt = $this->db->prepare(sprintf('INSERT INTO %s (issuer, subject, audience, expires, jti) VALUES (:client_id, :subject, :audience, :expires, :jti)', $this->config['jti_table']));
        
        return $stmt->execute(compact('client_id', 'subject', 'audience', 'expires', 'jti'));
    }
    
    /**
     * [getPublicKey description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getPublicKey($client_id = null) 
    {
        $stmt = $this->db->prepare("SELECT public_key FROM {$this->db->prefix}oauth_public_keys WHERE client_id=%s OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC", array($client_id));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        if (null != $stmt) {
            return $result['public_key'];
        }
    }
    
    /**
     * [getPrivateKey description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getPrivateKey($client_id = null) 
    {
        $stmt = $this->db->prepare("SELECT private_key FROM {$this->db->prefix}oauth_public_keys WHERE client_id=%s OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC", array($client_id));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        if (null != $stmt) {
            return $result['private_key'];
        }
    }
    
    /**
     * [getEncryptionAlgorithm description]
     * @param  [type] $client_id [description]
     * @return [type]            [description]
     */
    public function getEncryptionAlgorithm($client_id = null) 
    {
        $stmt = $this->db->prepare("SELECT encryption_algorithm FROM {$this->db->prefix}oauth_public_keys WHERE client_id=%s OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC", array($client_id));
        $stmt = $this->db->get_row($stmt, ARRAY_A);
        
        if (null != $stmt) {
            return $result['encryption_algorithm'];
        }
    }
    
}
