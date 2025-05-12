<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType\HttpBasic;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientCredentialsInterface;

class ClientCredentials extends HttpBasic implements GrantTypeInterface
{

    private $clientData;

    public function __construct(ClientCredentialsInterface $storage, array $config = array())
    {

        $config['allow_public_clients'] = false;

        parent::__construct($storage, $config);
    }

    public function getQuerystringIdentifier()
    {
        return 'client_credentials';
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $uid, $scope)
    {

        $includeRefreshToken = false;

        return $accessToken->createAccessToken($client_id, $uid, $scope, $includeRefreshToken);
    }

    public function getUserId()
    {
        $this->loadClientData();

        return $this->clientData['uid'];
    }

    public function getScope()
    {
        $this->loadClientData();

        return $this->clientData['scope'];
    }

    private function loadClientData()
    {
        if (!$this->clientData) {
            $this->clientData = $this->storage->getClientDetail($this->getClientId());
        }
    }

}
