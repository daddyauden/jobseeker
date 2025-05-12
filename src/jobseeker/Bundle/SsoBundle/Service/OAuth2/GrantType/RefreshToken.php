<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\RefreshTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OAuth2Exception;

class RefreshToken implements GrantTypeInterface
{

    private $refreshToken;
    protected $storage;
    protected $config;

    public function __construct(RefreshTokenInterface $storage, $config = array())
    {
        $this->config = array_merge(array('always_issue_new_refresh_token' => false), $config);
        $this->storage = $storage;
    }

    public function getQuerystringIdentifier()
    {
        return 'refresh_token';
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        $issueNewRefreshToken = $this->config['always_issue_new_refresh_token'];
        $token = $accessToken->createAccessToken($client_id, $user_id, $scope, $issueNewRefreshToken);

        if ($issueNewRefreshToken) {
            $this->storage->unsetRefreshToken($this->refreshToken['refresh_token']);
        }

        return $token;
    }

    public function getClientId()
    {
        return $this->refreshToken['client_id'];
    }

    public function getUserId()
    {
        return $this->refreshToken['uid'];
    }

    public function getScope()
    {
        return $this->refreshToken['scope'];
    }

    public function validateRequest($request)
    {
        if (!$request->request->get("refresh_token")) {
            return JsonResponse::create(OAuth2Exception::get("miss_refresh_token"));
        }

        if (!$refreshToken = $this->storage->getRefreshToken($request->request->get("refresh_token"))) {
            return JsonResponse::create(OAuth2Exception::get("invalid_refresh_token"));
        }

        if ($refreshToken['expires'] > 0 && $refreshToken["expires"] < time()) {
            return JsonResponse::create(OAuth2Exception::get("expires_refresh_token"));
        }

        $this->refreshToken = $refreshToken;

        return true;
    }

}
