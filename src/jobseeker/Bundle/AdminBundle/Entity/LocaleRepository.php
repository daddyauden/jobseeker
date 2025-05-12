<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LocaleRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "l")
                ->add("from", "AdminBundle:Locale l")
                ->add("orderBy", "l.queue ASC");
        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(l.id)")
                ->add("from", "AdminBundle:Locale l");
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "l")
                ->add("from", "AdminBundle:Locale l")
                ->add("orderBy", "l.queue ASC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);
        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "l")
                ->add("from", "AdminBundle:Locale l")
                ->add("where", "l.status = 1")
                ->add("orderBy", "l.queue ASC, l.code ASC");
        return $qb->getQuery()->getResult($hydration);
    }

}
