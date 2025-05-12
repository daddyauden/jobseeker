<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\EncryptionInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\Jwt;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface as AccessTokenStorageInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\RefreshTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\PublicKeyInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\Memory;

class CryptoToken extends AccessToken
{

    protected $publicKeyStorage;
    protected $encryptionUtil;

    public function __construct(PublicKeyInterface $publicKeyStorage = null, AccessTokenStorageInterface $tokenStorage = null, RefreshTokenInterface $refreshStorage = null, array $config = array(), EncryptionInterface $encryptionUtil = null)
    {
        $this->publicKeyStorage = $publicKeyStorage;
        $config = array_merge(array(
            'store_encrypted_token_string' => true,
                ), $config);
        if (is_null($tokenStorage)) {
            $tokenStorage = new Memory();
        }
        if (is_null($encryptionUtil)) {
            $encryptionUtil = new Jwt();
        }
        $this->encryptionUtil = $encryptionUtil;
        parent::__construct($tokenStorage, $refreshStorage, $config);
    }

    public function createAccessToken($client_id, $uid, $scope = null, $includeRefreshToken = true)
    {
        $expires = time() + $this->config['access_lifetime'];
        $cryptoToken = array(
            'id' => $this->generateAccessToken(),
            'client_id' => $client_id,
            'uid' => $uid,
            'expires' => $expires,
            'token_type' => $this->config['token_type'],
            'scope' => $scope
        );

        $access_token = $this->encodeToken($cryptoToken, $client_id);

        $token_to_store = $this->config['store_encrypted_token_string'] ? $access_token : $cryptoToken['id'];
        $this->tokenStorage->setAccessToken($token_to_store, $client_id, $uid, $this->config['access_lifetime'] ? time() + $this->config['access_lifetime'] : null, $scope);

        $token = array(
            'access_token' => $access_token,
            'expires_in' => $this->config['access_lifetime'],
            'token_type' => $this->config['token_type'],
            'scope' => $scope
        );

        if ($includeRefreshToken && $this->refreshStorage) {
            $refresh_token = $this->generateRefreshToken();
            $expires = 0;
            if ($this->config['refresh_token_lifetime'] > 0) {
                $expires = time() + $this->config['refresh_token_lifetime'];
            }
            $this->refreshStorage->setRefreshToken($refresh_token, $client_id, $uid, $expires, $scope);
            $token['refresh_token'] = $refresh_token;
        }

        return $token;
    }

    protected function encodeToken(array $token, $client_id = null)
    {
        $private_key = $this->publicKeyStorage->getPrivateKey($client_id);
        $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);

        return $this->encryptionUtil->encode($token, $private_key, $algorithm);
    }

}
