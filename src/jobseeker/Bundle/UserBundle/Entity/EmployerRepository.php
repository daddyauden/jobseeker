<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EmployerRepository extends EntityRepository
{

    public function getByUser($by, $hydration = 2)
    {
        switch ($by[0]) {
            case "email":
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->add("select", "er, user, area, type, scale")
                    ->add("from", "UserBundle:Employer er")
                    ->leftJoin("UserBundle:User", "user", "WITH", "user.uid = er.uid")
                    ->leftJoin("er.location", "area", "WITH", "area.id = er.location")
                    ->leftJoin("er.type", "type", "WITH", "type.id = er.type")
                    ->leftJoin("er.scale", "scale", "WITH", "scale.id = er.scale")
                    ->where("user.email = :email")
                    ->setParameter("email", $by[1]);
                break;
            case "uid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->add("select", "er, user, area, type, scale")
                    ->add("from", "UserBundle:Employer er")
                    ->leftJoin("UserBundle:User", "user", "WITH", "user.uid = er.uid")
                    ->leftJoin("er.location", "area", "WITH", "area.id = er.location")
                    ->leftJoin("er.type", "type", "WITH", "type.id = er.type")
                    ->leftJoin("er.scale", "scale", "WITH", "scale.id = er.scale")
                    ->where("user.uid = :uid")
                    ->setParameter("uid", (int)$by[1]);
                break;
            case "id":
            default :
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->add("select", "er, user, area, type, scale")
                    ->add("from", "UserBundle:Employer er")
                    ->leftJoin("UserBundle:User", "user", "WITH", "user.uid = er.uid")
                    ->leftJoin("er.location", "area", "WITH", "area.id = er.location")
                    ->leftJoin("er.type", "type", "WITH", "type.id = er.type")
                    ->leftJoin("er.scale", "scale", "WITH", "scale.id = er.scale")
                    ->where("user.id = :id")
                    ->setParameter("id", $by[1]);
                break;
        }

        $result = $qb->getQuery()->getResult($hydration);

        if (isset($result[0])) {
            if (isset($result[1]) && count($result[1]) > 0) {
                $result[0]['uid'] = $result[1];
            }

            return $result[0];
        } else {
            return $result;
        }
    }

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "er, user, area, type, scale")
            ->add("from", "UserBundle:Employer er")
            ->leftJoin("UserBundle:User", "user", "WITH", "user.uid = er.uid")
            ->leftJoin("er.location", "area", "WITH", "area.id = er.location")
            ->leftJoin("er.type", "type", "WITH", "type.id = er.type")
            ->leftJoin("er.scale", "scale", "WITH", "scale.id = er.scale")
            ->add("orderBy", "user.logintime DESC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "er, user, area, type, scale")
            ->add("from", "UserBundle:Employer er")
            ->leftJoin("UserBundle:User", "user", "WITH", "user.uid = er.uid")
            ->leftJoin("er.location", "area", "WITH", "area.id = er.location")
            ->leftJoin("er.type", "type", "WITH", "type.id = er.type")
            ->leftJoin("er.scale", "scale", "WITH", "scale.id = er.scale")
            ->add("orderBy", "user.logintime DESC")
            ->setFirstResult(($page - 1) * $offset)
            ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAllAvaliable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "er, user, area, type, scale")
            ->add("from", "UserBundle:Employer er")
            ->leftJoin("UserBundle:User", "user", "WITH", "user.uid = er.uid")
            ->leftJoin("er.location", "area", "WITH", "area.id = er.location")
            ->leftJoin("er.type", "type", "WITH", "type.id = er.type")
            ->leftJoin("er.scale", "scale", "WITH", "scale.id = er.scale")
            ->add("where", "user.status = 1")
            ->add("orderBy", "user.logintime DESC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "count(er.id)")
            ->add("from", "UserBundle:Employer er");
        return $qb->getQuery()->getSingleScalarResult();
    }

}
