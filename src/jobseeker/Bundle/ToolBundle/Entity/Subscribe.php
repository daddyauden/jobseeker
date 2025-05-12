<?php

namespace jobseeker\Bundle\ToolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscribe
 */
class Subscribe
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
    private $type;

    /**
     * @var integer
     */
    private $industry;

    /**
     * @var string
     */
    private $location;

    /**
     * @var integer
     */
    private $duration;

    /**
     * @var integer
     */
    private $ctime;

    /**
     * @var integer
     */
    private $sendtime;

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
     * @return Subscribe
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
     * Set type
     *
     * @param integer $type
     * @return Subscribe
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set industry
     *
     * @param integer $industry
     * @return Subscribe
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

        return $this;
    }

    /**
     * Get industry
     *
     * @return integer 
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Subscribe
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     * @return Subscribe
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set ctime
     *
     * @param integer $ctime
     * @return Subscribe
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
    public function getTtime()
    {
        return $this->ctime;
    }

    /**
     * Set sendtime
     *
     * @param integer $sendtime
     * @return Subscribe
     */
    public function setSendtime($sendtime)
    {
        $this->sendtime = $sendtime;

        return $this;
    }

    /**
     * Get sendtime
     *
     * @return integer 
     */
    public function getSendtime()
    {
        return $this->sendtime;
    }

}
