<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CurrencyRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "c")
                ->add("from", "AdminBundle:Currency c")
                ->add("orderBy", "c.code ASC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(c.id)")
                ->add("from", "AdminBundle:Currency c");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "c")
                ->add("from", "AdminBundle:Currency c")
                ->add("orderBy", "c.code ASC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "c")
                ->add("from", "AdminBundle:Currency c")
                ->add("where", "c.status = 1")
                ->add("orderBy", "c.code ASC");
        return $qb->getQuery()->getResult($hydration);
    }

}
