<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use jobseeker\Bundle\ToolBundle\Service\AbstractUploadEntity;

/**
 * SendCV
 */
class SendCV extends AbstractUploadEntity implements \Serializable
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sessionid;

    /**
     * @var integer
     */
    private $jid;

    /**
     * @var integer
     */
    private $emr;

    /**
     * @var string
     */
    private $emailfrom;

    /**
     * @var string
     */
    private $emailto;

    /**
     * @var string
     */
    private $cv;

    /**
     * @var integer
     */
    private $readed;

    /**
     * @var integer
     */
    private $ctime;

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
     * Set sessionid
     *
     * @param string $sessionid
     *
     * @return SendCV
     */
    public function setSessionid($sessionid)
    {
        $this->sessionid = $sessionid;

        return $this;
    }

    /**
     * Get sessionid
     *
     * @return string
     */
    public function getSessionid()
    {
        return $this->sessionid;
    }

    /**
     * Set jid
     *
     * @param integer $jid
     *
     * @return SendCV
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
     * Set emr
     *
     * @param integer $emr
     *
     * @return SendCV
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
     * Set emailfrom
     *
     * @param string $emailfrom
     *
     * @return SendCV
     */
    public function setEmailfrom($emailfrom)
    {
        $this->emailfrom = $emailfrom;

        return $this;
    }

    /**
     * Get emailfrom
     *
     * @return string
     */
    public function getEmailfrom()
    {
        return $this->emailfrom;
    }

    /**
     * Set emailto
     *
     * @param string $emailto
     *
     * @return SendCV
     */
    public function setEmailto($emailto)
    {
        $this->emailto = $emailto;

        return $this;
    }

    /**
     * Get emailto
     *
     * @return string
     */
    public function getEmailto()
    {
        return $this->emailto;
    }

    /**
     * Set cv
     *
     * @param string $cv
     *
     * @return SendCV
     */
    public function setCv($cv)
    {
        $this->cv = $cv;

        return $this;
    }

    /**
     * Get cv
     *
     * @return string
     */
    public function getCv()
    {
        return $this->cv;
    }

    /**
     * Set readed
     *
     * @param integer $readed
     *
     * @return SendCV
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
     * Set ctime
     *
     * @param integer $ctime
     *
     * @return SendCV
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

    public function serialize(array $data = array())
    {
        $sendCV = array();
        $sendCV["id"] = $this->getId();
        $sendCV["sessionid"] = $this->getSessionid();
        $sendCV["jid"] = $this->getJid();
        $sendCV["emr"] = $this->getEmr();
        $sendCV["emailfrom"] = $this->getEmailfrom();
        $sendCV["emailto"] = $this->getEmailto();
        $sendCV["cv"] = $this->getCv();
        $sendCV["readed"] = $this->getReaded();
        $sendCV["ctime"] = $this->getCtime();
        return array_merge($sendCV, $data);
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

}
