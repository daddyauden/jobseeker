<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType;

use Symfony\Component\HttpFoundation\JsonResponse;

class Bearer implements TokenTypeInterface
{

    private $config;

    public function __construct(array $config = array())
    {
        $this->config = array_merge(array(
            'token_param_name' => 'access_token',
            'token_bearer_header_name' => 'Bearer',
                ), $config);
    }

    public function getAccessTokenParameter($request)
    {
        $headers = $request->headers->get('AUTHORIZATION');

        $methodsUsed = !empty($headers) + (bool) ($request->query->has($this->config['token_param_name'])) + (bool) ($request->request->has($this->config['token_param_name']));
        if ($methodsUsed > 1) {
            return false;
        }

        if ($methodsUsed == 0) {
            return false;
        }

        if (!empty($headers)) {
            if (!preg_match('/' . $this->config['token_bearer_header_name'] . '\s(\S+)/', $headers, $matches)) {
                return false;
            }

            return $matches[1];
        }

        if ($request->request->get($this->config['token_param_name'])) {
            if (!in_array(strtolower($request->getMethod()), array('post', 'put'))) {
                return false;
            }

            $contentType = $request->server->get('CONTENT_TYPE');
            if (false !== $pos = strpos($contentType, ';')) {
                $contentType = substr($contentType, 0, $pos);
            }

            if ($contentType !== null && $contentType != 'application/x-www-form-urlencoded') {
                return false;
            }

            return $request->request->get($this->config['token_param_name']);
        }

        return $request->query($this->config['token_param_name']);
    }

    public function getTokenType()
    {
        return 'Bearer';
    }

    public function requestHasToken($request)
    {
        $headers = $request->headers->get('AUTHORIZATION');

        return !empty($headers) || (bool) ($request->request->get($this->config['token_param_name'])) || (bool) ($request->query->get($this->config['token_param_name']));
    }

}
