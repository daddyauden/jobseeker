<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractScope implements ScopeInterface, PluginInterface
{

    protected $container;
    protected $config;
    private static $plugins = null;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getConfig($name = null)
    {
        if ($name) {
            if (isset($this->config[$name])) {
                return $this->config[$name];
            } else {
                return null;
            }
        }
        return $this->config;
    }

    public function resetConfig($serialized)
    {
        $data = unserialize($serialized);
        foreach ($this->config as $key => $value) {
            if (array_key_exists($key, $data) && $configValue = $data[$key]) {
                $this->config[$key] = $configValue;
            } else {
                throw new \Exception(sprintf("%s is not set in %s", $key, var_dump($data)));
            }
        }
    }

    public function getDescription()
    {
        return $this->container->get("translator")->trans("plugin." . $this->getName() . ".description");
    }

    public static function registerScopes()
    {
        return array("user", "payment");
    }

    protected function detectScope($scope)
    {
        if (in_array($scope, self::registerScopes())) {
            return $scope;
        } else {
            throw new \Exception(sprintf("%s(scope) must exist in %s", $scope, var_export(self::registerScopes(), TRUE)));
        }
    }

    public static function registerPlugins()
    {
        if (self::$plugins !== null) {
            return self::$plugins;
        } else {
            $plugins = array();
            $pluginPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "Library" . DIRECTORY_SEPARATOR . "*Plugin.php";
            $pluginFiles = glob($pluginPath);
            if (is_array($pluginFiles) && count($pluginFiles) == 0) {
                return null;
            } else {
                foreach ($pluginFiles as $file) {
                    $pluginClass = "jobseeker\\Bundle\\PluginBundle\\Library\\" . pathinfo($file, PATHINFO_FILENAME);
                    $plugin = new $pluginClass;
                    if ($plugin instanceof AbstractScope) {
                        $plugins[$plugin->getScope()][$plugin->getName()] = $plugin;
                    }
                }
                if (count($plugins) > 0) {
                    return self::$plugins = $plugins;
                } else {
                    return null;
                }
            }
        }
    }

    abstract protected function getScope();

}
