<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use jobseeker\Bundle\ToolBundle\Service\AbstractUploadEntity;

/**
 * Employee
 */
class Employee extends AbstractUploadEntity implements \Serializable
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
    private $avator;

    /**
     * @var integer
     */
    private $gender;

    /**
     * @var integer
     */
    private $marital;

    /**
     * @var date
     */
    private $birthday;

    /**
     * @var string
     */
    private $nationality;

    /**
     * @var string
     */
    private $hometown;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $mobile;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $interest;

    /**
     * @var string
     */
    private $skill;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $description;

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
     * @return Employee
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
     * @return Employee
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
     * Set avator
     *
     * @param string $avator
     * @return Employee
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
     * Set gender
     *
     * @param integer $gender
     * @return Employee
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Get marital
     *
     * @return integer 
     */
    public function getMarital()
    {
        return $this->marital;
    }

    /**
     * Set marital
     *
     * @param integer $marital
     * @return Employee
     */
    public function setMarital($marital)
    {
        $this->marital = $marital;

        return $this;
    }

    /**
     * Set birthday
     *
     * @param date $birthday
     * @return Employee
     */
    public function setBirthday($birthday)
    {
        $this->birthday = strtotime($birthday);

        return $this;
    }

    /**
     * Get birthday
     *
     * @return date 
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set nationality
     *
     * @param string $nationality
     * @return Employee
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality
     *
     * @return string 
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set hometown
     *
     * @param string $hometown
     * @return Employee
     */
    public function setHometown($hometown)
    {
        $this->hometown = $hometown;

        return $this;
    }

    /**
     * Get hometown
     *
     * @return string 
     */
    public function getHometown()
    {
        return $this->hometown;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Employee
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return Employee
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Employee
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
     * Set interest
     *
     * @param string $interest
     * @return Employee
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;

        return $this;
    }

    /**
     * Get interest
     *
     * @return string 
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * Set skill
     *
     * @param string $skill
     * @return Employee
     */
    public function setSkill($skill)
    {
        $this->skill = $skill;

        return $this;
    }

    /**
     * Get skill
     *
     * @return string 
     */
    public function getSkill()
    {
        return $this->skill;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Employee
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Employee
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

    public function serialize()
    {
        $employee = array();
        $employee["id"] = $this->getId();
        $employee["uid"] = $this->getUid();
        $employee["name"] = $this->getName();
        $employee["avator"] = $this->getAvator();
        $employee["gender"] = $this->getGender();
        $employee["marital"] = $this->getMarital();
        $employee["birthday"] = $this->getBirthday();
        $employee["nationality"] = $this->getNationality();
        $employee["hometown"] = $this->getHometown();
        $employee["location"] = $this->getLocation();
        $employee["mobile"] = $this->getMobile();
        $employee["email"] = $this->getEmail();
        $employee["interest"] = $this->getInterest();
        $employee["skill"] = $this->getSkill();
        $employee["language"] = $this->getLanguage();
        $employee["description"] = $this->getDescription();
        return $employee;
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

}
