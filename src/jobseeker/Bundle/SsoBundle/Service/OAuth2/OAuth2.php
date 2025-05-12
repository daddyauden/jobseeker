<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType\ClientAssertionTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType\HttpBasic;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\AuthorizeControllerInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\AuthorizeController;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\ResourceControllerInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\ResourceController;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\TokenControllerInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\TokenController;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType\AuthorizationCode;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType\ClientCredentials;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType\GrantTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType\RefreshToken;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType\UserCredentials;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Controller\AuthorizeController as OpenIDAuthorizeController;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Controller\UserInfoControllerInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Controller\UserInfoController;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\GrantType\AuthorizationCode as OpenIDAuthorizationCodeGrantType;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\ResponseType\AuthorizationCode as OpenIDAuthorizationCodeResponseType;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\ResponseType\IdToken;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\ResponseType\TokenIdToken;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessToken;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AuthorizationCode as AuthorizationCodeResponseType;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\CryptoToken;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\ResponseTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType\TokenTypeInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType\Bearer;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\CryptoTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\CryptoToken as CryptoTokenStorage;

class OAuth2 implements AuthorizeControllerInterface, TokenControllerInterface, ResourceControllerInterface, UserInfoControllerInterface
{

    protected $config;
    protected $storages;
    protected $authorizeController;
    protected $resourceController;
    protected $tokenController;
    protected $userInfoController;
    protected $grantTypes;
    protected $responseTypes;
    protected $tokenType;
    protected $scopeUtil;
    protected $clientAssertionType;
    protected $storageMap = array(
        'authorization_code' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AuthorizationCodeInterface',
        'access_token' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface',
        'client_credentials' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientCredentialsInterface',
        'client' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientInterface',
        'refresh_token' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\RefreshTokenInterface',
        'scope' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ScopeInterface',
        'user_credentials' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\UserCredentialsInterface',
        'user_claims' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface',
    );
    protected $responseTypeMap = array(
        'token' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface',
        'code' => 'jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AuthorizationCodeInterface',
    );

    public function __construct($storage = array(), array $config = array(), array $grantTypes = array(), array $responseTypes = array(), TokenTypeInterface $tokenType = null, ScopeInterface $scopeUtil = null, ClientAssertionTypeInterface $clientAssertionType = null)
    {
        $storage = is_array($storage) ? $storage : array($storage);
        $this->storages = array();
        foreach ($storage as $key => $service) {
            $this->addStorage($service, $key);
        }

        $this->config = array_merge(array(
            'use_crypto_tokens' => false,
            'store_encrypted_token_string' => true,
            'use_openid_connect' => false,
            'id_lifetime' => 3600,
            'access_lifetime' => 3600,
            'www_realm' => 'Service',
            'token_param_name' => 'access_token',
            'token_bearer_header_name' => 'Bearer',
            'enforce_state' => true,
            'require_exact_redirect_uri' => true,
            'allow_implicit' => false,
            'allow_credentials_in_request_body' => true,
            'allow_public_clients' => true,
            'always_issue_new_refresh_token' => false,
                ), $config);

        foreach ($grantTypes as $key => $grantType) {
            $this->addGrantType($grantType, $key);
        }
        foreach ($responseTypes as $key => $responseType) {
            $this->addResponseType($responseType, $key);
        }
        $this->tokenType = $tokenType;
        $this->scopeUtil = $scopeUtil;
        $this->clientAssertionType = $clientAssertionType;
    }

    public function addStorage($storage, $key = null)
    {

        if (isset($this->storageMap[$key])) {
            if (!is_null($storage) && !$storage instanceof $this->storageMap[$key]) {
                throw new \InvalidArgumentException(sprintf('storage of type "%s" must implement interface "%s"', $key, $this->storageMap[$key]));
            }
            $this->storages[$key] = $storage;

            if ($key === 'client' && !isset($this->storages['client_credentials'])) {
                if ($storage instanceof jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientCredentialsInterface) {
                    $this->storages['client_credentials'] = $storage;
                }
            } elseif ($key === 'client_credentials' && !isset($this->storages['client'])) {
                if ($storage instanceof jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientInterface) {
                    $this->storages['client'] = $storage;
                }
            }
        } elseif (!is_null($key) && !is_numeric($key)) {
            throw new \InvalidArgumentException(sprintf('unknown storage key "%s", must be one of [%s]', $key, implode(', ', array_keys($this->storageMap))));
        } else {
            $set = false;
            foreach ($this->storageMap as $type => $interface) {
                if ($storage instanceof $interface) {
                    $this->storages[$type] = $storage;
                    $set = true;
                }
            }

            if (!$set) {
                throw new \InvalidArgumentException(sprintf('storage of class "%s" must implement one of [%s]', get_class($storage), implode(', ', $this->storageMap)));
            }
        }
    }

    public function addGrantType(GrantTypeInterface $grantType, $key = null)
    {
        if (is_string($key)) {
            $this->grantTypes[$key] = $grantType;
        } else {
            $this->grantTypes[$grantType->getQuerystringIdentifier()] = $grantType;
        }

        if (!is_null($this->tokenController)) {
            $this->getTokenController()->addGrantType($grantType);
        }
    }

    protected function getDefaultGrantTypes()
    {
        $grantTypes = array();

        if (isset($this->storages['user_credentials'])) {
            $grantTypes['password'] = new UserCredentials($this->storages['user_credentials']);
        }

        if (isset($this->storages['client_credentials'])) {
            $config = array_intersect_key($this->config, array('allow_credentials_in_request_body' => ''));
            $grantTypes['client_credentials'] = new ClientCredentials($this->storages['client_credentials'], $config);
        }

        if (isset($this->storages['refresh_token'])) {
            $config = array_intersect_key($this->config, array('always_issue_new_refresh_token' => ''));
            $grantTypes['refresh_token'] = new RefreshToken($this->storages['refresh_token'], $config);
        }

        if (isset($this->storages['authorization_code'])) {
            if ($this->config['use_openid_connect']) {
                if (!$this->storages['authorization_code'] instanceof OpenIDAuthorizationCodeInterface) {
                    throw new \LogicException("Your authorization_code storage must implement jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\AuthorizationCodeInterface to work when 'use_openid_connect' is true");
                }
                $grantTypes ['authorization_code'] = new OpenIDAuthorizationCodeGrantType($this->storages['authorization_code']);
            } else {
                $grantTypes['authorization_code'] = new AuthorizationCode($this->storages['authorization_code']);
            }
        }

        if (count($grantTypes) == 0) {
            throw new \LogicException("Unable to build default grant types - You must supply an array of grant_types in the constructor");
        }

        return $grantTypes;
    }

    public function addResponseType(ResponseTypeInterface $responseType, $key = null)
    {
        if (isset($this->responseTypeMap[$key])) {
            if (!$responseType instanceof $this->responseTypeMap[$key]) {
                throw new \InvalidArgumentException(sprintf('responseType of type "%s" must implement interface "%s"', $key, $this->responseTypeMap[$key]));
            }
            $this->responseTypes[$key] = $responseType;
        } elseif (!is_null($key) && !is_numeric($key)) {
            throw new \InvalidArgumentException(sprintf('unknown responseType key "%s", must be one of [%s]', $key, implode(', ', array_keys($this->responseTypeMap))));
        } else {
            $set = false;
            foreach ($this->responseTypeMap as $type => $interface) {
                if ($responseType instanceof $interface) {
                    $this->responseTypes[$type] = $responseType;
                    $set = true;
                }
            }

            if (!$set) {
                throw new \InvalidArgumentException(sprintf('Unknown response type %s.  Please implement one of [%s]', get_class($responseType), implode(', ', $this->responseTypeMap)));
            }
        }
    }

    protected function getDefaultResponseTypes()
    {
        $responseTypes = array();

        if ($this->config['allow_implicit']) {
            $responseTypes['token'] = $this->getAccessTokenResponseType();
        }

        if ($this->config['use_openid_connect']) {
            $responseTypes['id_token'] = $this->getIdTokenResponseType();
            if ($this->config['allow_implicit']) {
                $responseTypes['token id_token'] = $this->getTokenIdTokenResponseType();
            }
        }

        if (isset($this->storages['authorization_code'])) {
            $config = array_intersect_key($this->config, array_flip(explode(' ', 'enforce_redirect auth_code_lifetime')));
            if ($this->config['use_openid_connect']) {
                if (!$this->storages['authorization_code'] instanceof OpenIDAuthorizationCodeInterface) {
                    throw new \LogicException("Your authorization_code storage must implement jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\AuthorizationCodeInterface to work when 'use_openid_connect' is true");
                }
                $responseTypes['code'] = new OpenIDAuthorizationCodeResponseType($this->storages['authorization_code'], $config);
            } else {
                $responseTypes['code'] = new AuthorizationCodeResponseType($this->storages['authorization_code'], $config);
            }
        }

        if (count($responseTypes) == 0) {
            throw new \LogicException("You must supply an array of response_types in the constructor or implement a jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AuthorizationCodeInterface storage object or set 'allow_implicit' to true and implement a jobseeker\Bundle\SsoBundle\Storage\AccessTokenInterface storage object");
        }

        return $responseTypes;
    }

    public function getScopeUtil()
    {
        if (!$this->scopeUtil) {
            $storage = isset($this->storages['scope']) ? $this->storages['scope'] : null;
            $this->scopeUtil = new Scope($storage);
        }

        return $this->scopeUtil;
    }

    public function setAuthorizeController(AuthorizeControllerInterface $authorizeController)
    {
        $this->authorizeController = $authorizeController;
    }

    public function setResourceController(ResourceControllerInterface $resourceController)
    {
        $this->resourceController = $resourceController;
    }

    public function setTokenController(TokenControllerInterface $tokenController)
    {
        $this->tokenController = $tokenController;
    }

    public function setUserInfoController(UserInfoControllerInterface $userInfoController)
    {
        $this->userInfoController = $userInfoController;
    }

    public function handleAuthorizeRequest($request, $is_authorized, $uid)
    {

        return $this->getAuthorizeController()->handleAuthorizeRequest($request, $is_authorized, $uid);
    }

    public function validateAuthorizeRequest($request)
    {
        return $this->getAuthorizeController()->validateAuthorizeRequest($request);
    }

    public function getAuthorizeController()
    {
        if (is_null($this->authorizeController)) {
            $this->authorizeController = $this->createDefaultAuthorizeController();
        }

        return $this->authorizeController;
    }

    protected function createDefaultAuthorizeController()
    {
        if (!isset($this->storages['client'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientInterface to use the authorize server");
        }
        if (0 == count($this->responseTypes)) {
            $this->responseTypes = $this->getDefaultResponseTypes();
        }
        if ($this->config['use_openid_connect'] && !isset($this->responseTypes['id_token'])) {
            $this->responseTypes['id_token'] = $this->createDefaultIdTokenResponseType();
            if ($this->config['allow_implicit']) {
                $this->responseTypes['token id_token'] = $this->createDefaultTokenIdTokenResponseType();
            }
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'allow_implicit enforce_state require_exact_redirect_uri')));

        if ($this->config['use_openid_connect']) {
            return new OpenIDAuthorizeController($this->storages['client'], $this->responseTypes, $config, $this->getScopeUtil());
        }

        return new AuthorizeController($this->storages['client'], $this->responseTypes, $config, $this->getScopeUtil());
    }

    protected function getAccessTokenResponseType()
    {
        if (isset($this->responseTypes['token'])) {
            return $this->responseTypes['token'];
        }

        if ($this->config['use_crypto_tokens']) {
            return $this->createDefaultCryptoTokenResponseType();
        }

        return $this->createDefaultAccessTokenResponseType();
    }

    protected function createDefaultAccessTokenResponseType()
    {
        if (!isset($this->storages['access_token'])) {
            throw new \LogicException("You must supply a response type implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface, or a storage object implementing jobseeker\Bundle\SsoBundle\Storage\AccessTokenInterface to use the token server");
        }

        $refreshStorage = null;
        if (isset($this->storages['refresh_token'])) {
            $refreshStorage = $this->storages['refresh_token'];
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'access_lifetime refresh_token_lifetime')));
        $config['token_type'] = $this->tokenType ? $this->tokenType->getTokenType() : $this->getDefaultTokenType()->getTokenType();

        return new AccessToken($this->storages['access_token'], $refreshStorage, $config);
    }

    public function getAccessTokenData($request)
    {
        return $this->getResourceController()->getAccessTokenData($request);
    }

    public function verifyResourceRequest($request, $scope = null)
    {
        $value = $this->getResourceController()->verifyResourceRequest($request, $scope);

        return $value;
    }

    public function getResourceController()
    {
        if (is_null($this->resourceController)) {
            $this->resourceController = $this->createDefaultResourceController();
        }

        return $this->resourceController;
    }

    protected function createDefaultResourceController()
    {
        if ($this->config['use_crypto_tokens']) {
            if (!isset($this->storages['access_token']) || !$this->storages['access_token'] instanceof CryptoTokenInterface) {
                $this->storages['access_token'] = $this->createDefaultCryptoTokenStorage();
            }
        } elseif (!isset($this->storages['access_token'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface or use CryptoTokens to use the resource server");
        }

        if (!$this->tokenType) {
            $this->tokenType = $this->getDefaultTokenType();
        }

        $config = array_intersect_key($this->config, array('www_realm' => ''));

        return new ResourceController($this->tokenType, $this->storages['access_token'], $config, $this->getScopeUtil());
    }

    protected function getDefaultTokenType()
    {
        $config = array_intersect_key($this->config, array_flip(explode(' ', 'token_param_name token_bearer_header_name')));

        return new Bearer($config);
    }

    public function handleTokenRequest($request)
    {
        return $this->getTokenController()->handleTokenRequest($request);
    }

    public function grantAccessToken($request)
    {
        return $this->getTokenController()->grantAccessToken($request);
    }

    public function getTokenController()
    {
        if (is_null($this->tokenController)) {
            $this->tokenController = $this->createDefaultTokenController();
        }

        return $this->tokenController;
    }

    protected function createDefaultTokenController()
    {
        if (0 == count($this->grantTypes)) {
            $this->grantTypes = $this->getDefaultGrantTypes();
        }

        if (is_null($this->clientAssertionType)) {
            foreach ($this->grantTypes as $grantType) {
                if (!$grantType instanceof ClientAssertionTypeInterface) {
                    if (!isset($this->storages['client_credentials'])) {
                        throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientCredentialsInterface to use the token server");
                    }
                    $config = array_intersect_key($this->config, array_flip(explode(' ', 'allow_credentials_in_request_body allow_public_clients')));
                    $this->clientAssertionType = new HttpBasic($this->storages['client_credentials'], $config);
                    break;
                }
            }
        }

        if (!isset($this->storages['client'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientInterface to use the token server");
        }

        $accessTokenResponseType = $this->getAccessTokenResponseType();

        return new TokenController($accessTokenResponseType, $this->storages['client'], $this->grantTypes, $this->clientAssertionType, $this->getScopeUtil());
    }

    public function handleUserInfoRequest($request)
    {
        return $this->getUserInfoController()->handleUserInfoRequest($request);
    }

    public function getUserInfoController()
    {
        if (is_null($this->userInfoController)) {
            $this->userInfoController = $this->createDefaultUserInfoController();
        }

        return $this->userInfoController;
    }

    protected function createDefaultUserInfoController()
    {
        if ($this->config['use_crypto_tokens']) {
            if (!isset($this->storages['access_token']) || !$this->storages['access_token'] instanceof CryptoTokenInterface) {
                $this->storages['access_token'] = $this->createDefaultCryptoTokenStorage();
            }
        } elseif (!isset($this->storages['access_token'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AccessTokenInterface or use CryptoTokens to use the UserInfo server");
        }

        if (!isset($this->storages['user_claims'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface to use the UserInfo server");
        }

        if (!$this->tokenType) {
            $this->tokenType = $this->getDefaultTokenType();
        }

        $config = array_intersect_key($this->config, array('www_realm' => ''));

        return new UserInfoController($this->tokenType, $this->storages['access_token'], $this->storages['user_claims'], $config, $this->getScopeUtil());
    }

    public function setScopeUtil($scopeUtil)
    {
        $this->scopeUtil = $scopeUtil;
    }

    protected function getIdTokenResponseType()
    {
        if (isset($this->responseTypes['id_token'])) {
            return $this->responseTypes['id_token'];
        }

        return $this->createDefaultIdTokenResponseType();
    }

    protected function createDefaultIdTokenResponseType()
    {
        if (!isset($this->storages['user_claims'])) {
            throw new \ LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage\UserClaimsInterface to use openid connect");
        }
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\PublicKeyInterface to use openid connect");
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'issuer id_lifetime')));

        return new IdToken($this->storages['user_claims'], $this->storages['public_key'], $config);
    }

    protected function getTokenIdTokenResponseType()
    {
        if (isset($this->responseTypes['token id_token'])) {
            return $this->responseTypes['token id_token'];
        }

        return $this->createDefaultTokenIdTokenResponseType();
    }

    protected function createDefaultTokenIdTokenResponseType()
    {
        return new TokenIdToken($this->getAccessTokenResponseType(), $this->getIdTokenResponseType());
    }

    protected function createDefaultCryptoTokenStorage()
    {
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\PublicKeyInterface to use crypto tokens");
        }
        $tokenStorage = null;
        if (!empty($this->config['store_encrypted_token_string']) && isset($this->storages['access_token'])) {
            $tokenStorage = $this->storages['access_token'];
        }
        return new CryptoTokenStorage($this->storages['public_key'], $tokenStorage);
    }

    protected function createDefaultCryptoTokenResponseType()
    {
        if (!isset($this->storages['public_key'])) {
            throw new \LogicException("You must supply a storage object implementing jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\PublicKeyInterface to use crypto tokens");
        }

        $tokenStorage = null;
        if (isset($this->storages['access_token'])) {
            $tokenStorage = $this->storages['access_token'];
        }

        $refreshStorage = null;
        if (isset($this->storages['refresh_token'])) {
            $refreshStorage = $this->storages['refresh_token'];
        }

        $config = array_intersect_key($this->config, array_flip(explode(' ', 'store_encrypted_token_string')));

        return new CryptoToken($this->storages['public_key'], $tokenStorage, $refreshStorage, $config);
    }

    public function getStorages()
    {
        return $this->storages;
    }

    public function getStorage($name)
    {
        return isset($this->storages[$name]) ? $this->storages[$name] : null;
    }

    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    public function getGrantType($name)
    {
        return isset($this->grantTypes[$name]) ? $this->grantTypes[$name] : null;
    }

    public function getResponseTypes()
    {
        return $this->responseTypes;
    }

    public function getResponseType($name)
    {
        return isset($this->responseTypes[$name]) ? $this->responseTypes[$name] : null;
    }

    public function getTokenType()
    {
        return $this->tokenType;
    }

    public function getClientAssertionType()
    {
        return $this->clientAssertionType;
    }

    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function getConfig($name, $default = null)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }

}
