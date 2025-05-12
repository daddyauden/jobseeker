<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PrivilegeRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "AdminBundle:Privilege p")
                ->add("orderBy", "p.bundle ASC, p.controller ASC, p.action ASC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(p.id)")
                ->add("from", "AdminBundle:Privilege p");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "AdminBundle:Privilege p")
                ->add("orderBy", "p.bundle ASC, p.route ASC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "AdminBundle:Privilege p")
                ->add("where", "p.status = 1")
                ->add("orderBy", "p.route ASC");
        return $qb->getQuery()->getResult($hydration);
    }

}
