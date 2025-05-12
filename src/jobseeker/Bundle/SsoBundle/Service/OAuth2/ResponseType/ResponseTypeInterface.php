<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ResponseType;

interface ResponseTypeInterface
{

    public function getAuthorizeResponse($params, $uid);

}
