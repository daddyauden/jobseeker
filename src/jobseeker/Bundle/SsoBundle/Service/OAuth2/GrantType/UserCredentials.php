<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\UserCredentialsInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;

class UserCredentials implements GrantTypeInterface
{

    private $userInfo;
    protected $storage;

    public function __construct(UserCredentialsInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getQuerystringIdentifier()
    {
        return 'password';
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }

    public function getClientId()
    {
        return null;
    }

    public function getUserId()
    {
        return $this->userInfo['uid'];
    }

    public function getScope()
    {
        return $this->userInfo['scope'];
    }

    public function validateRequest($request)
    {
        if (!$request->request->get("password") || !$request->request->get("username")) {
            return JsonResponse::create(OAuth2Exception::get("miss_username"));
        }

        if (!$this->storage->checkUserCredentials($request->request->get("username"), $request->request->get("password"))) {
            return JsonResponse::create(OAuth2Exception::get("invalid_username"));
        }

        $userInfo = $this->storage->getUserDetail($request->request->get("username"));

        if (false === $userInfo) {
            return JsonResponse::create(OAuth2Exception::get("error_username"));
        }

        $this->userInfo = $userInfo;

        return true;
    }

}
