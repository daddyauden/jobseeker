<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AuthorizationCodeInterface as AuthorizationCodeStorageInterface;

class AuthorizationCode implements AuthorizationCodeInterface
{

    protected $storage;
    protected $config;

    public function __construct(AuthorizationCodeStorageInterface $storage, array $config = array())
    {
        $this->storage = $storage;
        $this->config = array_merge(array(
            'enforce_redirect' => false,
            'auth_code_lifetime' => 30,
                ), $config);
    }

    public function getAuthorizeResponse($params, $uid)
    {

        $result = array('query' => array());

        $result['query']['code'] = $this->createAuthorizationCode($params['client_id'], $params['redirect_uri'], $uid, $params['scope']);

        if (isset($params['state'])) {
            $result['query']['state'] = $params['state'];
        }

        return array($params['redirect_uri'], $result);
    }

    public function createAuthorizationCode($client_id, $redirect_uri, $uid, $scope = null)
    {
        $code = $this->generateAuthorizationCode();
        $result = $this->storage->setAuthorizationCode($code, $client_id, $redirect_uri, time() + $this->config['auth_code_lifetime'], $uid, $scope);
        if ($result === false) {
            throw new \Exception("operate authorization_code error");
        }
        return $code;
    }

    public function enforceRedirect()
    {
        return $this->config['enforce_redirect'];
    }

    protected function generateAuthorizationCode()
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

}
