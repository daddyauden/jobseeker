<?php

namespace jobseeker\Bundle\PluginBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PluginRepository extends EntityRepository
{

    public function getAll($scope = null, $status = null, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "p")
                ->add("from", "PluginBundle:Plugin p");

        if ($scope !== null) {
            $qb->add("where", "p.scope = :scope")->setParameter("scope", strtolower($scope));
        }

        if ($status !== null) {
            $qb->add("where", "p.status = :status")->setParameter("status", (int) $status);
        }

        $qb->add("orderBy", "p.scope DESC");

        return $qb->getQuery()->getResult($hydration);
    }

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(p.id)")
                ->add("from", "PluginBundle:Plugin p");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAvailable($scope = "all", $hydration = 2)
    {
        switch ($scope) {
            case "all":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "p")
                        ->add("from", "PluginBundle:Plugin p")
                        ->add("where", "p.status = 1")
                        ->add("orderBy", "p.scope DESC");
                break;
            default :
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "p")
                        ->add("from", "PluginBundle:Plugin p")
                        ->add("where", "p.scope = :scope and p.status = 1")
                        ->add("orderBy", "p.scope DESC")
                        ->setParameter("scope", $scope);
                break;
        }

        return $qb->getQuery()->getResult($hydration);
    }

}
