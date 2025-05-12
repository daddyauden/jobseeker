<?php

namespace jobseeker\Bundle\PluginBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PluginBundle extends Bundle
{

    private static $loaded = false;

    public function boot()
    {
        if (self::$loaded === true) {
            return;
        }
        spl_autoload_register(array("self", "autoload"));
        self::$loaded = true;
    }

    public static function autoload($class)
    {
        if (0 === strpos($class, "Google_")) {
            $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "Library" . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $class) . ".php";
            if (file_exists($filePath)) {
                require $filePath;
            } else {
                return;
            }
        } else {
            return;
        }
    }

}
