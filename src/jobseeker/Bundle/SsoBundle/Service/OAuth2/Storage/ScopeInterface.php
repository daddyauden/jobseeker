<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

interface ScopeInterface
{

    public function getDefaultScope();

    public function scopeExists($scope);

}
