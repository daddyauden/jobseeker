<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Industry
 */
class Industry
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
    private $isn;

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
     * @return Industry
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
     * Set isn
     *
     * @param string $isn
     * @return Industry
     */
    public function setIsn($isn)
    {
        $this->isn = strtoupper($isn);

        return $this;
    }

    /**
     * Get isn
     *
     * @return string 
     */
    public function getIsn()
    {
        return $this->isn;
    }

    /**
     * Set queue
     *
     * @param integer $queue
     * @return Industry
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
     * @return Industry
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
