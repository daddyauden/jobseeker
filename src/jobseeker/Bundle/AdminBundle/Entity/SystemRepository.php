<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SystemRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "s")
                ->add("from", "AdminBundle:System s")
                ->add("orderBy", "s.skey ASC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(s.id)")
                ->add("from", "AdminBundle:System s");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "s")
                ->add("from", "AdminBundle:System s")
                ->add("orderBy", "s.skey ASC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

}
