<?php

namespace jobseeker\Bundle\SsoBundle\Storage;

use jobseeker\Bundle\SsoBundle\OpenID\Storage\UserClaimsInterface;

class Redis implements AccessTokenInterface, AuthorizationCodeInterface, ClientCredentialsInterface, RefreshTokenInterface, ScopeInterface, UserCredentialsInterface, UserClaimsInterface
{

    protected $redis;
    protected $config;
    private $cache;

    public function __construct($redis, array $config = array())
    {
        $this->redis = $redis;
        $this->config = array_merge(array(
            'access_token_key' => 'access_token:',
            'client_key' => 'client:',
            'code_key' => 'authorization_code:',
            'refresh_token_key' => 'refresh_token:',
            'user_key' => 'user:',
            'scope_key' => 'scope:',
                ), $config);
    }

    protected function getValue($key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $value = $this->redis->get($key);
        if (isset($value)) {
            return json_decode($value, true);
        } else {
            return false;
        }
    }

    protected function setValue($key, $value, $expire = 0)
    {
        $this->cache[$key] = $value;
        $str = json_encode($value);
        if ($expire > 0) {
            $seconds = $expire - time();
            $ret = $this->redis->setex($key, $seconds, $str);
        } else {
            $ret = $this->redis->set($key, $str);
        }

        return is_bool($ret) ? $ret : $ret->getPayload() == 'OK';
    }

    protected function expireValue($key)
    {
        unset($this->cache[$key]);

        return $this->redis->del($key);
    }

    public function getAccessToken($access_token)
    {
        return $this->getValue($this->config['access_token_key'] . $access_token);
    }

    public function setAccessToken($access_token, $client_id, $expires, $uid, $scope = null)
    {
        return $this->setValue($this->config['access_token_key'] . $access_token, compact('access_token', 'client_id', 'expires', 'uid', 'scope'), $expires);
    }

    public function getAuthorizationCode($authorization_code)
    {
        return $this->getValue($this->config['code_key'] . $authorization_code);
    }

    public function setAuthorizationCode($authorization_code, $client_id, $redirect_uri, $expires, $uid, $scope = null)
    {
        return $this->setValue($this->config['code_key'] . $authorization_code, compact('authorization_code', 'client_id', 'redirect_uri', 'expires', 'uid', 'scope'), $expires);
    }

    public function expireAuthorizationCode($authorization_code)
    {
        $key = $this->config['code_key'] . $authorization_code;

        return $this->expireValue($key);
    }

    public function getClientDetail($client_id)
    {
        return $this->getValue($this->config['client_key'] . $client_id);
    }

    public function setClientDetail($client_id, $client_secret, $redirect_uri = null, $grant_types = null, $scope = null)
    {
        return $this->setValue($this->config['client_key'] . $client_id, compact('client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope'));
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
        $client = $this->getClientDetail($client_id);
        if (isset($client['grant_types'])) {
            $grant_types = explode(' ', $client['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        return true;
    }

    public function checkClientCredentials($client_id, $client_secret)
    {
        if (!$client = $this->getClientDetail($client_id)) {
            return false;
        }

        return isset($client['client_secret']) && $client['client_secret'] === $client_secret;
    }

    public function isPublicClient($client_id)
    {
        if ($this->getClientDetail($client_id)) {
            return true;
        }

        return false;
    }

    public function getRefreshToken($refresh_token)
    {
        return $this->getValue($this->config['refresh_token_key'] . $refresh_token);
    }

    public function setRefreshToken($refresh_token, $client_id, $expires, $uid, $scope = null)
    {
        return $this->setValue($this->config['refresh_token_key'] . $refresh_token, compact('refresh_token', 'client_id', 'expires', 'uid', 'scope'), $expires);
    }

    public function unsetRefreshToken($refresh_token)
    {
        return $this->expireValue($this->config['refresh_token_key'] . $refresh_token);
    }

    public function scopeExists($scope)
    {
        $scope = explode(' ', $scope);

        $result = $this->getValue($this->config['scope_key'] . 'supported:global');

        $supportedScope = explode(' ', (string) $result);

        return (count(array_diff($scope, $supportedScope)) === 0);
    }

    public function getDefaultScope()
    {

        return $this->getValue($this->config['scope_key'] . 'default:global');
    }

    public function checkUserCredentials($email, $password)
    {
        $user = $this->getUserDetail($email);

        return $user && $user['password'] === $password;
    }

    public function getUserDetail($email)
    {
        return $this->getUser($email);
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
        if ($this->getUserDetail($email)) {
            $arr["password"] = $password;
            foreach (array("username" => "username", "firstName" => "first_name", "lastName" => "last_name", "country" => "country") as $key => $value) {
                if (${$key} !== null) {
                    $arr[$value] = ${$key};
                }
            }
            $this->setValue($this->config['user_key'] . $email, $arr);
        } else {
            $uid = $this->generateUid();
            $arr['uid'] = $uid;
            $arr['email'] = $email;
            $arr["password"] = $password;
            foreach (array("username" => "username", "firstName" => "first_name", "lastName" => "last_name", "country" => "country") as $key => $value) {
                if (${$key} !== null) {
                    $arr[$value] = ${$key};
                }
            }
            $this->setValue($this->config['user_key'] . $email, $arr);
            $this->setValue($this->config['user_key'] . $uid, $arr);
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

    protected function getUser($email)
    {
        if (!$user = $this->getValue($this->config['user_key'] . $email)) {
            return false;
        }

        return $user;
    }

    public function setScope($scope, $type = 'supported')
    {
        if (!in_array($type, array('default', 'supported'))) {
            throw new \InvalidArgumentException('"$type" must be one of "default", "supported"');
        }

        $key = $this->config['scope_key'] . $type . ':global';

        return $this->setValue($key, $scope);
    }

}
