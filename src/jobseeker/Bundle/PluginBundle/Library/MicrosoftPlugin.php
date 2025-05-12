<?php

namespace jobseeker\Bundle\PluginBundle\Library;

use jobseeker\Bundle\PluginBundle\DependencyInjection\UserScope;
use jobseeker\Bundle\PluginBundle\Library\Microsoft\MicrosoftOAuthV2;
use jobseeker\Bundle\PluginBundle\Library\Microsoft\MicrosoftClientV2;

class MicrosoftPlugin extends UserScope
{

    public function __construct()
    {
        $this->config = array(
            "client_id" => "",
            "client_secret" => "",
            "scope" => "",
            "redirect_uri" => ""
        );
    }

    public function getName()
    {
        return "microsoft";
    }

    public function getLogo()
    {
        return "data:image/png;base64,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UmVhZHlxyWU8AAADc2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMTQgNzkuMTUxNDgxLCAyMDEzLzAzLzEzLTEyOjA5OjE1ICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjUzODMyZWY4LWRmMGMtNGE1Yy1hMjNiLWJhMTczMTY0YTRhNCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDowQjgzRDkwNEZCNUMxMUUzQUQ1Q0Q3ODE0NDFDMDFBRCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDowQjgzRDkwM0ZCNUMxMUUzQUQ1Q0Q3ODE0NDFDMDFBRCIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1MzgzMmVmOC1kZjBjLTRhNWMtYTIzYi1iYTE3MzE2NGE0YTQiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NTM4MzJlZjgtZGYwYy00YTVjLWEyM2ItYmExNzMxNjRhNGE0Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+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";
    }

    public function getConfigForInstall()
    {
        $config = array();
        $config['client_id'] = "***";
        $config['client_secret'] = "***";
        $config['scope'] = "wl.basic,wl.signin,wl.emails,wl.calendars";
        $config['redirect_uri'] = $this->container->getParameter("host") . "/auth/callback";
        return $config;
    }

    public function registerProxy()
    {
        $proxy = new MicrosoftOAuthV2($this->getConfig("client_id"), $this->getConfig("client_secret"), $this->getConfig("redirect_uri"));
        $this->proxy = $proxy;
        $proxy->setScopes(explode(",", $this->getConfig("scope")));
        $this->proxy = $proxy;
    }

    public function getLoginUri()
    {
        return array(
            $this->getName(),
            $this->getProxy()->getAuthorizeURL("code", $this->generateState())
        );
    }

    public function authenticate($code)
    {
        $keys['code'] = $code;
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
                $client = new MicrosoftClientV2($this->getConfig("client_id"), $this->getConfig("client_secret"), $this->getConfig("redirect_uri"), $value['access_token']);
                $this->setClient($client);
            }
        }

        return $this->getClient()->getMe();
    }

    public function getUserForSso()
    {
        $user = $this->getUser();

        return array(
            "source" => $this->getName(),
            "email" => $user["emails"]['account'],
            "password" => $user['id']
        );
    }

}
