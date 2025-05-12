<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 */
class Product
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $duration;

    /**
     * @var float
     */
    private $price;

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
     * Set duration
     *
     * @param integer $duration
     * @return Product
     */
    public function setDuration($duration)
    {
        $this->duration = $duration * 3600 * 24;

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
     * Set price
     *
     * @param float $price
     * @return Product
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
     * Set queue
     *
     * @param integer $queue
     * @return Product
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
     * @return Product
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
