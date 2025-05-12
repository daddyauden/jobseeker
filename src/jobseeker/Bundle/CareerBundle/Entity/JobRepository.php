<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Comparison;

class JobRepository extends EntityRepository
{

    public function getTotal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "count(j.id)")
            ->add("from", "CareerBundle:Job j");

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getTotalByUid($uid)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "count(j.id)")
            ->add("from", "CareerBundle:Job j")
            ->where("j.uid = :uid")
            ->setParameter("uid", intval($uid));

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAllForPagerByUid($uid, $page = 1, $offset = 10, $hydration = 2)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->add("select", "j.id, j.avator, j.title, j.begintime, j.endtime, j.status")
            ->add("from", "CareerBundle:Job j")
            ->where("j.uid = :uid")
            ->add("orderBy", "j.ctime DESC, j.begintime ASC")
            ->setFirstResult(($page - 1) * $offset)
            ->setMaxResults($offset)
            ->setParameter("uid", intval($uid));

        return $qb->getQuery()->getResult($hydration);
    }

    public function getAllForPager(array $condition = array(), $total = false, $page = 1, $offset = 10, $hydration = 2)
    {
        $count = null;

        $qb = $this->getEntityManager()->createQueryBuilder();

        $conditionArr = array();

        $conditionStr = "";

        foreach ($condition as $columm => $value) {
            $columm = strtolower($columm);
            if (is_array($value) && count($value) > 0) {
                $conditionArr[] = $qb->expr()->in("j." . $columm, $value);
            } else if (stripos($value, "-") > 0) {
                $tmp = explode("-", $value);
                $conditionArr[] = $qb->expr()->between("j." . $columm, intval($tmp[0]), intval($tmp[1]));
            } else if (stripos($value, Comparison::LTE) === 0) {
                $tmp = intval(substr($value, strlen(Comparison::LTE)));
                $tmp = $columm === "begintime" ? time() - $tmp : $tmp;
                $conditionArr[] = $qb->expr()->gt("j." . $columm, $tmp);
            } else if (stripos($value, Comparison::LT) === 0) {
                $tmp = intval(substr($value, strlen(Comparison::LT)));
                $tmp = $columm === "begintime" ? time() + $tmp : $tmp;
                $conditionArr[] = $qb->expr()->lt("j." . $columm, $tmp);
            } else if (stripos($value, Comparison::GTE) === 0) {
                $tmp = intval(substr($value, strlen(Comparison::GTE)));
                $tmp = $columm === "begintime" ? time() - $tmp : $tmp;
                $conditionArr[] = $qb->expr()->gte("j." . $columm, $tmp);
            } else if (stripos($value, Comparison::GT) === 0) {
                $tmp = intval(substr($value, strlen(Comparison::GT)));
                $tmp = $columm === "begintime" ? time() - $tmp : $tmp;
                $conditionArr[] = $qb->expr()->gt("j." . $columm, $tmp);
            } else {
                $conditionArr[] = $qb->expr()->eq("j." . $columm, $value);
            }
        }

        if (count($conditionArr) > 1) {
            $conditionStr = implode(" and ", $conditionArr);
        } else {
            $conditionStr = "";
        }

        if ($total === true) {
            $count = $qb->add("select", "count(j)")->add("from", "CareerBundle:Job j")->add("where", $conditionStr)->getQuery()->getSingleScalarResult();
        }

        $result = $qb->add("select", "j.id, j.avator, j.title, j.salary, emr.avator as emr_avator, emr.abbr, emr.name, t.tsn, a1.code, a2.code as pcode")
            ->add("from", "CareerBundle:Job j")
            ->leftJoin("UserBundle:Employer", "emr", "WITH", "j.uid = emr.uid")
            ->leftJoin("CareerBundle:Type", "t", "WITH", "j.type = t.id")
            ->leftJoin("AdminBundle:Area", "a1", "WITH", "j.area = a1.id")
            ->leftJoin("AdminBundle:Area", "a2", "INNER", "a1.pid = a2.id")
            ->add("where", $conditionStr)
            ->setFirstResult(max(0, $page - 1) * $offset)
            ->setMaxResults($offset)
            ->getQuery()->getResult($hydration);

        if ($result !== null && $count !== null) {
            $result[] = $count;
        }


        return $result;
    }

    public function getJobBy($by, $hydration = 2)
    {
        switch ($by[0]) {
            case "jid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->add("select", "j, p, i, t, a1, a2")
                    ->add("from", "CareerBundle:Job j")
                    ->leftJoin("CareerBundle:Product", "p", "WITH", "j.product = p.id")
                    ->leftJoin("CareerBundle:Industry", "i", "WITH", "j.industry = i.id")
                    ->leftJoin("CareerBundle:Type", "t", "WITH", "j.type = t.id")
                    ->leftJoin("AdminBundle:Area", "a1", "WITH", "j.area = a1.id")
                    ->leftJoin("AdminBundle:Area", "a2", "INNER", "a1.pid = a2.id")
                    ->where("j.jid = :jid")
                    ->setParameter("jid", $by[1]);
                break;
            case "uid":
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->add("select", "j, p, i, t, a1, a2")
                    ->add("from", "CareerBundle:Job j")
                    ->leftJoin("CareerBundle:Product", "p", "WITH", "j.product = p.id")
                    ->leftJoin("CareerBundle:Industry", "i", "WITH", "j.industry = i.id")
                    ->leftJoin("CareerBundle:Type", "t", "WITH", "j.type = t.id")
                    ->leftJoin("AdminBundle:Area", "a1", "WITH", "j.area = a1.id")
                    ->leftJoin("AdminBundle:Area", "a2", "INNER", "a1.pid = a2.id")
                    ->where("j.uid = :uid")
                    ->setParameter("uid", $by[1]);
                break;
            case "id":
            default :
                $qb = $this->getEntityManager()->createQueryBuilder()
                    ->add("select", "j, p, i, t, a1, a2")
                    ->add("from", "CareerBundle:Job j")
                    ->leftJoin("CareerBundle:Product", "p", "WITH", "j.product = p.id")
                    ->leftJoin("CareerBundle:Industry", "i", "WITH", "j.industry = i.id")
                    ->leftJoin("CareerBundle:Type", "t", "WITH", "j.type = t.id")
                    ->leftJoin("AdminBundle:Area", "a1", "WITH", "j.area = a1.id")
                    ->leftJoin("AdminBundle:Area", "a2", "INNER", "a1.pid = a2.id")
                    ->where("j.id = :id")
                    ->setParameter("id", $by[1]);
                break;
        }

        $result = $qb->getQuery()->getResult($hydration);

        if (isset($result[0])) {
            if (isset($result[1]) && count($result[1]) > 0) {
                $result[0]['product'] = $result[1];
            }

            if (isset($result[2]) && count($result[2]) > 0) {
                $result[0]['industry'] = $result[2];
            }

            if (isset($result[3]) && count($result[3]) > 0) {
                $result[0]['type'] = $result[3];
            }

            if (isset($result[4]) && count($result[4]) > 0) {
                $result[0]['area'] = $result[4];
            }

            if (isset($result[5]) && count($result[5]) > 0) {
                $result[0]['area']['pid'] = $result[5];
            }

            return $result[0];
        } else {
            return $result;
        }
    }

}
