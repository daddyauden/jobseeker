<?php

namespace jobseeker\Bundle\CareerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use jobseeker\Bundle\ToolBundle\Service\AbstractUploadEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * Job
 */
class Job extends AbstractUploadEntity implements \Serializable
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
    private $jid;

    /**
     * @var integer
     */
    private $ctime;

    /**
     * @var integer
     */
    private $product;

    /**
     * @var integer
     */
    private $industry;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var float
     */
    private $salary;

    /**
     * @var integer
     */
    private $area;

    /**
     * @var integer
     */
    private $begintime;

    /**
     * @var integer
     */
    private $endtime;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $contacter;

    /**
     * @var string
     */
    private $contacteremail;

    /**
     * @var string
     */
    private $contactertel;

    /**
     * @var string
     */
    private $about;

    /**
     * @var string
     */
    private $verify;

    /**
     * @var string
     */
    private $avator;

    public function __construct()
    {
        $this->jid = $this->generateJid();
        $this->status = 0;
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
     * Set uid
     *
     * @param integer $uid
     * @return Job
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
     * Set jid
     *
     * @param integer $jid
     * @return Job
     */
    public function setJid()
    {
        $this->jid = $this->generateJid();

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
     * Set ctime
     *
     * @param integer $ctime
     * @return Job
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
     * Set product
     *
     * @param integer $product
     * @return Job
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return integer
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set job industry
     *
     * @param integer $industry
     * @return Job
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

        return $this;
    }

    /**
     * Get job industry
     *
     * @return integer
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * Set job category
     *
     * @param integer $type
     * @return Job
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get job category
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set salary
     *
     * @param float $salary
     * @return Job
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;

        return $this;
    }

    /**
     * Get salary
     *
     * @return float
     */
    public function getSalary()
    {
        return $this->salary;
    }

    /**
     * Set area
     *
     * @param integer $area
     * @return Job
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return integer
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set begintime
     *
     * @param integer $begintime
     * @return Job
     */
    public function setBegintime($begintime)
    {
        $this->begintime = strtotime($begintime);

        return $this;
    }

    /**
     * Get begintime
     *
     * @return integer
     */
    public function getBegintime()
    {
        return $this->begintime;
    }

    /**
     * Set endtime
     *
     * @param integer $endtime
     * @return Job
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;

        return $this;
    }

    /**
     * Get endtime
     *
     * @return integer
     */
    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Job
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Job
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Job
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

    /**
     * Set contacter
     *
     * @param string $contacter
     * @return Job
     */
    public function setContacter($contacter)
    {
        $this->contacter = $contacter;

        return $this;
    }

    /**
     * Get contacter
     *
     * @return string
     */
    public function getContacter()
    {
        return $this->contacter;
    }

    /**
     * Set contacteremail
     *
     * @param string $contacteremail
     * @return Job
     */
    public function setContacteremail($contacteremail)
    {
        $this->contacteremail = $contacteremail;

        return $this;
    }

    /**
     * Get contacteremail
     *
     * @return string
     */
    public function getContacteremail()
    {
        return $this->contacteremail;
    }

    /**
     * Set contactertel
     *
     * @param string $contactertel
     * @return Job
     */
    public function setContactertel($contactertel)
    {
        $this->contactertel = $contactertel;

        return $this;
    }

    /**
     * Get contactertel
     *
     * @return string
     */
    public function getContactertel()
    {
        return $this->contactertel;
    }

    /**
     * Set about
     *
     * @param string $about
     * @return Job
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set verify
     *
     * @param string $verify
     * @return Job
     */
    public function setVerify()
    {
        $uuid4 = Uuid::uuid4();
        $this->verify = $uuid4->toString();

        return $this;
    }

    /**
     * Get verify
     *
     * @return string
     */
    public function getVerify()
    {
        return $this->verify;
    }

    /**
     * Set avator
     *
     * @param string $avator
     * @return Job
     */
    public function setAvator($avator)
    {
        $this->avator = $avator;

        return $this;
    }

    /**
     * Get avator
     *
     * @return string
     */
    public function getAvator()
    {
        return $this->avator;
    }

    private function generateJid()
    {
        $nstr = '';
        $str = sha1(uniqid(mt_rand(), true));
        for ($i = 0, $len = strlen($str); $i < $len; $i += 1) {
            $nstr .= hexdec($str[$i]);
        }

        return intval(substr($nstr, 0, 10));
    }

    public function serialize()
    {
        $job = array();
        $job["id"] = $this->getId();
        $job['uid'] = $this->getUid();
        $job["jid"] = $this->getJid();
        $job["ctime"] = $this->getCtime();
        $job["product"] = $this->getProduct();
        $job["industry"] = $this->getIndustry();
        $job["type"] = $this->getType();
        $job["salary"] = $this->getSalary();
        $job["area"] = $this->getArea();
        $job["begintime"] = $this->getBegintime();
        $job["endtime"] = $this->getEndtime();
        $job["title"] = $this->getTitle();
        $job["description"] = $this->getDescription();
        $job["status"] = $this->getStatus();
        $job["contacter"] = $this->getContacter();
        $job["contacteremail"] = $this->getContacteremail();
        $job["contactertel"] = $this->getContactertel();
        $job["about"] = $this->getAbout();
        return $job;
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

}
