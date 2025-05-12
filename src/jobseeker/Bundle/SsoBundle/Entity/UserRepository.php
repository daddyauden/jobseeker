<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    public function findUserBy($fieldName, $fieldValue, $hydration = 2)
    {

        switch ($fieldName) {
            case "uid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "u")
                        ->add("from", "SsoBundle:User u")
                        ->add("where", "u.uid = :fieldValue")
                        ->setParameter("fieldValue", intval($fieldValue));
                break;
            case "email":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "u")
                        ->add("from", "SsoBundle:User u")
                        ->add("where", "u.email = :fieldValue")
                        ->setParameter("fieldValue", $fieldValue);
                break;
        }

        return $qb->getQuery()->getOneOrNullResult($hydration);
    }

}
