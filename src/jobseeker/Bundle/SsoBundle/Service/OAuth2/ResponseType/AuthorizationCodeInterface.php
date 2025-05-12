<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType;

interface AuthorizationCodeInterface extends ResponseTypeInterface
{

    public function createAuthorizationCode($client_id, $redirect_uri, $uid, $scope = null);

    public function enforceRedirect();

}
