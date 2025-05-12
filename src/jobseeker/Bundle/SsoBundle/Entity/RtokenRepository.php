<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RtokenRepository extends EntityRepository
{

    public function findRtokenBy($fieldName, $fieldValue, $hydration = 2)
    {

        switch ($fieldName) {
            case "refreshToken":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "r")
                        ->add("from", "SsoBundle:Rtoken r")
                        ->add("where", "r.refreshToken = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
        }

        return $qb->getQuery()->getOneOrNullResult($hydration);
    }

}
