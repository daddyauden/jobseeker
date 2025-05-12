<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AtokenRepository extends EntityRepository
{

    public function findAtokenBy($fieldName, $fieldValue, $hydration = 2)
    {

        switch ($fieldName) {
            case "accessToken":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "a")
                        ->add("from", "SsoBundle:Atoken a")
                        ->add("where", "a.accessToken = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
        }

        return $qb->getQuery()->getOneOrNullResult($hydration);
    }

}
