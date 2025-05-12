<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RoleRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "r")
                ->add("from", "UserBundle:Role r")
                ->add("orderBy", "r.id ASC, r.name ASC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(r.id)")
                ->add("from", "UserBundle:Role r");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "r")
                ->add("from", "UserBundle:Role r")
                ->add("orderBy", "r.id ASC, r.name ASC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);

        return $qb->getQuery()->getResult($hydration);
    }

}
