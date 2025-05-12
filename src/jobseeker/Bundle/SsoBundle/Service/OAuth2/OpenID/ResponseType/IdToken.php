<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\ResponseType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\EncryptionInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Encryption\Jwt;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\PublicKeyInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface;

class IdToken implements IdTokenInterface
{

    protected $userClaimsStorage;
    protected $publicKeyStorage;
    protected $config;
    protected $encryptionUtil;

    public function __construct(UserClaimsInterface $userClaimsStorage, PublicKeyInterface $publicKeyStorage, array $config = array(), EncryptionInterface $encryptionUtil = null)
    {
        $this->userClaimsStorage = $userClaimsStorage;
        $this->publicKeyStorage = $publicKeyStorage;
        if (is_null($encryptionUtil)) {
            $encryptionUtil = new Jwt();
        }
        $this->encryptionUtil = $encryptionUtil;

        if (!isset($config['issuer'])) {
            throw new \LogicException('config parameter "issuer" must be set');
        }
        $this->config = array_merge(array(
            'id_lifetime' => 3600,
                ), $config);
    }

    public function getAuthorizeResponse($params, $userInfo = null)
    {

        $result = array('query' => array());
        $params += array('scope' => null, 'state' => null, 'nonce' => null);

        list($uid, $auth_time) = $this->getUserIdAndAuthTime($userInfo);
        $userClaims = $this->userClaimsStorage->getUserClaims($uid, $params['scope']);

        $id_token = $this->createIdToken($params['client_id'], $userInfo, $params['nonce'], $userClaims, null);
        $result["fragment"] = array('id_token' => $id_token);
        if (isset($params['state'])) {
            $result["fragment"]["state"] = $params['state'];
        }

        return array($params['redirect_uri'], $result);
    }

    public function createIdToken($client_id, $userInfo, $nonce = null, $userClaims = null, $access_token = null)
    {

        list($uid, $auth_time) = $this->getUserIdAndAuthTime($userInfo);

        $token = array(
            'iss' => $this->config['issuer'],
            'sub' => $uid,
            'aud' => $client_id,
            'iat' => time(),
            'exp' => time() + $this->config['id_lifetime'],
            'auth_time' => $auth_time,
        );

        if ($nonce) {
            $token['nonce'] = $nonce;
        }

        if ($userClaims) {
            $token += $userClaims;
        }

        if ($access_token) {
            $token['at_hash'] = $this->createAtHash($access_token, $client_id);
        }

        return $this->encodeToken($token, $client_id);
    }

    protected function createAtHash($access_token, $client_id = null)
    {
        $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);
        $hash_algorithm = 'sha' . substr($algorithm, 2);
        $hash = hash($hash_algorithm, $access_token);
        $at_hash = substr($hash, 0, strlen($hash) / 2);

        return $this->encryptionUtil->urlSafeB64Encode($at_hash);
    }

    protected function encodeToken(array $token, $client_id = null)
    {
        $private_key = $this->publicKeyStorage->getPrivateKey($client_id);
        $algorithm = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);

        return $this->encryptionUtil->encode($token, $private_key, $algorithm);
    }

    private function getUserIdAndAuthTime($userInfo)
    {
        $auth_time = null;

        if (is_array($userInfo)) {
            if (!isset($userInfo['uid'])) {
                throw new \LogicException('if $uid argument is an array, uid index must be set');
            }

            $auth_time = isset($userInfo['auth_time']) ? $userInfo['auth_time'] : null;
            $uid = $userInfo['uid'];
        } else {
            $uid = $userInfo;
        }

        if (is_null($auth_time)) {
            $auth_time = time();
        }

        return array($uid, $auth_time);
    }

}
