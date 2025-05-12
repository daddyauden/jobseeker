<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DeliveryRepository extends EntityRepository
{

    public function getAllForPagerByEme($eme, $readed = 0, $page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "d.id as did, d.reserve, d.schedule, d.ctime, j.id as jid, j.title, j.salary, eme.uid as emeuid, emr.uid as emruid, emr.name, emr.avator, type.tsn")
                ->add("from", "CareerBundle:Delivery d")
                ->leftJoin("d.jid", "j")
                ->leftJoin("d.eme", "eme")
                ->leftJoin("d.emr", "emr")
                ->leftJoin("CareerBundle:Type", "type", "WITH", "j.type = type.id")
                ->add("orderBy", "d.reserve DESC, d.ctime DESC")
                ->add("where", "d.eme = :eme and d.readed = :readed")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset)
                ->setParameter("eme", intval($eme))
                ->setParameter("readed", intval($readed));

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAllForPagerByEmr($emr, $readed = 0, $page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "d,j,eme,emr")
                ->add("from", "CareerBundle:Delivery d")
                ->leftJoin("d.jid", "j")
                ->leftJoin("d.eme", "eme")
                ->leftJoin("d.emr", "emr")
                ->add("orderBy", "d.reserve DESC, d.ctime DESC")
                ->add("where", "d.emr = :emr and d.readed = :readed")
                ->setFirstResult(($page - 1) * $offset)
                ->setMaxResults($offset)
                ->setParameter("emr", intval($emr))
                ->setParameter("readed", intval($readed));

        return $qb->getQuery()->getResult($hydration);
    }

    public function getById($id, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "d")
                ->add("from", "CareerBundle:Delivery d")
                ->where("d.id = :id")
                ->setParameter("id", intval($id));

        return $qb->getQuery()->getOneOrNullResult($hydration);
    }

    public function getTotalByEme($eme, $readed = 0)
    {
        return $this->getTotalBy("eme", $eme, $readed);
    }

    public function getTotalByEmr($emr, $readed = 0)
    {
        return $this->getTotalBy("emr", $emr, $readed);
    }

    private function getTotalBy($name, $value, $readed)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
                ->add("select", "count(d.id)")
                ->add("from", "CareerBundle:Delivery d")
                ->add("where", "d." . $name . " = :value and d.readed = :readed")
                ->setParameter("value", intval($value))
                ->setParameter("readed", intval($readed));

        return $qb->getQuery()->getSingleScalarResult();
    }

}
