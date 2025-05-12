<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller;

interface TokenControllerInterface
{

    public function handleTokenRequest($request);

    public function grantAccessToken($request);

}
