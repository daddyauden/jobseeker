<?php

namespace jobseeker\Bundle\SsoBundle\DependencyInjection;

use Doctrine\Common\Persistence\ObjectManager;

class UserManager implements UserInterface
{

    protected $objectManager;
    protected $repository;
    protected $class;

    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);
        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class;
        $uid = $this->generateUid();
        $user->setUid($uid);
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

    public function findUserBy(array $criteria)
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

    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('email' => $email));
    }

    public function findUserByEP($email, $password)
    {
        return $this->findUserBy(array('email' => $email, 'password' => $password));
    }

    public function findUsers()
    {
        return $this->repository->findAll();
    }

    public function generateUid()
    {
        $times = self::UID_DIGIT;
        $randNum = "123456789";
        $randStr = "";
        for ($i = 0; $i < $times; $i++) {
            $randomIndex = mt_rand(0, 8);
            $randStr .= $randNum[$randomIndex];
        }
        $uid = (int) $randStr;
        $res = $this->findUserByUid($uid);
        while ($res) {
            $this->generateUid();
        }
        return $uid;
    }

}
