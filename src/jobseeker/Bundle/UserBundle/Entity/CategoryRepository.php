<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{

    public function getAll($type, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "c")
                ->add("from", "UserBundle:Category c")
                ->add("where", "c.type = :type")
                ->add("orderBy", "c.pid ASC, c.queue DESC, c.csn ASC")
                ->setParameter("type", $type);

        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal($type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(c.id)")
                ->add("from", "UserBundle:Category c")
                ->add("where", "c.type = :type")
                ->setParameter("type", $type);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPager($type, $page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "c")
                ->add("from", "UserBundle:Category c")
                ->add("where", "c.type = :type")
                ->add("orderBy", "c.pid ASC, c.queue DESC, c.csn ASC")
                ->setParameter("type", $type)
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset);

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAvailable($type, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "c")
                ->add("from", "UserBundle:Category c")
                ->add("where", "c.status = 1 and c.type = :type")
                ->add("orderBy", "c.pid ASC, c.queue DESC, c.csn ASC")
                ->setParameter("type", $type);

        return $qb->getQuery()->getResult($hydration);
    }

}
