<?php

namespace jobseeker\Bundle\UserBundle\DependencyInjection;

use Doctrine\Common\Persistence\ObjectManager;

class RoleManager
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

    public function createRole($name = null)
    {
        $class = $this->getClass();
        return new $class($name);
    }

    public function deleteRole(RoleInterface $role)
    {
        $this->objectManager->remove($role);
        $this->objectManager->flush();
    }

    public function updateRole(RoleInterface $role, $andFlush = true)
    {
        $this->objectManager->persist($role);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    public function findRoleBy(array $query)
    {
        return $this->repository->findOneBy($query);
    }

    public function findRoleById($id)
    {
        return $this->findRoleBy(array('id' => $id));
    }

    public function findRoleByName($name = null)
    {
        if ($name === null) {
            $name = RoleInterface::NORMAL;
        }

        $roleEntity = $this->findRoleBy(array('name' => $name));

        if (null === $roleEntity && RoleInterface::NORMAL === strtolower($name)) {
            try {
                $normalRoleEntity = $this->createRole(RoleInterface::NORMAL);
                $this->updateRole($normalRoleEntity);
                $roleEntity = $this->findRoleBy(array('name' => RoleInterface::NORMAL));
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $roleEntity;
    }

    public function findRoles()
    {
        return $this->repository->findAll();
    }

}
