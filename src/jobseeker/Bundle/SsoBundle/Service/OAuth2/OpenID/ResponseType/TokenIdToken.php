<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\ResponseType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;

class TokenIdToken implements TokenIdTokenInterface
{

    protected $accessToken;
    protected $idToken;

    public function __construct(AccessTokenInterface $accessToken, IdToken $idToken)
    {
        $this->accessToken = $accessToken;
        $this->idToken = $idToken;
    }

    public function getAuthorizeResponse($params, $uid = null)
    {
        $result = $this->accessToken->getAuthorizeResponse($params, $uid);
        $access_token = $result[1]['fragment']['access_token'];
        $id_token = $this->idToken->createIdToken($params['client_id'], $uid, $params['nonce'], null, $access_token);
        $result[1]['fragment']['id_token'] = $id_token;

        return $result;
    }

}
