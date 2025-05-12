<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{

    public function findClientBy($fieldName, $fieldValue, $hydration = 2)
    {

        switch ($fieldName) {
            case "clientId":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "c")
                        ->add("from", "SsoBundle:Client c")
                        ->add("where", "c.clientId = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
        }

        return $qb->getQuery()->getOneOrNullResult($hydration);
    }

}
