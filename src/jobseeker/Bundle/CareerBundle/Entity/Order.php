<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Order
 */
class Order
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
    private $oid;

    /**
     * @var string
     */
    private $jid;

    /**
     * @var integer
     */
    private $ctime;

    /**
     * @var integer
     */
    private $mtime;

    /**
     * @var float
     */
    private $price;

    /**
     * @var integer
     */
    private $did;

    /**
     * @var float
     */
    private $realprice;

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
     * @return Order
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
     * Set oid
     *
     * @param integer $oid
     * @return Order
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
     * Set jid
     *
     * @param string $jid
     * @return Order
     */
    public function setJid($jid)
    {
        $this->jid = $jid;

        return $this;
    }

    /**
     * Get jid
     *
     * @return string 
     */
    public function getJid()
    {
        return $this->jid;
    }

    /**
     * Set ctime
     *
     * @param integer $ctime
     * @return Order
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
     * Set mtime
     *
     * @param integer $mtime
     * @return Order
     */
    public function setMtime($mtime)
    {
        $this->mtime = $mtime;

        return $this;
    }

    /**
     * Get mtime
     *
     * @return integer 
     */
    public function getMtime()
    {
        return $this->mtime;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Order
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
     * Set did
     *
     * @param integer $did
     * @return Order
     */
    public function setDid($did)
    {
        $this->did = $did;

        return $this;
    }

    /**
     * Get did
     *
     * @return integer 
     */
    public function getDid()
    {
        return $this->did;
    }

    /**
     * Set realprice
     *
     * @param float $realprice
     * @return Order
     */
    public function setRealprice($realprice)
    {
        $this->realprice = $realprice;

        return $this;
    }

    /**
     * Get realprice
     *
     * @return float 
     */
    public function getRealprice()
    {
        return $this->realprice;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Order
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
