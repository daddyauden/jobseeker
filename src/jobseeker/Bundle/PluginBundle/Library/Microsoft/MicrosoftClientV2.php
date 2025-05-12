<?php

namespace jobseeker\Bundle\PluginBundle\Library\Microsoft;

class MicrosoftClientV2
{

    function __construct($akey, $skey, $redirect_uri, $access_token)
    {
        $this->oauth = new MicrosoftOAuthV2($akey, $skey, $redirect_uri, $access_token);
    }

    function set_debug($enable)
    {
        $this->oauth->debug = $enable;
    }

    function getMe()
    {
        return $this->oauth->get('me', array("access_token" => $this->oauth->access_token));
    }

}
