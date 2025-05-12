<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface RefreshTokenInterface
{

    public function getRefreshToken($refresh_token);

    public function setRefreshToken($refresh_token, $client_id, $expires, $uid, $scope = null);

    public function unsetRefreshToken($refresh_token);

}
