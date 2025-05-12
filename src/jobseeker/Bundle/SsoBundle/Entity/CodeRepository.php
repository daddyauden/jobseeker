<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CodeRepository extends EntityRepository
{

    public function findCodeBy($fieldName, $fieldValue, $hydration = 2)
    {

        switch ($fieldName) {
            case "authorizationCode":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "c")
                        ->add("from", "SsoBundle:Code c")
                        ->add("where", "c.authorizationCode = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
        }

        return $qb->getQuery()->getOneOrNullResult($hydration);
    }

}
