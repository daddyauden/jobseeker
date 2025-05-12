<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Delivery
 */
class Delivery
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $jid;

    /**
     * @var integer
     */
    private $eme;

    /**
     * @var integer
     */
    private $emr;

    /**
     * @var string
     */
    private $email;

    /**
     * @var integer
     */
    private $readed;

    /**
     * @var string
     */
    private $reserve;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $schedule;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var integer
     */
    private $ctime;

    public function __construct()
    {
        $this->readed = 0;
        $this->status = 1;
    }

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
     * Set jid
     *
     * @param integer $jid
     * @return Delivery
     */
    public function setJid($jid)
    {
        $this->jid = $jid;

        return $this;
    }

    /**
     * Get jid
     *
     * @return integer 
     */
    public function getJid()
    {
        return $this->jid;
    }

    /**
     * Set eme
     *
     * @param integer $eme
     * @return Delivery
     */
    public function setEme($eme)
    {
        $this->eme = $eme;

        return $this;
    }

    /**
     * Get eme
     *
     * @return integer 
     */
    public function getEme()
    {
        return $this->eme;
    }

    /**
     * Set emr
     *
     * @param integer $emr
     * @return Delivery
     */
    public function setEmr($emr)
    {
        $this->emr = $emr;

        return $this;
    }

    /**
     * Get emr
     *
     * @return integer 
     */
    public function getEmr()
    {
        return $this->emr;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Delivery
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set readed
     *
     * @param integer $readed
     * @return Delivery
     */
    public function setReaded($readed)
    {
        $this->readed = $readed;

        return $this;
    }

    /**
     * Get readed
     *
     * @return integer 
     */
    public function getReaded()
    {
        return $this->readed;
    }

    /**
     * Set reserve
     *
     * @param string $reserve
     * @return Delivery
     */
    public function setReserve(array $reserve = array())
    {
        $this->reserve = serialize($reserve);

        return $this;
    }

    /**
     * Get reserve
     *
     * @return string 
     */
    public function getReserve()
    {
        return unserialize($this->reserve);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Delivery
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set schedule
     *
     * @param string $schedule
     * @return Delivery
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule
     *
     * @return string 
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Set ctime
     *
     * @param integer $ctime
     * @return Delivery
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
     * @return Delivery
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
