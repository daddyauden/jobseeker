<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface as AccessTokenStorageInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\RefreshTokenInterface;

class AccessToken implements AccessTokenInterface
{

    protected $tokenStorage;
    protected $refreshStorage;
    protected $config;

    public function __construct(AccessTokenStorageInterface $tokenStorage, RefreshTokenInterface $refreshStorage = null, array $config = array())
    {
        $this->tokenStorage = $tokenStorage;
        if ($refreshStorage === null && $tokenStorage instanceof RefreshTokenInterface) {
            $this->refreshStorage = $tokenStorage;
        }
        $this->config = array_merge(array(
            'token_type' => 'bearer',
            'access_lifetime' => 3600,
            'refresh_token_lifetime' => 1209600,
                ), $config);
    }

    public function getAuthorizeResponse($params, $uid)
    {
        $result = array('query' => array());

        $includeRefreshToken = false;

        $result["fragment"] = $this->createAccessToken($params['client_id'], $uid, $params['scope'], $includeRefreshToken);

        if (isset($params['state'])) {
            $result["fragment"]["state"] = $params['state'];
        }

        return array($params['redirect_uri'], $result);
    }

    public function createAccessToken($client_id, $uid, $scope = null, $includeRefreshToken = true)
    {
        $token = array(
            "access_token" => $this->generateAccessToken(),
            "expires_in" => $this->config['access_lifetime'],
            "token_type" => $this->config['token_type'],
            "scope" => $scope
        );
        $result = $this->tokenStorage->setAccessToken($token["access_token"], $client_id, $this->config['access_lifetime'] ? time() + $this->config['access_lifetime'] : time(), $uid, $scope);

        if ($includeRefreshToken && $this->refreshStorage) {
            $token["refresh_token"] = $this->generateRefreshToken();
            $expires = 0;
            if ($this->config['refresh_token_lifetime'] > 0) {
                $expires = time() + $this->config['refresh_token_lifetime'];
            }
            $this->refreshStorage->setRefreshToken($token['refresh_token'], $client_id, $expires, $uid, $scope);
        }

        if ($result === false) {
            return false;
        }

        return $token;
    }

    protected function generateAccessToken()
    {
        $tokenLen = 40;
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(100, MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(100);
        } elseif (@file_exists('/dev/urandom')) {
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 100) . uniqid(mt_rand(), true);
        } else {
            $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        }

        return substr(hash('sha512', $randomData), 0, $tokenLen);
    }

    protected function generateRefreshToken()
    {
        return $this->generateAccessToken();
    }

}
