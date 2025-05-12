<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface;
use jobseeker\Bundle\ToolBundle\Service\Encrypt;

class Doctrine extends Encrypt implements AccessTokenInterface, AuthorizationCodeInterface, ClientCredentialsInterface, RefreshTokenInterface, ScopeInterface, UserCredentialsInterface, UserClaimsInterface
{

    protected $container;
    protected $objectManager;
    protected $entities;

    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->container = $container;
        $this->objectManager = $om;
        $this->entities = array(
            "access_token" => "jobseeker\Bundle\SsoBundle\Entity\Atoken",
            "authorization_code" => "jobseeker\Bundle\SsoBundle\Entity\Code",
            "client" => "jobseeker\Bundle\SsoBundle\Entity\Client",
            "refresh_token" => "jobseeker\Bundle\SsoBundle\Entity\Rtoken",
            'scope' => "jobseeker\Bundle\SsoBundle\Entity\Scope",
            'user' => "jobseeker\Bundle\SsoBundle\Entity\User",
        );
    }

    public function getAccessToken($access_token)
    {
        $atoken = $this->objectManager->getRepository($this->entities["access_token"])->findAtokenBy("accessToken", $access_token);

        return $atoken ? : false;
    }

    public function setAccessToken($access_token, $client_id, $expires, $uid, $scope = null)
    {
        if ($this->getAccessToken($access_token)) {
            try {
                $atoken = $this->objectManager->getRepository($this->entities["access_token"])->findOneBy(array("accessToken" => $access_token));
                $atoken->setClientId($client_id);
                $atoken->setExpires($expires);
                $atoken->setUid($uid);
                $atoken->setScope($scope);
                $this->objectManager->persist($atoken);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            try {
                $class = $this->entities["access_token"];
                $atoken = new $class();
                $atoken->setAccessToken($access_token);
                $atoken->setClientId($client_id);
                $atoken->setExpires($expires);
                $atoken->setUid($uid);
                $atoken->setScope($scope);
                $this->objectManager->persist($atoken);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    public function getAuthorizationCode($authorization_code)
    {
        $code = $this->objectManager->getRepository($this->entities["authorization_code"])->findCodeBy("authorizationCode", $authorization_code);

        return $code ? : false;
    }

    public function setAuthorizationCode($authorization_code, $client_id, $redirect_uri, $expires, $uid, $scope = null)
    {
        if ($this->getAuthorizationCode($authorization_code)) {
            try {
                $code = $this->objectManager->getRepository($this->entities["authorization_code"])->findOnBy(array("authorizationCode" => $authorization_code));
                $code->setClientId($client_id);
                $code->setRedirectUri($redirect_uri);
                $code->setExpires($expires);
                $code->setUid($uid);
                $code->setScope($scope);
                $this->objectManager->persist($code);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            try {
                $class = $this->entities["authorization_code"];
                $code = new $class();
                $code->setAuthorizationCode($authorization_code);
                $code->setClientId($client_id);
                $code->setRedirectUri($redirect_uri);
                $code->setExpires($expires);
                $code->setUid($uid);
                $code->setScope($scope);
                $this->objectManager->persist($code);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    public function expireAuthorizationCode($authorization_code)
    {
        if ($this->getAuthorizationCode($authorization_code)) {
            try {
                $code = $this->objectManager->getRepository($this->entities["authorization_code"])->findOneBy(array("authorizationCode" => $authorization_code));
                $this->objectManager->remove($code);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return true;
        }
    }

    public function getClientDetail($client_id)
    {
        $client = $this->objectManager->getRepository($this->entities["client"])->findClientBy("clientId", $client_id);

        return $client ? : false;
    }

    public function setClientDetail($client_id, $client_secret, $redirect_uri = null, $grant_types = null, $scope = null)
    {
        if ($this->getClientDetail($client_id)) {
            try {
                $client = $this->objectManager->getRepository($this->entities["client"])->findOneBy(array("clientId" => $client_id));
                $client->setClientSecret($client_secret);
                $client->setRedirectUri($redirect_uri);
                $client->setGrantTypes($grant_types);
                $client->setScope($scope);
                $this->objectManager->persist($client);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            try {
                $class = $this->entities["client"];
                $client = new $class();
                $client->setClientId($client_id);
                $client->setClientSecret($client_secret);
                $client->setRedirectUri($redirect_uri);
                $client->setGrantTypes($grant_types);
                $client->setScope($scope);
                $this->objectManager->persist($client);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    public function getClientScope($client_id)
    {
        if (!$client = $this->getClientDetail($client_id)) {
            return false;
        }
        return $client['scope'] ? : false;
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $client = $this->getClientDetail($client_id);
        if ($client !== false && $grant_types = $client['grantTypes']) {
            $grant_types = explode(' ', $grant_types);

            return in_array($grant_type, (array) $grant_types);
        }

        return true;
    }

    public function checkClientCredentials($client_id, $client_secret)
    {
        $client = $this->objectManager->getRepository($this->entities["client"])->findClientBy("clientId", $client_id);

        return $client && $client['clientSecret'] === $client_secret;
    }

    public function isPublicClient($client_id)
    {
        $client = $this->objectManager->getRepository($this->entities["client"])->findClientBy("clientId", $client_id);

        return $client ? true : false;
    }

    public function getRefreshToken($refresh_token)
    {
        $rtoken = $this->objectManager->getRepository($this->entities["refresh_token"])->findRtokenBy("refreshToken", $refresh_token);

        return $rtoken ? : false;
    }

    public function setRefreshToken($refresh_token, $client_id, $expires, $uid, $scope = null)
    {
        try {
            $class = $this->entities["refresh_token"];
            $rtoken = new $class();
            $rtoken->setRefreshToken($refresh_token);
            $rtoken->setClientId($client_id);
            $rtoken->setExpires($expires);
            $rtoken->setUid($uid);
            $rtoken->setScope($scope);
            $this->objectManager->persist($rtoken);
            $this->objectManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function unsetRefreshToken($refresh_token)
    {
        if ($this->getRefreshToken($refresh_token)) {
            try {
                $rtoken = $this->objectManager->getRepository($this->entities["refresh_token"])->findOneBy(array("refreshToken" => $refresh_token));
                $this->objectManager->remove($rtoken);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return true;
        }
    }

    public function scopeExists($scope)
    {
        $scopeArr = explode(' ', $scope);
        $count = $this->objectManager->getRepository($this->entities["scope"])->getMatchScopeNum();

        return (int) $count === count($scopeArr);
    }

    public function getDefaultScope()
    {
        $res = array();
        $scopes = $this->objectManager->getRepository($this->entities["scope"])->findScopeBy("isDefault", 1);
        if (count($scopes) > 0) {
            foreach ($scopes as $scope) {
                $res[] = $scope['scope'];
            }

            return implode(' ', $res);
        } else {
            return false;
        }
    }

    public function checkUserCredentials($email, $password)
    {
        $user = $this->getUserDetail($email);

        return $user && ($user['password'] === $this->encrypt($password) || $user['password'] === $password);
    }

    public function getUserDetail($email)
    {
        $user = $this->objectManager->getRepository($this->entities["user"])->findUserBy("email", $email);

        return $user ? : false;
    }

    public function getUserByUid($uid)
    {
        $user = $this->objectManager->getRepository($this->entities["user"])->findUserBy("uid", (int) $uid);

        return $user ? : false;
    }

    public function getUserBySEP($source, $email, $password)
    {
        $user = $this->getUserDetail($email);

        if ($user && $source === $user['source'] && $user['password'] === $password) {
            return $user;
        } else {
            return false;
        }
    }

    public function getUserByEP($email, $password)
    {
        $user = $this->getUserDetail($email);

        if ($user && $user['password'] === $password) {
            return $user;
        } else {
            return false;
        }
    }

    public function userExistsByUid($uid)
    {
        $user = $this->getUserByUid($uid);

        return $user === false ? false : true;
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
        $password = $this->encrypt($password);

        if ($this->getUserDetail($email)) {
            try {
                $user = $this->objectManager->getRepository($this->entities["user"])->findOneBy(array("email" => $email));
                $user->setPassword($password);

                $username !== null && $user->setUsername($username);

                $firstName !== null && $user->setFirstName($firstName);

                $lastName !== null && $user->setLastName($lastName);

                $country !== null && $user->setCountry($country);

                $this->objectManager->persist($user);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            try {
                $class = $this->entities["user"];
                $uid = $this->generateUid();
                $user = new $class();
                $user->setUid($uid);
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setAddtime(time());

                $username !== null && $user->setUsername($username);

                $firstName !== null && $user->setFirstName($firstName);

                $lastName !== null && $user->setLastName($lastName);

                $country !== null && $user->setCountry($country);

                $this->objectManager->persist($user);
                $this->objectManager->flush();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    public function getUserClaims($uid, $claims)
    {
        if (!$user = $this->getUserByUid($uid)) {
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

    final public function encrypt($data)
    {
        $method = self::ENCRYPT;

        return $method($data);
    }

}
