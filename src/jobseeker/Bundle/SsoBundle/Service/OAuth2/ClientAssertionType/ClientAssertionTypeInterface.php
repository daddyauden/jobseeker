<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\ClientAssertionType;

interface ClientAssertionTypeInterface
{

    public function getClientId();

    public function validateRequest($request);

}
