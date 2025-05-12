<?php

namespace jobseeker\Bundle\PluginBundle\Library;

use jobseeker\Bundle\PluginBundle\DependencyInjection\UserScope;

class GooglePlugin extends UserScope
{

    public function __construct()
    {
        $this->config = array(
            "client_id" => "",
            "client_secret" => "",
            "scope" => "",
            "api_key" => "",
            "redirect_uri" => ""
        );
    }

    public function getName()
    {
        return "google";
    }

    public function getLogo()
    {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkY2N0Y2NUY5REJGRDExRTNBNzM3RTM4RjFEQzQ3MkVDIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkY2N0Y2NUZBREJGRDExRTNBNzM3RTM4RjFEQzQ3MkVDIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6RjY3RjY1RjdEQkZEMTFFM0E3MzdFMzhGMURDNDcyRUMiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6RjY3RjY1RjhEQkZEMTFFM0E3MzdFMzhGMURDNDcyRUMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6k+Ee1AAAPP0lEQVR42uxbC3RW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";
    }

    public function getConfigForInstall()
    {
        $config = array();
        $config['client_id'] = "***";
        $config['client_secret'] = "***";
        $config['scope'] = "userinfo.email,userinfo.profile";
        $config['api_key'] = "AIzaSyDc5Qg8NjeK9ZTnWpr_MPS0gy17eGSBioE";
        $config['redirect_uri'] = $this->container->getParameter("host") . "/auth/callback";
        return $config;
    }

    public function registerProxy()
    {
        $scopes = array();
        $config = new \Google_Config();
        $config->setApplicationName("jobseeker");
        $config->setCacheClass("Google_Cache_Memcache");
        $config->setIoClass("Google_IO_Curl");
        $config->setClassConfig("Google_Cache_Memcache", "host", $this->container->getParameter("session_memcached_host"));
        $config->setClassConfig("Google_Cache_Memcache", "port", $this->container->getParameter("session_memcached_port"));
        $config->setClientId($this->getConfig("client_id"));
        $config->setClientSecret($this->getConfig("client_secret"));
        $config->setDeveloperKey($this->getConfig("api_key"));
        $config->setRedirectUri($this->getConfig("redirect_uri"));
        $proxy = new \Google_Client($config);
        foreach (explode(",", $this->getConfig("scope")) as $scope) {
            $scopes[] = "https://www.googleapis.com/auth/" . $scope;
        }
        $proxy->setScopes($scopes);
        $this->proxy = $proxy;
    }

    public function getLoginUri()
    {
        $this->getProxy()->setState($this->generateState());
        return array(
            $this->getName(),
            $this->getProxy()->createAuthUrl()
        );
    }

    public function authenticate($code)
    {
        try {
            $token = $this->getProxy()->authenticate($code);
            parent::saveToken($token);
        } catch (\Exception $e) {
            throw new \Google_Auth_Exception($e->getMessage(), $e->getCode());
        }
    }

    public function revokeToken($token)
    {
        return $this->getProxy()->revokeToken($token);
    }

    public function getUser()
    {
        if ($this->getClient() == null) {
            $token = $this->container->get("UserPluginManager")->getToken();
            if ($token['name'] == $this->getName() && $value = $token['value']) {
                $client = new \Google_Service_Oauth2($this->getProxy());
                $this->setClient($client);
            }
        }
        return $this->getClient()->userinfo_v2_me->get();
    }

    public function getUserForSso()
    {
        $user = $this->getUser();

        return array(
            "source" => $this->getName(),
            "email" => $user["email"],
            "password" => $user['id']
        );
    }

}
