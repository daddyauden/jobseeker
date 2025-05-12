<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface;

class Mongo implements AccessTokenInterface, AuthorizationCodeInterface, ClientCredentialsInterface, RefreshTokenInterface, ScopeInterface, UserCredentialsInterface, UserClaimsInterface
{

    protected $db;
    protected $config;

    public function __construct($connection, $config = array())
    {
        if ($connection instanceof \MongoDB) {
            $this->db = $connection;
        } else {
            if (!is_array($connection)) {
                throw new \InvalidArgumentException('First argument must be an instance of MongoDB or a configuration array');
            }
            $server = sprintf('mongodb://%s:%d', $connection['host'], $connection['port']);
            $m = new \MongoClient($server);
            $this->db = $m->{$connection['database']};
        }

        // Unix timestamps might get larger than 32 bits,
        // so let's add native support for 64 bit ints.
        ini_set('mongo.native_long', 1);

        $this->config = array_merge(array(
            'access_token_table' => 'access_token',
            'client_table' => 'client',
            'code_table' => 'authorization_code',
            'refresh_token_table' => 'refresh_token',
            'scope_table' => 'scope',
            'user_table' => 'user',
                ), $config);
    }

    protected function collection($name)
    {
        return $this->db->{$this->config[$name]};
    }

    public function getAccessToken($access_token)
    {
        $token = $this->collection('access_token_table')->findOne(array('access_token' => $access_token));

        return is_null($token) ? false : $token;
    }

    public function setAccessToken($access_token, $client_id, $expires, $uid, $scope = null)
    {
        if ($this->getAccessToken($access_token)) {
            $this->collection('access_token_table')->update(
                    array('access_token' => $access_token), array('$set' => array(
                    'client_id' => $client_id,
                    'expires' => $expires,
                    'uid' => $uid,
                    'scope' => $scope
                ))
            );
        } else {
            $this->collection('access_token_table')->insert(
                    array(
                        'access_token' => $access_token,
                        'client_id' => $client_id,
                        'expires' => $expires,
                        'uid' => $uid,
                        'scope' => $scope
                    )
            );
        }

        return true;
    }

    public function getAuthorizationCode($authorization_code)
    {
        $code = $this->collection('code_table')->findOne(array('authorization_code' => $authorization_code));

        return is_null($code) ? false : $code;
    }

    public function setAuthorizationCode($authorization_code, $client_id, $redirect_uri, $expires, $uid, $scope = null)
    {
        if ($this->getAuthorizationCode($authorization_code)) {
            $this->collection('code_table')->update(
                    array('authorization_code' => $authorization_code), array('$set' => array(
                    'client_id' => $client_id,
                    'redirect_uri' => $redirect_uri,
                    'expires' => $expires,
                    'uid' => $uid,
                    'scope' => $scope
                ))
            );
        } else {
            $this->collection('code_table')->insert(
                    array(
                        'authorization_code' => $authorization_code,
                        'client_id' => $client_id,
                        'redirect_uri' => $redirect_uri,
                        'expires' => $expires,
                        'uid' => $uid,
                        'scope' => $scope
                    )
            );
        }

        return true;
    }

    public function expireAuthorizationCode($authorization_code)
    {
        $this->collection('code_table')->remove(array('authorization_code' => $authorization_code));

        return true;
    }

    public function getClientDetail($client_id)
    {
        $client = $this->collection('client_table')->findOne(array('client_id' => $client_id));

        return is_null($client) ? false : $client;
    }

    public function setClientDetail($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null)
    {
        if ($this->getClientDetail($client_id)) {
            $this->collection('client_table')->update(
                    array('client_id' => $client_id), array('$set' => array(
                    'client_secret' => $client_secret,
                    'redirect_uri' => $redirect_uri,
                    'grant_types' => $grant_types,
                    'scope' => $scope
                ))
            );
        } else {
            $this->collection('client_table')->insert(
                    array(
                        'client_id' => $client_id,
                        'client_secret' => $client_secret,
                        'redirect_uri' => $redirect_uri,
                        'grant_types' => $grant_types,
                        'scope' => $scope
                    )
            );
        }

        return true;
    }

    public function getClientScope($client_id)
    {
        if (!$clientDetail = $this->getClientDetail($client_id)) {
            return false;
        }

        if (isset($clientDetail['scope'])) {
            return $clientDetail['scope'];
        }

        return false;
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $detail = $this->getClientDetail($client_id);
        if (isset($detail['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, $grant_types);
        }

        return true;
    }

    public function checkClientCredentials($client_id, $client_secret)
    {
        if ($result = $this->collection('client_table')->findOne(array('client_id' => $client_id))) {
            return $result['client_secret'] == $client_secret;
        }

        return false;
    }

    public function isPublicClient($client_id)
    {
        if ($this->collection('client_table')->findOne(array('client_id' => $client_id))) {
            return true;
        }

        return false;
    }

    public function getRefreshToken($refresh_token)
    {
        $token = $this->collection('refresh_token_table')->findOne(array('refresh_token' => $refresh_token));

        return is_null($token) ? false : $token;
    }

    public function setRefreshToken($refresh_token, $client_id, $expires, $uid, $scope = null)
    {
        $this->collection('refresh_token_table')->insert(
                array(
                    'refresh_token' => $refresh_token,
                    'client_id' => $client_id,
                    'expires' => $expires,
                    'uid' => $uid,
                    'scope' => $scope
                )
        );

        return true;
    }

    public function unsetRefreshToken($refresh_token)
    {
        $this->collection('refresh_token_table')->remove(array('refresh_token' => $refresh_token));

        return true;
    }

    public function scopeExists($scope)
    {
        $scope = explode(' ', trim($scope));
        $scopes = $this->collection('scope_table')->find();

        return count($scope) === $scopes->count();
    }

    public function getDefaultScope()
    {
        $scopeArr = array();
        $scopes = $this->collection('scope_table')->find(array('is_default' => 1));
        if ($scopes->count() > 0) {
            foreach ($scopes as $scope) {
                $scopeArr[] = $scope['scope'];
            }
            return implode(" ", $scopeArr);
        } else {
            return false;
        }
    }

    public function checkUserCredentials($email, $password)
    {
        if ($user = $this->getUser($email)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    public function getUserDetail($email)
    {
        return $this->getUser($email);
    }

    public function userExistsByUid($uid)
    {
        $user = $this->collection('user_table')->findOne(array("uid" => (int) $uid));
        return is_null($user) ? false : true;
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

        if ($this->getUser($email)) {
            $arr["password"] = $password;
            foreach (array("username" => "username", "firstName" => "first_name", "lastName" => "last_name", "country" => "country") as $key => $value) {
                if (${$key} !== null) {
                    $arr[$value] = ${$key};
                }
            }
            $this->collection('user_table')->update(array('email' => $email), array('$set' => $arr));
        } else {
            $uid = $this->generateUid();
            $this->collection('user_table')->insert(
                    array(
                        "uid" => $uid,
                        "email" => $email,
                        'password' => $password,
                        "username" => $username,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        "country" => $country,
                        "addtime" => time()
                    )
            );
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
        $user = $this->collection('user_table')->findOne(array('email' => $email));

        return is_null($user) ? false : $user;
    }

    protected function checkPassword($user, $password)
    {
        return $user['password'] === sha1($password);
    }

}
