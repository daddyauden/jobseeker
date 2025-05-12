<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Discount
 */
class Discount
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var float
     */
    private $rate;

    /**
     * @var string
     */
    private $dsn;

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
     * Set rate
     *
     * @param float $rate
     * @return Discount
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return float 
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set dsn
     *
     * @param string $dsn
     * @return Discount
     */
    public function setDsn($dsn)
    {
        $this->dsn = strtoupper($dsn);

        return $this;
    }

    /**
     * Get dsn
     *
     * @return string 
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * Set queue
     *
     * @param integer $queue
     * @return Discount
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
