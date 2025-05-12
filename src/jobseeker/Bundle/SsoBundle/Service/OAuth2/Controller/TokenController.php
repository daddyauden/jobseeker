<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType\ClientAssertionTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType\GrantTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ScopeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Scope;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OAuth2Exception;

class TokenController implements TokenControllerInterface
{

    protected $accessToken;
    protected $grantTypes;
    protected $clientAssertionType;
    protected $scopeUtil;
    protected $clientStorage;

    public function __construct(AccessTokenInterface $accessToken, ClientInterface $clientStorage, array $grantTypes = array(), ClientAssertionTypeInterface $clientAssertionType = null, ScopeInterface $scopeUtil = null)
    {
        if (is_null($clientAssertionType)) {
            foreach ($grantTypes as $grantType) {
                if (!$grantType instanceof ClientAssertionTypeInterface) {
                    throw new \InvalidArgumentException('You must supply an instance of jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType\ClientAssertionTypeInterface or only use grant types which implement jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType\ClientAssertionTypeInterface');
                }
            }
        }
        $this->clientAssertionType = $clientAssertionType;
        $this->accessToken = $accessToken;
        $this->clientStorage = $clientStorage;
        foreach ($grantTypes as $grantType) {
            $this->addGrantType($grantType);
        }

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function handleTokenRequest($request)
    {
        try {
            $response = $this->grantAccessToken($request);
            //$response->headers->set('Cache-Control', 'no-store');
            //$response->headers->set('Pragma', 'no-cache');
            return $response;
        } catch (\Exception $e) {
            return JsonResponse::create(OAuth2Exception::get("error_access_token", __CLASS__, __LINE__));
        }
    }

    public function grantAccessToken($request)
    {
        if (!$request->isMethod('post')) {
            return JsonResponse::create(OAuth2Exception::get("invalid_method"));
        }

        if (!$grantTypeIdentifier = $request->request->get('grant_type')) {
            return JsonResponse::create(OAuth2Exception::get("miss_grant_type"));
        }

        if (!isset($this->grantTypes[$grantTypeIdentifier])) {
            return JsonResponse::create(OAuth2Exception::get("invalid_grant_type"));
        }

        $grantType = $this->grantTypes[$grantTypeIdentifier];

        if (!$grantType instanceof ClientAssertionTypeInterface) {

            $response = $this->clientAssertionType->validateRequest($request);
            if (true !== $response) {
                return $response;
            } else {
                $clientId = $this->clientAssertionType->getClientId();
            }
        }

        $code = $grantType->validateRequest($request);

        if (true !== $code) {
            return $code;
        }

        if ($grantType instanceof ClientAssertionTypeInterface) {
            $clientId = $grantType->getClientId();
        } else {
            $storedClientId = $grantType->getClientId();
            if ($storedClientId && $storedClientId != $clientId) {
                return JsonResponse::create(OAuth2Exception::get("invalid_grant_type"));
            }
        }

        if (!$this->clientStorage->checkRestrictedGrantType($clientId, $grantTypeIdentifier)) {
            return JsonResponse::create(OAuth2Exception::get("invalid_grant_type"));
        }

        $requestedScope = $this->scopeUtil->getScopeFromRequest($request);

        $availableScope = $grantType->getScope();

        if ($requestedScope) {
            if ($availableScope) {
                if (!$this->scopeUtil->checkScope($requestedScope, $availableScope)) {
                    return JsonResponse::create(OAuth2Exception::get("invalid_scope"));
                }
            } else {
                if ($clientScope = $this->clientStorage->getClientScope($clientId)) {
                    if (!$this->scopeUtil->checkScope($requestedScope, $clientScope)) {
                        return JsonResponse::create(OAuth2Exception::get("invalid_scope"));
                    }
                } elseif (!$this->scopeUtil->scopeExists($requestedScope)) {
                    return JsonResponse::create(OAuth2Exception::get("invalid_scope"));
                }
            }
        } elseif ($availableScope) {
            $requestedScope = $availableScope;
        } else {
            $defaultScope = $this->scopeUtil->getDefaultScope();
            if (false === $defaultScope) {
                return JsonResponse::create(OAuth2Exception::get("miss_scope"));
            }
            $requestedScope = $defaultScope;
        }

        $response = $grantType->createAccessToken($this->accessToken, $clientId, $grantType->getUserId(), $requestedScope);
        if ($response === false) {
            return JsonResponse::create(OAuth2Exception::get("error_access_token"));
        }
        return $response;
    }

    public function addGrantType(GrantTypeInterface $grantType, $identifier = null)
    {
        if (is_null($identifier) || is_numeric($identifier)) {
            $identifier = $grantType->getQuerystringIdentifier();
        }
        $this->grantTypes[$identifier] = $grantType;
    }

}
