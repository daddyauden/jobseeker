<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AdminRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "a, role")
                ->add("from", "AdminBundle:Admin a")
                ->leftJoin("a.rid", "role")
                ->add("orderBy", "a.logintime DESC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(a.id)")
                ->add("from", "AdminBundle:Admin a");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "a, role")
                ->add("from", "AdminBundle:Admin a")
                ->leftJoin("a.rid", "role")
                ->add("orderBy", "a.logintime DESC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

}
