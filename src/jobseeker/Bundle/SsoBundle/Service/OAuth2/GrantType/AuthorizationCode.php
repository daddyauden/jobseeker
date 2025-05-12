<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AuthorizationCodeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OAuth2Exception;

class AuthorizationCode implements GrantTypeInterface
{

    protected $storage;
    protected $authCode;

    public function __construct(AuthorizationCodeInterface $storage)
    {
        $this->storage = $storage;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $uid, $scope)
    {

        $token = $accessToken->createAccessToken($client_id, $uid, $scope);
        $result = $this->storage->expireAuthorizationCode($this->authCode['code']);
        if ($result === false) {
            return false;
        }
        return $token;
    }

    public function getClientId()
    {
        return $this->authCode['clientId'];
    }

    public function getQuerystringIdentifier()
    {
        return 'authorization_code';
    }

    public function getScope()
    {
        return isset($this->authCode['scope']) ? $this->authCode['scope'] : null;
    }

    public function getUserId()
    {
        return isset($this->authCode['uid']) ? $this->authCode['uid'] : null;
    }

    public function validateRequest($request)
    {
        if (!$request->request->has('code')) {
            return JsonResponse::create(OAuth2Exception::get("miss_code"));
        }

        $code = $request->request->get('code');

        if (!$authCode = $this->storage->getAuthorizationCode($code)) {
            return JsonResponse::create(OAuth2Exception::get("invalid_code"));
        }

        if (isset($authCode['redirect_uri']) && $authCode['redirect_uri']) {
            if (!$request->request->has('redirect_uri') || urldecode($request->request->get('redirect_uri')) != $authCode['redirect_uri']) {
                return JsonResponse::create(OAuth2Exception::get("mismatch_redirect_uri"));
            }
        }

        if ($authCode["expires"] < time()) {
            return JsonResponse::create(OAuth2Exception::get("expires_code"));
        }

        if (!isset($authCode['code'])) {
            $authCode['code'] = $code;
        }

        $this->authCode = $authCode;

        return true;
    }

}
