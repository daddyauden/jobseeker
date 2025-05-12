<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface ClientInterface
{

    public function getClientDetail($client_id);

    public function setClientDetail($client_id, $client_secret, $redirect_uri = null, $grant_types = null, $scope = null);

    public function getClientScope($client_id);

    public function checkRestrictedGrantType($client_id, $grant_type);

}
