<?php

namespace jobseeker\Bundle\UserBundle\DependencyInjection;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use jobseeker\Bundle\ToolBundle\Service\CurlService;
use jobseeker\Bundle\ToolBundle\Service\StreamService;

class UserManager
{

    protected $container;
    protected $objectManager;
    protected $repository;
    protected $class;
    protected $ssoHost;
    protected $apiUrls = array();

    public function __construct(ContainerInterface $container, ObjectManager $om, $class)
    {
        $this->container = $container;
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);
        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
        $this->ssoHost = $container->getParameter("sso_host");
        $this->apiUrls = array(
            "user_get" => $this->ssoHost . "/api/user/get",
            "user_register" => $this->ssoHost . "/api/user/register",
            "user_active" => $this->ssoHost . "/api/user/active",
        );
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class;
        return $user;
    }

    public function deleteUser($user)
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }

    public function updateUser($user, $andFlush = true)
    {
        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    protected function findUserBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findUserById($id)
    {
        return $this->findUserBy(array('id' => (int) $id));
    }

    public function findUserByUid($uid)
    {
        return $this->findUserBy(array('uid' => (int) $uid));
    }

    public function findSsoUserByEmail($email)
    {
        return $this->findSsoUserBy(array("email" => $email));
    }

    public function findSsoUserByUid($uid)
    {
        return $this->findSsoUserBy(array("uid" => $uid));
    }

    public function findSsoUserByEP($email, $password)
    {
        return $this->findSsoUserBy(array("email" => $email, "password" => $password));
    }

    public function findUserByPlugin($pluginUser)
    {
        if (!isset($pluginUser['source']) && !isset($pluginUser['email']) && !isset($pluginUser['password'])) {
            return null;
        }

        return $this->findSsoUserBySEP($pluginUser['source'], $pluginUser['email'], $pluginUser['password']);
    }

    protected function findSsoUserBySEP($source, $email, $password)
    {
        return $this->findSsoUserBy(array("source" => $source, 'email' => $email, 'password' => $password));
    }

    /**
     * @return $ssoUser
     * array: sso user detail: uid,email,password(encrypted),username,first_name,last_name,country,addtime
     * false: sso error
     * 
     */
    protected function findSsoUserBy($option)
    {
        $params = array();
        $params['client_id'] = $this->getContainer()->getParameter("sso_client_id");
        $params['client_secret'] = $this->getContainer()->getParameter("sso_client_secret");

        $ssoUser = CurlService::post($this->apiUrls['user_get'], array_merge($params, $option));
        if ($ssoUser === null || isset($ssoUser['error'])) {
            return false;
        }

        return $ssoUser;
    }

    /**
     * @return $ssoUser
     * true: sso user exit
     * false: sso error
     * array: sso user detail: uid,source,country,email,password(encrypted),username,first_name,last_name,addtime,status,salt
     * 
     */
    public function createSsoUser($email, $password, $country, $source = "jobseeker", $isEncrypted = true)
    {
        $params = array();
        $params['client_id'] = $this->getContainer()->getParameter("sso_client_id");
        $params['client_secret'] = $this->getContainer()->getParameter("sso_client_secret");
        $params['email'] = $email;
        $params['password'] = $password;
        $params['country'] = $country;
        $params['source'] = $source;
        $params['isEncrypted'] = $isEncrypted;

        $ssoUser = CurlService::post($this->apiUrls['user_register'], $params);

        if ($ssoUser === null || isset($ssoUser['error'])) {
            if (((int) $ssoUser['error']['code']) === 400) {
                return true;
            }
            return false;
        }

        return $ssoUser;
    }

    public function activeSsoUser($salt, $uid)
    {
        $params = array();
        $params['client_id'] = $this->getContainer()->getParameter("sso_client_id");
        $params['client_secret'] = $this->getContainer()->getParameter("sso_client_secret");
        $params['salt'] = $salt;
        $params['uid'] = $uid;

        $ssoUser = CurlService::post($this->apiUrls['user_active'], $params);

        if ($ssoUser === false) {
            return false;
        } else {
            return $ssoUser;
        }
    }

}
