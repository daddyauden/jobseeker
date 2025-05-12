<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ExperienceRepository extends EntityRepository
{

    public function getExpBy($by, $hydration = 2)
    {
        switch ($by[0]) {
            case "uid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "exp")
                        ->add("from", "UserBundle:Experience exp")
                        ->where("exp.uid = :uid")
                        ->add("orderBy", "exp.orientation DESC")
                        ->setParameter("uid", intval($by[1]));
                break;
            case "id":
            default :
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "exp")
                        ->add("from", "UserBundle:Experience exp")
                        ->where("exp.id = :id")
                        ->add("orderBy", "exp.orientation DESC")
                        ->setParameter("id", intval($by[1]));
                break;
        }

        return $qb->getQuery()->getResult($hydration);
    }

}
