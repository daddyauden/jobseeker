<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Controller;

interface AuthorizeControllerInterface
{

    const RESPONSE_TYPE_AUTHORIZATION_CODE = 'code';
    const RESPONSE_TYPE_ACCESS_TOKEN = 'token';

    public function handleAuthorizeRequest($request, $is_authorized, $uid);

    public function validateAuthorizeRequest($request);

}
