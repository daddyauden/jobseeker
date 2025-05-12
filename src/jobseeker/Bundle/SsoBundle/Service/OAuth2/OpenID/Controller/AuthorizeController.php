<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Controller;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller\AuthorizeController as BaseAuthorizeController;

class AuthorizeController extends BaseAuthorizeController implements AuthorizeControllerInterface
{

    private $nonce;

    protected function setNotAuthorizedResponse(RequestInterface $request, ResponseInterface $response, $redirect_uri, $uid = null)
    {
        $prompt = $request->query('prompt', 'consent');
        if ($prompt == 'none') {
            if (is_null($uid)) {
                $error = 'login_required';
                $error_message = 'The user must log in';
            } else {
                $error = 'interaction_required';
                $error_message = 'The user must grant access to your application';
            }
        } else {
            $error = 'consent_required';
            $error_message = 'The user denied access to your application';
        }

        $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $this->state, $error, $error_message);
    }

    protected function buildAuthorizeParameters($request, $response, $uid)
    {
        if (!$params = parent::buildAuthorizeParameters($request, $response, $uid)) {
            return;
        }

        if ($this->needsIdToken($this->getScope()) && $this->getResponseType() == self::RESPONSE_TYPE_AUTHORIZATION_CODE) {
            $params['id_token'] = $this->responseTypes['id_token']->createIdToken($this->getClientId(), $uid, $this->nonce);
        }

        $params['nonce'] = $this->nonce;

        return $params;
    }

    public function validateAuthorizeRequest(RequestInterface $request, ResponseInterface $response)
    {
        if (!parent::validateAuthorizeRequest($request, $response)) {
            return false;
        }

        $nonce = $request->query('nonce');

        if (!$nonce && in_array($this->getResponseType(), array(self::RESPONSE_TYPE_ID_TOKEN, self::RESPONSE_TYPE_TOKEN_ID_TOKEN))) {
            $response->setError(400, 'invalid_nonce', 'This application requires you specify a nonce parameter');

            return false;
        }

        $this->nonce = $nonce;

        return true;
    }

    protected function getValidResponseTypes()
    {
        return array(
            self::RESPONSE_TYPE_ACCESS_TOKEN,
            self::RESPONSE_TYPE_AUTHORIZATION_CODE,
            self::RESPONSE_TYPE_ID_TOKEN,
            self::RESPONSE_TYPE_TOKEN_ID_TOKEN,
        );
    }

    public function needsIdToken($request_scope)
    {
        return $this->scopeUtil->checkScope('openid', $request_scope);
    }

    public function getNonce()
    {
        return $this->nonce;
    }

}
