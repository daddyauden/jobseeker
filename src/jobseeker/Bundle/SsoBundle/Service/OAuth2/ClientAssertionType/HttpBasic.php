<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType;

use Symfony\Component\HttpFoundation\JsonResponse;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\OAuth2Exception;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ClientCredentialsInterface;

class HttpBasic implements ClientAssertionTypeInterface
{

    private $clientData;
    protected $storage;
    protected $config;

    public function __construct(ClientCredentialsInterface $storage, array $config = array())
    {
        $this->storage = $storage;
        $this->config = array_merge(array(
            'allow_credentials_in_request_body' => true,
            'allow_public_clients' => true,
                ), $config);
    }

    public function getClientId()
    {
        return $this->clientData['client_id'];
    }

    public function validateRequest($request)
    {
        $clientData = $this->getClientCredentials($request);

        if (!isset($clientData['client_id']) || !$clientData['client_id']) {
            return JsonResponse::create(OAuth2Exception::get("miss_client_id"));
        }

        if (!isset($clientData['client_secret']) || !$clientData['client_secret']) {
            if (!$this->config['allow_public_clients']) {
                return JsonResponse::create(OAuth2Exception::get("miss_client_secret"));
            }

            if (!$this->storage->isPublicClient($clientData['client_id'])) {
                return JsonResponse::create(OAuth2Exception::get("invalid_client_id"));
            }
        } elseif ($this->storage->checkClientCredentials($clientData['client_id'], $clientData['client_secret']) === false) {
            return JsonResponse::create(OAuth2Exception::get("invalid_client_secret"));
        }

        $this->clientData = $clientData;

        return true;
    }

    protected function getClientCredentials($request)
    {
        if ($request->headers->has('PHP_AUTH_USER') && $request->headers->has('PHP_AUTH_PW')) {
            return array('client_id' => $request->header->get('PHP_AUTH_USER'), 'client_secret' => $request->header->get('PHP_AUTH_PW'));
        }

        if ($this->config['allow_credentials_in_request_body']) {
            if ($request->request->has('client_id') && $request->request->has('client_secret')) {
                return array('client_id' => $request->request->get('client_id'), 'client_secret' => $request->request->get('client_secret'));
            }
        }

        return array();
    }

}
