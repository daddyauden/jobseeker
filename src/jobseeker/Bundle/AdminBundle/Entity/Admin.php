<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use jobseeker\Bundle\ToolBundle\Service\Encrypt;

/**
 * Admin
 */
class Admin extends Encrypt implements \Serializable
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $rid;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var integer
     */
    private $logintime;

    /**
     * @var string
     */
    private $loginip;

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
     * Set rid
     *
     * @param integer $rid
     * @return Admin
     */
    public function setRid($rid)
    {
        $this->rid = $rid;

        return $this;
    }

    /**
     * Get rid
     *
     * @return integer 
     */
    public function getRid()
    {
        return $this->rid;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Admin
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
     * Set password
     *
     * @param string $password
     * @return Admin
     */
    public function setPassword($password)
    {
        $this->password = $this->encrypt($password);

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set logintime
     *
     * @param integer $logintime
     * @return Admin
     */
    public function setLogintime($logintime)
    {
        $this->logintime = $logintime;

        return $this;
    }

    /**
     * Get logintime
     *
     * @return integer 
     */
    public function getLogintime()
    {
        return $this->logintime;
    }

    /**
     * Set loginip
     *
     * @param string $loginip
     * @return Admin
     */
    public function setLoginip($loginip)
    {
        $this->loginip = $loginip;

        return $this;
    }

    /**
     * Get loginip
     *
     * @return string 
     */
    public function getLoginip()
    {
        return $this->loginip;
    }

    public function serialize(array $data = array())
    {
        $admin = array();
        $admin['id'] = $this->getId();
        $admin['rid'] = $this->getRid()->getId();
        $admin['logintime'] = $this->getLogintime();
        $admin['loginip'] = $this->getLoginip();

        return array_merge($admin, $data);
    }

    public function unserialize($serialized)
    {
        $admin = array();
        $data = unserialize($serialized);
        list($admin['id'], $admin['rid'], $admin['logintime'], $admin['loginip']) = $data;

        return $admin;
    }

    final public function encrypt($data)
    {
        $method = self::ENCRYPT;

        return $method($data);
    }

}
