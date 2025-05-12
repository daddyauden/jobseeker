<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 */
class Type
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tsn;

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
     * Set tsn
     *
     * @param string $tsn
     * @return Type
     */
    public function setTsn($tsn)
    {
        $this->tsn = strtoupper($tsn);

        return $this;
    }

    /**
     * Get tsn
     *
     * @return string 
     */
    public function getTsn()
    {
        return $this->tsn;
    }

    /**
     * Set queue
     *
     * @param integer $queue
     * @return Type
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
     * @return Type
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
