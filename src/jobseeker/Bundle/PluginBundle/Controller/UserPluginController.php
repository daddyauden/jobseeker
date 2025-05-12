<?php

namespace jobseeker\Bundle\PluginBundle\Controller;

use jobseeker\Bundle\ToolBundle\Base;

class UserPluginController extends Base
{

    protected $entityName = "PluginBundle:Plugin";

    public function callbackAction()
    {
        $referer = $this->getRequest()->headers->get('referer');
        $jobsearchURL = $this->generateurl("job_post");

        $state = $this->getRequest()->get("state");
        $pluginName = $this->getPluginManager()->validateState($state);
        if ($state && $pluginName) {
            try {
                $code = $this->getRequest()->get("code");
                $this->getPluginManager()->getPlugin($pluginName, true)->authenticate($code);
            } catch (\Exception $e) {
                $this->getPluginManager()->removeState();
                $this->getPluginManager()->removeToken();
                $this->get("session")->getFlashBag()->add("danger", sprintf("Sorry! Signin %s error, Please try again", ucfirst($pluginName) ?: ""));
                if (strpos($referer, $jobsearchURL) !== false) {
                    return $this->redirect($jobsearchURL);
                } else {
                    return $this->redirect($this->generateUrl("index"));
                }
            }

            try {
                return $this->signin($pluginName);
            } catch (\Exception $e) {
                $this->getPluginManager()->removeState();
                $this->getPluginManager()->removeToken();
                $this->get("session")->getFlashBag()->add("danger", sprintf("Sorry! Signin %s error, Please try again.", ucfirst($pluginName) ?: ""));
                if (strpos($referer, $jobsearchURL) !== false) {
                    return $this->redirect($jobsearchURL);
                } else {
                    return $this->redirect($this->generateUrl("index"));
                }
            }
        } else {
            $this->getPluginManager()->removeState();
            $this->getPluginManager()->removeToken();
            $this->get("session")->getFlashBag()->add("danger", sprintf("Sorry! Signin %s error, Please try again.", ucfirst($pluginName) ?: ""));
            if (strpos($referer, $jobsearchURL) !== false) {
                return $this->redirect($jobsearchURL);
            } else {
                return $this->redirect($this->generateUrl("index"));
            }
        }
    }

    public function revokeAction()
    {
        if ($token = $this->getPluginManager()->getToken()) {
            $this->getPluginManager()->removeState();
            $this->getPluginManager()->removeToken();
            try {
                $this->getPluginManager()->getPlugin($token['name'], true)->revokeToken($token['value']);
                return $this->redirect($this->generateUrl("index"));
            } catch (\Exception $e) {
                return $this->redirect($this->generateUrl("index"));
            }
        } else {
            $this->getPluginManager()->removeState();
            $this->getPluginManager()->removeToken();
            return $this->redirect($this->generateUrl("index"));
        }
    }

    private function signin($pluginName)
    {
        $referer = $this->getRequest()->headers->get('referer');
        $jobsearchURL = $this->generateurl("job_post");

        $pluginName = strtolower($pluginName);
        $pluginUser = $this->getPluginManager()->getPlugin($pluginName, true)->getUserForSso();
        $userManager = $this->get("UserManager");
        $ssoUser = $userManager->findUserByPlugin($pluginUser);
        $country = $this->getSystem("country") ?: "cn";
        if (false === $ssoUser) {
            $ssoUser = $userManager->createSsoUser($pluginUser['email'], $pluginUser['password'], $country, $pluginName);
            if ($ssoUser === true) {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.exist"));
            } else if ($ssoUser === false) {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.error"));
            } else if (!isset($ssoUser['uid'])) {
                $this->get("session")->getFlashBag()->add("danger", $this->trans("user.signup.error"));
            } else {
                $role = $this->get("RoleManager")->findRoleByName();
                $userEntity = $userManager->createUser();
                $userEntity->setUid($ssoUser['uid']);
                $userEntity->setRid($role);
                $userEntity->setLogintime($ssoUser['addtime']);
                $userEntity->setLoginIp($this->getClientIp());
                $userManager->updateUser($userEntity);
                $cookie = array(
                    "name" => "UID",
                    "value" => $this->encodeCookie($userEntity->serialize(array("status" => $ssoUser["status"], "email" => $ssoUser["email"], "source" => $ssoUser["source"], "country" => $ssoUser["country"]))),
                    "expire" => $this->printCookieExpire($ssoUser['addtime']),
                    "domain" => $this->getSystem("domain"),
                    "path" => "/",
                    "secure" => false,
                    "httpOnly" => true
                );
                $this->sendCookie($cookie);
            }

            if (strpos($referer, $jobsearchURL) !== false) {
                return $this->redirect($jobsearchURL);
            } else {
                return $this->redirect($this->generateUrl("index"));
            }
        } else {
            $userEntity = $userManager->findUserByUid($ssoUser["uid"]);
            if (null === $userEntity) {
                $role = $this->get("RoleManager")->findRoleByName();
                $userEntity = $userManager->createUser();
                $userEntity->setUid($ssoUser["uid"]);
                $userEntity->setRid($role);
            }
            $timestamp = time();
            $userEntity->setLogintime($timestamp);
            $userEntity->setLoginIp($this->getClientIp());
            $userManager->updateUser($userEntity);
            $cookie = array(
                "name" => "UID",
                "value" => $this->encodeCookie($userEntity->serialize(array("status" => $ssoUser["status"], "email" => $ssoUser["email"], "source" => $ssoUser["source"], "country" => $ssoUser["country"]))),
                "expire" => $this->printCookieExpire($timestamp),
                "domain" => $this->getSystem("domain"),
                "path" => "/",
                "secure" => false,
                "httpOnly" => true
            );
            $this->sendCookie($cookie);

            if (strpos($referer, $jobsearchURL) !== false) {
                return $this->redirect($jobsearchURL);
            } else {
                return $this->redirect($this->generateUrl("index"));
            }
        }
    }

    private function getPluginManager()
    {
        return $this->get("UserPluginManager");
    }

    final public function dumpCache()
    {

        $plugin = array();
        $pluginEntity = $this->getRepo()->getAll();
        if (count($pluginEntity) > 0) {
            foreach ($pluginEntity as $value) {
                $plugin[$value['name']] = $value;
                $plugin[$value['name']]['status'] = (int)$value['status'];
                $plugin[$value['name']]['logo'] = $this->get($value['name'])->getLogo();
            }
        }
        parent::buildCache($plugin);
    }

}
