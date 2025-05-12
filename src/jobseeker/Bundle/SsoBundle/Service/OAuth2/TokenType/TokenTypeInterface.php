<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType;

interface TokenTypeInterface
{

    public function getAccessTokenParameter($request);

    public function getTokenType();

}
