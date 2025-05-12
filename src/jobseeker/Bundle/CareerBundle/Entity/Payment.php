<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 */
class Payment
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $uid;

    /**
     * @var integer
     */
    private $pid;

    /**
     * @var integer
     */
    private $oid;

    /**
     * @var integer
     */
    private $paytype;

    /**
     * @var string
     */
    private $payinfo;

    /**
     * @var float
     */
    private $price;

    /**
     * @var integer
     */
    private $ctime;

    /**
     * @var integer
     */
    private $status;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uid
     *
     * @param integer $uid
     * @return Payment
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return integer 
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set pid
     *
     * @param integer $pid
     * @return Payment
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get pid
     *
     * @return integer 
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set oid
     *
     * @param integer $oid
     * @return Payment
     */
    public function setOid($oid)
    {
        $this->oid = $oid;

        return $this;
    }

    /**
     * Get oid
     *
     * @return integer 
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Set paytype
     *
     * @param integer $paytype
     * @return Payment
     */
    public function setPaytype($paytype)
    {
        $this->paytype = $paytype;

        return $this;
    }

    /**
     * Get paytype
     *
     * @return integer 
     */
    public function getPaytype()
    {
        return $this->paytype;
    }

    /**
     * Set payinfo
     *
     * @param string $payinfo
     * @return Payment
     */
    public function setPayinfo($payinfo)
    {
        $this->payinfo = $payinfo;

        return $this;
    }

    /**
     * Get payinfo
     *
     * @return string 
     */
    public function getPayinfo()
    {
        return $this->payinfo;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Payment
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set ctime
     *
     * @param integer $ctime
     * @return Payment
     */
    public function setCtime($ctime)
    {
        $this->ctime = $ctime;

        return $this;
    }

    /**
     * Get ctime
     *
     * @return integer 
     */
    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Payment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

}
