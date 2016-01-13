<?php
/**
 *
 * @todo Setup refresh token option. Currently it is set to always true
 */

namespace OAuth2\OpenID\GrantType;

use OAuth2\GrantType\AuthorizationCode as BaseAuthorizationCode;
use OAuth2\ResponseType\AccessTokenInterface;

/**
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class AuthorizationCode extends BaseAuthorizationCode {
    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope) {
        $includeRefreshToken = true;

        $config = get_option("wo_options");
        /*
        if ( isset( $this->authCode['id_token'] ) ) {

            // Issue a refresh token when "offline_access" is presented
            // http://openid.net/specs/openid-connect-core-1_0-17.html#OfflineAccess
            // 
            // The document states that a server "MAY" issue a refresh token outside of the "offline_access"
            // and since there is a paramter "always_issue_refresh_token" we can hook into that 
            $scopes = explode(' ', trim($scope));
            if(in_array('offline_access', $scopes) || $config['refresh_tokens_enabled']){
                $includeRefreshToken = true;
            }
        }
        */

        $token = $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
        if ( isset( $this->authCode['id_token'] ) ) {
            $token['id_token'] = $this->authCode['id_token'];
        }
        
        $this->storage->expireAuthorizationCode( $this->authCode['code'] );

        return $token;
    }
}
