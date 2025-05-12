<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractPluginManager
{

    protected $container;
    protected $objectManager;
    protected $repository;
    protected $class;

    public function __construct(ContainerInterface $container, ObjectManager $objectManager, $class)
    {
        $this->container = $container;
        $this->objectManager = $objectManager;
        $this->repository = $objectManager->getRepository($class);
        $metadata = $objectManager->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function registerPlugin()
    {
        $installedPlugins = $this->getRepo()->getAll($this->getScope());

        if (count($installedPlugins) > 0) {
            foreach ($installedPlugins as $plugin) {
                $pluginName = strtolower($plugin['name']);
                try {
                    $servicePlugin = $this->getContainer()->get($pluginName . "Plugin");
                } catch (\Exception $e) {
                    throw $e;
                }
                $servicePlugin->resetConfig($plugin['config']);
                $servicePlugin->registerProxy();
                $this->setPlugin($servicePlugin, (boolean) $plugin['status'] ? true : false);
            }
        }
    }

    public function autoLogin()
    {
        if (null === $this->getToken()) {
            return $this->getLoginUri();
        } else {
            $this->removeState();
            $this->removeToken();
            return array();
        }
    }

    public function getLoginUri()
    {
        $loginUris = array();
        $activedPlugin = $this->getPlugin(null, true);
        if (null !== $activedPlugin) {
            foreach ($activedPlugin as $plugin) {
                $loginUris[] = $plugin->getLoginUri();
            }
        }

        return $loginUris;
    }

    protected function getClass()
    {
        return $this->class;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function getRepo()
    {
        return $this->repository;
    }

    protected function hasSession($name)
    {
        $name = $this->getSessionPrefix() . $name;
        return $this->getContainer()->get("session")->has($name);
    }

    protected function getSession($name, $default = null)
    {
        $name = $this->getSessionPrefix() . $name;
        return $this->getContainer()->get("session")->get($name, $default);
    }

    protected function setSession($name, $value)
    {
        $name = $this->getSessionPrefix() . $name;
        return $this->getContainer()->get("session")->set($name, $value);
    }

    protected function removeSession($name)
    {
        $name = $this->getSessionPrefix() . $name;
        return $this->getContainer()->get("session")->remove($name);
    }

    protected function validateScope($scope)
    {
        return $this->getScope() === strtolower($scope);
    }

    protected function setPlugin($plugin, $isActived = false)
    {
        $pluginName = strtolower($plugin->getName());
        $this->plugins[$pluginName] = $plugin;
        if ($isActived === true) {
            $this->activedPlugins[$pluginName] = $plugin;
        }
    }

    public function getPlugin($name = null, $isActived = false)
    {
        if ($this->plugins === null) {
            $this->registerPlugin();
        }

        if ($name !== null) {
            $name = strtolower($name);
        }

        if ($isActived === true) {
            return !isset($this->activedPlugins[$name]) || $name === null ? $this->activedPlugins : $this->activedPlugins[$name];
        } else {
            return !isset($this->plugins[$name]) || $name === null ? $this->plugins : $this->plugins[$name];
        }
    }

    protected function hasState()
    {
        return $this->hasSession("state");
    }

    public function getState()
    {

        return $this->hasState() ? unserialize($this->getSession("state")) : null;
    }

    public function saveState(array $data = array())
    {
        $state = $this->getState();
        if (null !== $state) {
            $state[$data['name']] = $data['salt'];
            $this->setSession("state", serialize($state));
        } else {
            $this->setSession("state", serialize(array($data['name'] => $data['salt'])));
        }
    }

    public function removeState()
    {
        if ($this->hasState()) {
            $this->removeSession("state");
        }
    }

    public function validateState($state = null)
    {
        if ($state === null) {
            return null;
        }
        if (null !== $data = $this->getState()) {
            foreach ($data as $name => $value) {
                if ($value === $state) {
                    return $name;
                }
            }
            return null;
        } else {
            return null;
        }
    }

    protected function hasToken()
    {
        return $this->hasSession("token");
    }

    public function getToken()
    {
        if ($this->hasToken()) {
            $data = array();
            $token = unserialize($this->getSession("token"));
            foreach ($token as $name => $value) {
                $data['name'] = $name;
                $data['value'] = json_decode($value, true);
            }
            return $data;
        } else {
            return null;
        }
    }

    public function saveToken(array $token = array())
    {
        $this->setSession("token", serialize($token));
    }

    public function removeToken()
    {
        if ($this->hasToken()) {
            $this->removeSession("token");
        }
    }

    abstract public function getScope();

}
