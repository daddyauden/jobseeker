<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use jobseeker\Bundle\UserBundle\DependencyInjection\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 */
class Role implements RoleInterface
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $pid;

    public function __construct($name = null, $pid = array())
    {
        $this->name = $name === null ? RoleInterface::NORMAL : $name;
        $this->pid = $pid;
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
     * Set name
     *
     * @param string $name
     * @return Role
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
     * Set pid
     *
     * @param array $pid
     * @return Role
     */
    public function setPid($pid)
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Get pid
     *
     * @return array 
     */
    public function getPid()
    {
        return $this->pid;
    }

    public function addPrivilege($privilege)
    {
        if (!$this->hasPrivilege($privilege)) {
            $this->pid[] = (int) $privilege;
        }

        return $this;
    }

    public function removePrivilege($privilege)
    {
        if ($this->hasPrivilege($privilege)) {
            unset($this->pid[$privilege]);
        }

        return $this;
    }

    public function hasPrivilege($privilege)
    {
        return in_array((int) $privilege, $this->pid, true);
    }

    public function getPrivileges()
    {
        return $this->pid;
    }

    public function setPrivileges(array $pid)
    {
        $this->pid = $pid;

        return $this;
    }

}
