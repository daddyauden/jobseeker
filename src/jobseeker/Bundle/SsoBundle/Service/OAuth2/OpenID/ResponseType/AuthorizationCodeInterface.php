<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\ResponseType;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType\AuthorizationCodeInterface as BaseAuthorizationCodeInterface;

interface AuthorizationCodeInterface extends BaseAuthorizationCodeInterface
{

    public function createAuthorizationCode($client_id, $uid, $redirect_uri, $scope = null, $id_token = null);

}
