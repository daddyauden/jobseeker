<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Privilege
 */
class Privilege
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $bundle;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $route;

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
     * Set bundle
     *
     * @param string $bundle
     * @return Privilege
     */
    public function setBundle($bundle)
    {
        $this->bundle = ucwords($bundle);

        return $this;
    }

    /**
     * Get bundle
     *
     * @return string 
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return Privilege
     */
    public function setController($controller)
    {
        $this->controller = ucwords($controller);

        return $this;
    }

    /**
     * Get controller
     *
     * @return string 
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Privilege
     */
    public function setAction($action)
    {
        $this->action = strtolower($action);

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set route
     *
     * @param string $route
     * @return Privilege
     */
    public function setRoute($route)
    {
        $this->route = strtolower($route);

        return $this;
    }

    /**
     * Get route
     *
     * @return string 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Privilege
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
