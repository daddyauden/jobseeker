<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType\TokenTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ScopeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Scope;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OAuth2Exception;

class ResourceController implements ResourceControllerInterface
{

    private $token;
    protected $tokenType;
    protected $tokenStorage;
    protected $config;
    protected $scopeUtil;

    public function __construct(TokenTypeInterface $tokenType, AccessTokenInterface $tokenStorage, $config = array(), ScopeInterface $scopeUtil = null)
    {
        $this->tokenType = $tokenType;
        $this->tokenStorage = $tokenStorage;

        $this->config = array_merge(array(
            'www_realm' => 'Service',
                ), $config);

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function getAccessTokenData($request)
    {
        if ($token_param = $this->tokenType->getAccessTokenParameter($request)) {
            if (!$token = $this->tokenStorage->getAccessToken($token_param)) {
                return JsonResponse::create(OAuth2Exception::get("invalid_token"));
            } elseif (!isset($token["expires"]) || !isset($token["client_id"])) {
                return JsonResponse::create(OAuth2Exception::get("invalid_token"));
            } elseif (time() > $token["expires"]) {
                return JsonResponse::create(OAuth2Exception::get("expires_token"));
            } else {
                return $token;
            }
        }

        return false;
    }

    public function verifyResourceRequest($request, $scope = null)
    {
        $token = $this->getAccessTokenData($request);

        if (false === $token) {
            return false;
        }

        if ($token instanceof JsonResponse) {
            return $token;
        }

        if ($scope && (!isset($token["scope"]) || !$token["scope"] || !$this->scopeUtil->checkScope($scope, $token["scope"]))) {
            return JsonResponse::create(OAuth2Exception::get("invalid_privilege"));
        }

        $this->token = $token;

        return true;
    }

    public function getToken()
    {
        return $this->token;
    }

}
