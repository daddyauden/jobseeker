<?php

namespace jobseeker\Bundle\AdminBundle;

use jobseeker\Bundle\ToolBundle\Base;

class AdminBase extends Base
{

    const ADMIN_COOKIE_SALT = "GPo&F]Gto8C){jq&,q^5\o@`(?'H|=U\jO2-1ip>:u3lB[QZ9z";

    protected function autoLogin()
    {
        if (null !== $this->getSession("aid")) {
            return true;
        }

        if ($this->hasCookie('AUTH')) {
            $data = $this->decodeCookie($this->getCookie("AUTH"));
            return ($data['logintime'] + ((int) $this->getSystem("cookie_expires"))) > time() ? true : false;
        }

        return false;
    }

    protected function encodeCookie($data)
    {
        return $this->encode($data, self::ADMIN_COOKIE_SALT);
    }

    protected function decodeCookie($data)
    {
        return $this->decode($data, self::ADMIN_COOKIE_SALT);
    }

}
