<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AreaRepository extends EntityRepository
{

    public function getAll($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "a")
                ->add("from", "AdminBundle:Area a")
                ->add("orderBy", "a.level ASC, a.pid ASC, a.alpha ASC, a.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(a.id)")
                ->add("from", "AdminBundle:Area a");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "a")
                ->add("from", "AdminBundle:Area a")
                ->add("orderBy", "a.level ASC, a.pid ASC, a.alpha ASC, a.queue DESC")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "a")
                ->add("from", "AdminBundle:Area a")
                ->add("where", "a.status = 1")
                ->add("orderBy", "a.level ASC, a.pid ASC, a.alpha ASC, a.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailableCountry($hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "a")
                ->add("from", "AdminBundle:Area a")
                ->add("where", "a.status = 1 and a.level = 2")
                ->add("orderBy", "a.pid ASC, a.alpha ASC, a.queue DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailableCity($countryCode, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "b, c")
                ->add("from", "AdminBundle:Area a")
                ->leftJoin("AdminBundle:Area", "b", "WITH", "b.pid = a.id")
                ->leftJoin("AdminBundle:Area", "c", "WITH", "c.pid = b.id")
                ->add("where", "a.code = :countryCode and b.status = 1 and c.status = 1")
                ->add("orderBy", "a.pid ASC, a.alpha ASC, a.queue DESC")
                ->setParameter("countryCode", strtolower($countryCode));

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailableDistrict($pid, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "a.id, a.code")
            ->add("from", "AdminBundle:Area a")
            ->add("where", "a.pid = :pid and a.status = 1 and a.level = 4")
            ->add("orderBy", "a.queue DESC")
            ->setParameter("pid", $pid);

        return $qb->getQuery()->getResult($hydration);
    }

}
