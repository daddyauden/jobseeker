<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface JwtBearerInterface
{

    public function getClientKey($client_id, $subject);

    public function getJti($client_id, $subject, $audience, $expiration, $jti);

    public function setJti($client_id, $subject, $audience, $expiration, $jti);

}
