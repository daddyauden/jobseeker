<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Area
 */
class Area
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $pid;

    /**
     * @var string
     */
    private $code;

    /**
     * @var integer
     */
    private $level;

    /**
     * @var string
     */
    private $alpha;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $lat;

    /**
     * @var string
     */
    private $lng;

    /**
     * @var integer
     */
    private $queue;

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
     * Set pid
     *
     * @param integer $pid
     * @return Area
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
     * Set code
     *
     * @param string $code
     * @return Area
     */
    public function setCode($code)
    {
        $this->code = strtolower($code);

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Area
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set alpha
     *
     * @param string $alpha
     * @return Area
     */
    public function setAlpha($alpha)
    {
        $this->alpha = strtolower($alpha);

        return $this;
    }

    /**
     * Get alpha
     *
     * @return string 
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return Area
     */
    public function setDomain($domain)
    {
        $this->domain = strtolower($domain);

        return $this;
    }

    /**
     * Get domain
     *
     * @return string 
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set lat
     *
     * @param string $lat
     * @return Area
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string $lng
     * @return Area
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set queue
     *
     * @param integer $queue
     * @return Area
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Get queue
     *
     * @return integer 
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Area
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
