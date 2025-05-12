<?php

namespace jobseeker\Bundle\PluginBundle\Library;

use jobseeker\Bundle\PluginBundle\DependencyInjection\UserScope;
use jobseeker\Bundle\PluginBundle\Library\Weibo\SaeTOAuthV2;
use jobseeker\Bundle\PluginBundle\Library\Weibo\SaeTClientV2;

class WeiboPlugin extends UserScope
{

    public function __construct()
    {
        $this->config = array(
            "app_key" => "",
            "app_secret" => "",
            "scope" => "",
            "redirect_uri" => ""
        );
    }

    public function getName()
    {
        return "weibo";
    }

    public function getLogo()
    {
        return "data:image/png;base64,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";
    }

    public function getConfigForInstall()
    {
        $config = array();
        $config['app_key'] = "***";
        $config['app_secret'] = "***";
        $config['scope'] = "follow_app_official_microblog,email";
        $config['redirect_uri'] = $this->container->getParameter("host") . "/auth/callback";
        return $config;
    }

    public function registerProxy()
    {
        $proxy = new SaeTOAuthV2($this->getConfig("app_key"), $this->getConfig("app_secret"));
        $this->proxy = $proxy;
    }

    public function getLoginUri()
    {
        return array(
            $this->getName(),
            $this->getProxy()->getAuthorizeURL($this->getConfig("redirect_uri"), "code", $this->generateState(), $this->getConfig("scope"))
        );
    }

    public function authenticate($code)
    {
        $keys['code'] = $code;
        $keys['redirect_uri'] = $this->getConfig("redirect_uri");
        try {
            $token = $this->getProxy()->getAccessToken("code", $keys);
            parent::saveToken(json_encode($token));
        } catch (\Exception $e) {
            throw $e;
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
                $client = new SaeTClientV2($this->getConfig("app_key"), $this->getConfig("app_secret"), $value['access_token']);
                $this->setClient($client);
            }
        }
        $uid = $this->getClient()->get_uid();

        return $this->getClient()->show_user_by_id($uid['uid']);
    }

    public function getUserForSso()
    {
        $user = $this->getUser();

        return array(
            "source" => $this->getName(),
            "email" => $user["weihao"],
            "password" => $user['id']
        );
    }

}
