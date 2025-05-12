<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "CareerBundle:Product p")
                ->add("orderBy", "p.queue DESC, p.duration DESC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(p.id)")
                ->add("from", "CareerBundle:Product p");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "CareerBundle:Product p")
                ->add("orderBy", "p.queue DESC, p.duration DESC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "CareerBundle:Product p")
                ->add("where", "p.status = 1")
                ->add("orderBy", "p.queue DESC, p.duration DESC");
        return $qb->getQuery()->getResult($hydration);
    }

}
