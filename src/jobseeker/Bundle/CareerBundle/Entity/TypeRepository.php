<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "t")
                ->add("from", "CareerBundle:Type t")
                ->add("orderBy", "t.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "t")
                ->add("from", "CareerBundle:Type t")
                ->add("where", "t.status = 1")
                ->add("orderBy", "t.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

}
