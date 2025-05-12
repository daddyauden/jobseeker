<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Experience
 */
class Experience
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
    private $location;

    /**
     * @var string
     */
    private $company;

    /**
     * @var date
     */
    private $orientation;

    /**
     * @var date
     */
    private $dimission;

    /**
     * @var string
     */
    private $title;

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
     * @return Experience
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
     * Set location
     *
     * @param string $location
     * @return Experience
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
     * Set company
     *
     * @param string $company
     * @return Experience
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set orientation
     *
     * @param date $orientation
     * @return Experience
     */
    public function setOrientation($orientation)
    {
        $this->orientation = strtotime($orientation);

        return $this;
    }

    /**
     * Get orientation
     *
     * @return date
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set dimission
     *
     * @param date $dimission
     * @return Experience
     */
    public function setDimission($dimission)
    {
        $this->dimission = strtotime($dimission);

        return $this;
    }

    /**
     * Get dimission
     *
     * @return date
     */
    public function getDimission()
    {
        return $this->dimission;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Experience
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
     * @return Experience
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
