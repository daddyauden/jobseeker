<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType;

interface AccessTokenInterface extends ResponseTypeInterface
{

    public function createAccessToken($client_id, $uid, $scope = null, $includeRefreshToken = true);

}
