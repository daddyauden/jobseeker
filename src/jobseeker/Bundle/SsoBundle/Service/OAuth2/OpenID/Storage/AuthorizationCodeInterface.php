<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\AuthorizationCodeInterface as BaseAuthorizationCodeInterface;

interface AuthorizationCodeInterface extends BaseAuthorizationCodeInterface
{

    public function setAuthorizationCode($code, $client_id, $uid, $redirect_uri, $expires, $scope = null, $id_token = null);

}
