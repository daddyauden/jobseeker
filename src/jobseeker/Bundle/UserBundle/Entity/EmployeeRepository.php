<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EmployeeRepository extends EntityRepository
{

    public function getByUser($by, $hydration = 2)
    {
        switch ($by[0]) {
            case "email":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "ee, u")
                        ->add("from", "UserBundle:Employee ee")
                        ->leftJoin("UserBundle:User", "u", "WITH", "u.uid = ee.uid")
                        ->where("u.email = :email")
                        ->setParameter("email", $by[1]);
                break;
            case "uid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "ee, u")
                        ->add("from", "UserBundle:Employee ee")
                        ->leftJoin("UserBundle:User", "u", "WITH", "u.uid = ee.uid")
                        ->where("u.uid = :uid")
                        ->setParameter("uid", $by[1]);
                break;
            case "id":
            default :
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "ee, u")
                        ->add("from", "UserBundle:Employee ee")
                        ->leftJoin("UserBundle:User", "u", "WITH", "u.uid = ee.uid")
                        ->where("u.id = :id")
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
                ->add("select", "ee")
                ->add("from", "UserBundle:Employee ee");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "ee")
                ->add("from", "UserBundle:Employee ee")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);

        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(ee.id)")
                ->add("from", "UserBundle:Employee ee");

        return $qb->getQuery()->getSingleScalarResult();
    }

}
