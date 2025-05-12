<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface AuthorizationCodeInterface
{

    const RESPONSE_TYPE_CODE = "code";

    public function getAuthorizationCode($authorization_code);

    public function setAuthorizationCode($authorization_code, $client_id, $redirect_uri, $expires, $uid, $scope = null);

    public function expireAuthorizationCode($authorization_code);

}
