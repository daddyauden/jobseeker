<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 */
class Category
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
    private $type;

    /**
     * @var string
     */
    private $csn;

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
     * @return Category
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
     * Set type
     *
     * @param string $type
     * @return Category
     */
    public function setType($type)
    {
        $this->type = strtolower($type);

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set csn
     *
     * @param string $csn
     * @return Category
     */
    public function setCsn($csn)
    {
        $this->csn = strtoupper($csn);

        return $this;
    }

    /**
     * Get csn
     *
     * @return string 
     */
    public function getCsn()
    {
        return $this->csn;
    }

    /**
     * Set queue
     *
     * @param integer $queue
     * @return Category
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
     * @return Category
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
