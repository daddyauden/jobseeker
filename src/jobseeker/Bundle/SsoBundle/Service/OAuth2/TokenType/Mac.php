<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\TokenType;

class Mac implements TokenTypeInterface
{

    public function getAccessTokenParameter($request)
    {
        throw new \LogicException("Not supported");
    }

    public function getTokenType()
    {
        return 'mac';
    }

}
