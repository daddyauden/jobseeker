<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DiscountRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "d")
                ->add("from", "CareerBundle:Discount d")
                ->add("orderBy", "d.queue ASC, d.rate DESC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(d.id)")
                ->add("from", "CareerBundle:Discount d");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "d")
                ->add("from", "CareerBundle:Discount d")
                ->add("orderBy", "d.queue ASC, d.rate DESC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "d")
                ->add("from", "CareerBundle:Discount d")
                ->add("where", "d.status = 1")
                ->add("orderBy", "d.queue ASC, d.rate DESC");
        return $qb->getQuery()->getResult($hydration);
    }

}
