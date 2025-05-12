<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface;

class Memory implements AccessTokenInterface, AuthorizationCodeInterface, ClientCredentialsInterface, RefreshTokenInterface, ScopeInterface, UserCredentialsInterface, UserClaimsInterface
{

    public $accessTokens;
    public $authorizationCodes;
    public $clientCredentials;
    public $refreshTokens;
    public $defaultScope;
    public $supportedScopes;
    public $userCredentials;

    public function __construct($params = array())
    {
        $params = array_merge(array(
            'access_tokens' => array(),
            'authorization_codes' => array(),
            'client_credentials' => array(),
            'refresh_tokens' => array(),
            'default_scope' => array(),
            'supported_scopes' => array(),
            'user_credentials' => array()
                ), $params);

        $this->accessTokens = $params['access_tokens'];
        $this->authorizationCodes = $params['authorization_codes'];
        $this->clientCredentials = $params['client_credentials'];
        $this->refreshTokens = $params['refresh_tokens'];
        $this->defaultScope = $params['default_scope'];
        $this->supportedScopes = $params['supported_scopes'];
        $this->userCredentials = $params['user_credentials'];
    }

    public function getAccessToken($access_token)
    {
        return isset($this->accessTokens[$access_token]) ? $this->accessTokens[$access_token] : false;
    }

    public function setAccessToken($access_token, $client_id, $expires, $uid, $scope = null)
    {
        $this->accessTokens[$access_token] = compact('access_token', 'client_id', 'expires', 'uid', 'scope');

        return true;
    }

    public function getAuthorizationCode($authorization_code)
    {
        if (!isset($this->authorizationCodes[$authorization_code])) {
            return false;
        }

        return $this->authorizationCodes[$authorization_code];
    }

    public function setAuthorizationCode($authorization_code, $client_id, $redirect_uri, $expires, $uid, $scope = null)
    {
        $this->authorizationCodes[$authorization_code] = compact('authorization_code', 'client_id', 'redirect_uri', 'expires', 'uid', 'scope');

        return true;
    }

    public function expireAuthorizationCode($authorization_code)
    {
        unset($this->authorizationCodes[$authorization_code]);
        return true;
    }

    public function getClientDetail($client_id)
    {
        if (!isset($this->clientCredentials[$client_id])) {
            return false;
        }

        return $this->clientCredentials[$client_id];
    }

    public function setClientDetail($client_id, $client_secret, $redirect_uri = null, $grant_types = null, $scope = null)
    {
        $this->clientCredentials[$client_id] = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_types' => $grant_types,
            'scope' => $scope
        );

        return true;
    }

    public function getClientScope($client_id)
    {
        if (!$client = $this->getClientDetail($client_id)) {
            return false;
        }

        if (isset($client['scope'])) {
            return $client['scope'];
        }

        return false;
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        if (isset($this->clientCredentials[$client_id]['grant_types'])) {
            $grant_types = explode(' ', $this->clientCredentials[$client_id]['grant_types']);
            return in_array($grant_type, $grant_types);
        }

        return true;
    }

    public function checkClientCredentials($client_id, $client_secret)
    {
        return isset($this->clientCredentials[$client_id]['client_secret']) && $this->clientCredentials[$client_id]['client_secret'] === $client_secret;
    }

    public function isPublicClient($client_id)
    {
        if (isset($this->clientCredentials[$client_id])) {
            return true;
        }

        return false;
    }

    public function getRefreshToken($refresh_token)
    {
        return isset($this->refreshTokens[$refresh_token]) ? $this->refreshTokens[$refresh_token] : false;
    }

    public function setRefreshToken($refresh_token, $client_id, $expires, $uid, $scope = null)
    {
        $this->refreshTokens[$refresh_token] = compact('refresh_token', 'client_id', 'expires', 'uid', 'scope');

        return true;
    }

    public function unsetRefreshToken($refresh_token)
    {
        unset($this->refreshTokens[$refresh_token]);
    }

    public function scopeExists($scope)
    {
        $scope = explode(' ', trim($scope));

        return count(array_diff($scope, $this->supportedScopes)) === 0;
    }

    public function getDefaultScope()
    {
        return count($this->defaultScope) === 0 ? false : $this->defaultScope;
    }

    public function checkUserCredentials($email, $password)
    {
        $user = $this->getUserDetail($email);

        return $user && $user['password'] && $user['password'] === sha1($password);
    }

    public function getUserDetail($email)
    {
        if (!isset($this->userCredentials[$email])) {
            return false;
        }

        return $this->userCredentials[$email];
    }

    public function userExistsByUid($uid)
    {
        return isset($this->userCredentials[$uid]) ? true : false;
    }

    public function generateUid()
    {
        $times = self::UID_DIGIT;
        $randNum = "123456789";
        $randStr = "";
        for ($i = 0; $i < $times; $i++) {
            $randomIndex = mt_rand(0, 8);
            $randStr .= $randNum[$randomIndex];
        }
        $uid = (int) $randStr;
        $res = $this->userExistsByUid($uid);
        while ($res) {
            $this->generateUid();
        }
        return $uid;
    }

    public function setUser($email, $password, $username = null, $firstName = null, $lastName = null, $country = null)
    {
        $password = sha1($password);
        if (!isset($this->userCredentials[$email])) {
            $uid = $this->generateUid();
            $this->userCredentials[$email] = array(
                "uid" => $uid,
                "email" => $email,
                'password' => $password,
                "username" => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                "country" => $country,
                "addtime" => time()
            );
            $this->userCredentials[$uid] = array(
                "uid" => $uid,
                "email" => $email,
                'password' => $password,
                "username" => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                "country" => $country,
                "addtime" => time()
            );
        } else {
            $arr["password"] = $password;
            foreach (array("username" => "username", "firstName" => "first_name", "lastName" => "last_name", "country" => "country") as $key => $value) {
                if (${$key} !== null) {
                    $arr[$value] = ${$key};
                }
            }
            $this->userCredentials[$email] = $arr;
        }

        return true;
    }

    public function getUserClaims($email, $claims)
    {
        if (!$user = $this->getUserDetail($email)) {
            return false;
        }
        $claims = explode(' ', trim($claims));
        $userClaims = array();
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    $userClaims['address'] = $this->getUserClaim($validClaim, $user['address'] ? : $user);
                } else {
                    $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $user));
                }
            }
        }
        return $userClaims;
    }

    protected function getUserClaim($claim, $user)
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);
        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($user[$value]) ? $user[$value] : null;
        }
        return $userClaims;
    }

}
