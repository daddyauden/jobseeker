<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ScopeRepository extends EntityRepository
{

    public function findScopeBy($fieldName, $fieldValue, $hydration = 2)
    {

        switch ($fieldName) {
            case "scope":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "s")
                        ->add("from", "SsoBundle:Scope s")
                        ->add("where", "s.scope = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
            case "isDefault":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "s")
                        ->add("from", "SsoBundle:Scope s")
                        ->add("where", "s.isDefault = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
        }

        return $qb->getQuery()->getResult($hydration);
    }

    public function getMatchScopeNum()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(s.scope)")
                ->add("from", "SsoBundle:Scope s");
        return $qb->getQuery()->getSingleScalarResult();
    }

}
