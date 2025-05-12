<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OAuth2Exception;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ScopeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Scope;

class AuthorizeController implements AuthorizeControllerInterface
{

    private $scope;
    private $state;
    private $client_id;
    private $redirect_uri;
    private $response_type;
    protected $clientStorage;
    protected $responseTypes;
    protected $config;
    protected $scopeUtil;

    public function __construct(ClientInterface $clientStorage, array $responseTypes = array(), array $config = array(), ScopeInterface $scopeUtil = null)
    {
        $this->clientStorage = $clientStorage;
        $this->responseTypes = $responseTypes;
        $this->config = array_merge(array(
            'allow_implicit' => false,
            'enforce_state' => true,
            'require_exact_redirect_uri' => true,
            'redirect_status_code' => 302,
                ), $config);

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function handleAuthorizeRequest($request, $is_authorized, $uid)
    {
        if ($is_authorized === false || !is_bool($is_authorized)) {
            return JsonResponse::create(OAuth2Exception::get("access_deny"));
        }

        try {
            $response = $this->validateAuthorizeRequest($request);
            if (true !== $response) {
                return $response;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        if (empty($this->redirect_uri)) {
            $clientData = $this->clientStorage->getClientDetail($this->client_id);
            $registered_redirect_uri = $clientData['redirect_uri'];
        }

        $params = $this->buildAuthorizeParameters();

        $authResult = $this->responseTypes[$this->response_type]->getAuthorizeResponse($params, $uid);

        list($redirect_uri, $uri_params) = $authResult;

        if (empty($redirect_uri) && !empty($registered_redirect_uri)) {
            $redirect_uri = $registered_redirect_uri;
        }

        $uri = $this->buildUri($redirect_uri, $uri_params);

        return new RedirectResponse($uri);
    }

    public function validateAuthorizeRequest($request)
    {

        $client_id = $request->query->get("client_id", $request->request->get("client_id"));
        $supplied_redirect_uri = $request->query->get('redirect_uri', $request->request->get("redirect_uri"));
        $response_type = $request->query->get('response_type', $request->request->get("response_type"));
        $state = $request->query->get('state', $request->request->get("state"));

        $requestedScope = $this->scopeUtil->getScopeFromRequest($request);

        if (!$client_id) {
            return JsonResponse::create(OAuth2Exception::get("miss_client_id"));
        }

        if (!$clientData = $this->clientStorage->getClientDetail($client_id)) {
            return JsonResponse::create(OAuth2Exception::get("invalid_client_id"));
        }

        $registered_redirect_uri = isset($clientData['redirect_uri']) ? $clientData['redirect_uri'] : null;

        if ($supplied_redirect_uri) {
            $parts = parse_url($supplied_redirect_uri);
            if (isset($parts['fragment']) && $parts['fragment']) {
                return JsonResponse::create(OAuth2Exception::get("invalid_uri"));
            }

            if ($registered_redirect_uri && !$this->validateRedirectUri($supplied_redirect_uri, $registered_redirect_uri)) {
                return JsonResponse::create(OAuth2Exception::get("mismatch_redirect_uri"));
            }

            $redirect_uri = $supplied_redirect_uri;
        } else {
            if (!$registered_redirect_uri) {
                return JsonResponse::create(OAuth2Exception::get("invalid_redirect_uri"));
            }

            if (count(explode(' ', $registered_redirect_uri)) > 1) {
                return JsonResponse::create(OAuth2Exception::get("miss_redirect_uri"));
            }

            $redirect_uri = $registered_redirect_uri;
        }

        if (!$response_type || !in_array($response_type, $this->getValidResponseTypes())) {
            return JsonResponse::create(OAuth2Exception::get("invalid_response_type"));
        }
        if ($response_type == self::RESPONSE_TYPE_AUTHORIZATION_CODE) {
            if (!isset($this->responseTypes['code'])) {
                throw new \Exception('authorization code grant type not supported');
            }

            if (!$this->clientStorage->checkRestrictedGrantType($client_id, 'authorization_code')) {
                throw new \Exception('The grant type is unauthorized for this client_id');
            }

            if ($this->responseTypes['code']->enforceRedirect() && !$redirect_uri) {
                return JsonResponse::create(OAuth2Exception::get("miss_redirect_uri"));
            }
        } else {
            if (!$this->config['allow_implicit']) {
                return JsonResponse::create(OAuth2Exception::get("invalid_grant_type"));
            }

            if (!$this->clientStorage->checkRestrictedGrantType($client_id, 'implicit')) {
                return JsonResponse::create(OAuth2Exception::get("invalid_grant_type"));
            }
        }

        if (!$requestedScope) {
            $clientScope = $this->clientStorage->getClientScope($client_id);

            if ((!$clientScope && !$this->scopeUtil->scopeExists($requestedScope)) || ($clientScope && !$this->scopeUtil->checkScope($requestedScope, $clientScope))) {
                return JsonResponse::create(OAuth2Exception::get("invalid_scope"));
            }
        } else {
            $defaultScope = $this->scopeUtil->getDefaultScope();

            if (false === $defaultScope) {
                return JsonResponse::create(OAuth2Exception::get("miss_scope"));
            }

            $requestedScope = $defaultScope;
        }

        if ($this->config['enforce_state'] && !$state) {
            return JsonResponse::create(OAuth2Exception::get("miss_state"));
        }

        $this->scope = $requestedScope;
        $this->state = $state;
        $this->client_id = $client_id;
        $this->redirect_uri = $supplied_redirect_uri;
        $this->response_type = $response_type;

        return true;
    }

    protected function buildAuthorizeParameters()
    {
        $params = array(
            'scope' => $this->scope,
            'state' => $this->state,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => $this->response_type,
        );

        return $params;
    }

    private function buildUri($uri, $params)
    {
        $parse_url = parse_url($uri);

        foreach ($params as $k => $v) {
            if (isset($parse_url[$k])) {
                $parse_url[$k] .= "&" . http_build_query($v);
            } else {
                $parse_url[$k] = http_build_query($v);
            }
        }

        return
                ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
                . ((isset($parse_url["user"])) ? $parse_url["user"]
                        . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
                . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
                . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
                . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
                . ((isset($parse_url["query"]) && !empty($parse_url['query'])) ? "?" . $parse_url["query"] : "")
                . ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "")
        ;
    }

    protected function getValidResponseTypes()
    {
        return array(
            self::RESPONSE_TYPE_ACCESS_TOKEN,
            self::RESPONSE_TYPE_AUTHORIZATION_CODE,
        );
    }

    private function validateRedirectUri($inputUri, $registeredUriString)
    {
        if (!$inputUri || !$registeredUriString) {
            return false;
        }
        $registered_uris = explode(' ', $registeredUriString);
        foreach ($registered_uris as $registered_uri) {
            if ($this->config['require_exact_redirect_uri']) {
                if (strcmp($inputUri, $registered_uri) === 0) {
                    return true;
                }
            } else {
                if (strcasecmp(substr($inputUri, 0, strlen($registered_uri)), $registered_uri) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function getResponseType()
    {
        return $this->response_type;
    }

}
