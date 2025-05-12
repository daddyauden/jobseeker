<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Education
 */
class Education
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
    private $university;

    /**
     * @var date
     */
    private $graduation;

    /**
     * @var string
     */
    private $diploma;

    /**
     * @var string
     */
    private $major;

    /**
     * @var string
     */
    private $course;

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
     * @return Education
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
     * Set university
     *
     * @param string $university
     * @return Education
     */
    public function setUniversity($university)
    {
        $this->university = $university;

        return $this;
    }

    /**
     * Get university
     *
     * @return string
     */
    public function getUniversity()
    {
        return $this->university;
    }

    /**
     * Set graduation
     *
     * @param date $graduation
     * @return Education
     */
    public function setGraduation($graduation)
    {
        $this->graduation = strtotime($graduation);

        return $this;
    }

    /**
     * Get graduation
     *
     * @return date
     */
    public function getGraduation()
    {
        return $this->graduation;
    }

    /**
     * Set diploma
     *
     * @param string $diploma
     * @return Education
     */
    public function setDiploma($diploma)
    {
        $this->diploma = $diploma;

        return $this;
    }

    /**
     * Get diploma
     *
     * @return string
     */
    public function getDiploma()
    {
        return $this->diploma;
    }

    /**
     * Set major
     *
     * @param string $major
     * @return Education
     */
    public function setMajor($major)
    {
        $this->major = $major;

        return $this;
    }

    /**
     * Get major
     *
     * @return string
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * Set course
     *
     * @param string $course
     * @return Education
     */
    public function setCourse($course)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return string
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Education
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

}
