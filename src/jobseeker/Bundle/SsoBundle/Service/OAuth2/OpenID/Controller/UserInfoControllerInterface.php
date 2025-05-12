<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Controller;

interface UserInfoControllerInterface
{

    public function handleUserInfoRequest($request);

}
