<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ScopeInterface as ScopeStorageInterface;

interface ScopeInterface extends ScopeStorageInterface
{

    public function checkScope($required_scope, $available_scope);

    public function getScopeFromRequest($request);

}
