<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

abstract class UserScope extends AbstractScope
{

    const SCOPE = "user";

    protected $proxy = null;
    protected $client = null;

    public function getScope()
    {
        return $this->detectScope(self::SCOPE);
    }

    protected function getProxy()
    {
        if ($this->proxy === null) {
            $this->registerProxy();
        }

        return $this->proxy;
    }

    protected function setClient($client)
    {
        $this->client = $client;
    }

    protected function getClient()
    {
        return $this->client;
    }

    protected function generateState()
    {
        $salt = substr(preg_replace("/[0-9]/", "", strtoupper(base64_encode(hash("md5", uniqid(mt_rand(), true))))), 0, 30);
        $this->container->get("UserPluginManager")->saveState(array("name" => $this->getName(), "salt" => $salt));

        return $salt;
    }

    protected function saveToken($token)
    {
        $this->container->get("UserPluginManager")->saveToken(array($this->getName() => $token));
    }

    abstract protected function registerProxy();

    abstract protected function getLoginUri();

    abstract protected function authenticate($code);

    abstract protected function getUser();

    abstract protected function getUserForSso();

}
