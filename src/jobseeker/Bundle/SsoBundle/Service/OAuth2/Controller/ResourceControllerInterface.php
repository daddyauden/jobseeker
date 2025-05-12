<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller;

interface ResourceControllerInterface
{

    public function getAccessTokenData($request);

    public function verifyResourceRequest($request, $scope = null);

}
