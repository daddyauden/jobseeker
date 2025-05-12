<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EducationRepository extends EntityRepository
{

    public function getEduBy($by, $hydration = 2)
    {
        switch ($by[0]) {
            case "uid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "edu, dip")
                        ->add("from", "UserBundle:Education edu")
                        ->leftJoin("edu.diploma", "dip")
                        ->where("edu.uid = :uid")
                        ->add("orderBy", "edu.graduation ASC")
                        ->setParameter("uid", intval($by[1]));
                break;
            case "id":
            default :
                $qb = $this->getEntityManager()->createQueryBuilder()
                        ->add("select", "edu, dip")
                        ->add("from", "UserBundle:Education edu")
                        ->leftJoin("edu.diploma", "dip")
                        ->where("edu.id = :id")
                        ->add("orderBy", "edu.graduation ASC")
                        ->setParameter("id", intval($by[1]));
                break;
        }

        return $qb->getQuery()->getResult($hydration);
    }

}
