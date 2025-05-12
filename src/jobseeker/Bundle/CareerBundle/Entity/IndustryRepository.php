<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class IndustryRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "i")
            ->add("from", "CareerBundle:Industry i")
            ->add("orderBy", "i.pid ASC, i.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "count(i.id)")
            ->add("from", "CareerBundle:Industry i");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "i, b")
            ->add("from", "CareerBundle:Industry i")
            ->leftJoin("CareerBundle:Industry", "b", "WITH", "b.pid = i.id")
            ->add("where", "i.status = 1 and b.status = 1")
            ->add("orderBy", "i.queue DESC, b.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailableSubIndustry($pid, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "i.id, i.isn")
            ->add("from", "CareerBundle:Industry i")
            ->add("where", "i.pid = :pid and i.status = 1")
            ->add("orderBy", "i.queue DESC")
            ->setParameter("pid", $pid);

        return $qb->getQuery()->getResult($hydration);
    }

}
