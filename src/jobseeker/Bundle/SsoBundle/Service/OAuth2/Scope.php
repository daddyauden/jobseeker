<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2;

use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\Memory;
use jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ScopeInterface as ScopeStorageInterface;

class Scope implements ScopeInterface
{

    protected $storage;

    public function __construct($storage = null)
    {
        if (is_null($storage) || is_array($storage)) {
            $storage = new Memory((array) $storage);
        }

        if (!$storage instanceof ScopeStorageInterface) {
            throw new \InvalidArgumentException("Argument 1 must be null, an array, or instance of jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage\ScopeInterface");
        }

        $this->storage = $storage;
    }

    public function getDefaultScope()
    {
        return $this->storage->getDefaultScope();
    }

    public function scopeExists($scope)
    {
        $scope = explode(' ', trim($scope));
        $reservedScope = $this->getReservedScopes();
        $nonReservedScopes = array_diff($scope, $reservedScope);
        if (count($nonReservedScopes) == 0) {
            return true;
        } else {
            $nonReservedScopes = implode(' ', $nonReservedScopes);

            return $this->storage->scopeExists($nonReservedScopes);
        }
    }

    public function checkScope($required_scope, $available_scope)
    {
        $required_scope = explode(' ', trim($required_scope));
        $available_scope = explode(' ', trim($available_scope));

        return (count(array_diff($required_scope, $available_scope)) == 0);
    }

    public function getScopeFromRequest($request)
    {
        return $request->request->get('scope', $request->query->get('scope'));
    }

    public function getReservedScopes()
    {
        return array('openid', 'offline_access');
    }

}
