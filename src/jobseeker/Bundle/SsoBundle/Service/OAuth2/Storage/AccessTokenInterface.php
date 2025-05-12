<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface AccessTokenInterface
{

    public function getAccessToken($access_token);

    public function setAccessToken($access_token, $client_id, $expires, $uid, $scope = null);

}
