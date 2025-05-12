<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface ClientCredentialsInterface extends ClientInterface
{

    public function checkClientCredentials($client_id, $client_secret);

    public function isPublicClient($client_id);

}
