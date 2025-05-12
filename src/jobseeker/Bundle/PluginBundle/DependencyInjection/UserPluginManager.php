<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

class UserPluginManager extends AbstractPluginManager
{

    const SCOPE = "user";

    private $session_prefix = "userplugin_";
    protected $plugins = null;
    protected $activedPlugins = null;

    public function getScope()
    {
        return strtolower(self::SCOPE);
    }

    protected function getSessionPrefix()
    {
        return $this->session_prefix;
    }

}
