<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType\TokenTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\ResourceController;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ScopeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Scope;

class UserInfoController extends ResourceController implements UserInfoControllerInterface
{

    private $token;
    protected $tokenType;
    protected $tokenStorage;
    protected $userClaimsStorage;
    protected $config;
    protected $scopeUtil;

    public function __construct(TokenTypeInterface $tokenType, AccessTokenInterface $tokenStorage, UserClaimsInterface $userClaimsStorage, $config = array(), ScopeInterface $scopeUtil = null)
    {
        $this->tokenType = $tokenType;
        $this->tokenStorage = $tokenStorage;
        $this->userClaimsStorage = $userClaimsStorage;

        $this->config = array_merge(array(
            'www_realm' => 'Service',
                ), $config);

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function handleUserInfoRequest($request)
    {
        $response = $this->verifyResourceRequest($request, 'openid');

        if (true === $response) {
            $token = $this->getToken();
            $claims = $this->userClaimsStorage->getUserClaims($token['uid'], $token['scope']);
            return JsonResponse::create($claims);
        } elseif (false === $response) {
            return false;
        } else {
            return $response;
        }
    }

}
