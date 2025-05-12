<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\EncryptionInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\Jwt;

class CryptoToken implements CryptoTokenInterface
{

    protected $publicKeyStorage;
    protected $tokenStorage;
    protected $encryptionUtil;

    public function __construct(PublicKeyInterface $publicKeyStorage, AccessTokenInterface $tokenStorage = null, EncryptionInterface $encryptionUtil = null)
    {
        $this->publicKeyStorage = $publicKeyStorage;
        $this->tokenStorage = $tokenStorage;
        if (is_null($encryptionUtil)) {
            $encryptionUtil = new Jwt;
        }
        $this->encryptionUtil = $encryptionUtil;
    }

    public function getAccessToken($oauth_token)
    {
        if (!$tokenData = $this->encryptionUtil->decode($oauth_token, null, false)) {
            return false;
        }

        $client_id = isset($tokenData['client_id']) ? $tokenData['client_id'] : null;
        $public_key = $this->publicKeyStorage->getPublicKey($client_id);
        $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);

        if (false === $this->encryptionUtil->decode($oauth_token, $public_key, true)) {
            return false;
        }

        return $tokenData;
    }

    public function setAccessToken($oauth_token, $client_id, $uid, $expires, $scope = null)
    {
        if ($this->tokenStorage) {
            return $this->tokenStorage->setAccessToken($oauth_token, $client_id, $uid, $expires, $scope);
        }
    }

}
