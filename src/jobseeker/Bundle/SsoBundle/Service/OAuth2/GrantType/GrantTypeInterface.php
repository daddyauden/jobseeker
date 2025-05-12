<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\GrantType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AccessTokenInterface;

interface GrantTypeInterface
{

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $uid, $scope);

    public function getClientId();

    public function getQuerystringIdentifier();

    public function getScope();

    public function getUserId();

    public function validateRequest($request);

}
