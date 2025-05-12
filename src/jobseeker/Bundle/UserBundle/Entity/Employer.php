<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use jobseeker\Bundle\ToolBundle\Service\AbstractUploadEntity;

/**
 * Employer
 */
class Employer extends AbstractUploadEntity implements \Serializable
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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $abbr;

    /**
     * @var string
     */
    private $avator;

    /**
     * @var integer
     */
    private $location;

    /**
     * @var string
     */
    private $address;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var integer
     */
    private $scale;

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
    private $fax;

    /**
     * @var string
     */
    private $site;

    /**
     * @var string
     */
    private $about;

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
     * @return Employer
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
     * Set name
     *
     * @param string $name
     * @return Employer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set abbr
     *
     * @param string $abbr
     * @return Employer
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    /**
     * Get abbr
     *
     * @return string 
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    /**
     * Set avator
     *
     * @param string $avator
     * @return Employer
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

    /**
     * Set location
     *
     * @param integer $location
     * @return Employer
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return integer 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Employer
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Employer
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
     * Set scale
     *
     * @param integer $scale
     * @return Employer
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get scale
     *
     * @return integer 
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Set contacter
     *
     * @param string $contacter
     * @return Employer
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
     * @return Employer
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
     * @return Employer
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
     * Set fax
     *
     * @param string $fax
     * @return Employer
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set site
     *
     * @param string $site
     * @return Employer
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string 
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set about
     *
     * @param string $about
     * @return Employer
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

    public function serialize()
    {
        $employer = array();
        $employer["id"] = $this->getId();
        $employer["uid"] = $this->getUid();
        $employer["name"] = $this->getName();
        $employer["attr"] = $this->getAbbr();
        $employer["avator"] = $this->getAvator();
        $employer["location"] = $this->getLocation()->getId();
        $employer["address"] = $this->getAddress();
        $employer["type"] = $this->getType()->getId();
        $employer["scale"] = $this->getScale()->getId();
        $employer["contacter"] = $this->getContacter();
        $employer["contacteremail"] = $this->getContacteremail();
        $employer["contactertel"] = $this->getContactertel();
        $employer["fax"] = $this->getFax();
        $employer["site"] = $this->getSite();
        $employer["about"] = $this->getAbout();
        return $employer;
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

}
