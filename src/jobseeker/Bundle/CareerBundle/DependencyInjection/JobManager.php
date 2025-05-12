<?php

namespace jobseeker\Bundle\CareerBundle\DependencyInjection;

use Doctrine\Common\Persistence\ObjectManager;

class JobManager
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

    protected function findJobBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    public function findJobById($id)
    {
        return $this->repository->findOneBy(array('id' => (int) $id));
    }

    public function findJobByUid($uid)
    {
        return $this->findJobBy(array('uid' => (int) $uid));
    }

    public function findJobByEmail($email)
    {
        return $this->findJobBy(array('email' => $email));
    }

    public function findJobByEP($email, $password)
    {
        return $this->findJobBy(array('email' => $email, 'password' => $password));
    }

    public function findJobBySEP($source, $email, $password)
    {
        return $this->findJobBy(array("source" => $source, 'email' => $email, 'password' => $password));
    }

}
