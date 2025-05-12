<?php

namespace jobseeker\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use jobseeker\Bundle\ToolBundle\Service\Encrypt;

/**
 * User
 */
class User extends Encrypt implements \Serializable
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
     * Set uid
     *
     * @param integer $uid
     * @return User
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
     * Set rid
     *
     * @param integer $rid
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setPassword($password, $isEncrypted = false)
    {
        if ($isEncrypted === true) {
            $this->password = $password;
        } else {
            $this->password = $this->encrypt($password);
        }

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
     * @return User
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
     * @return User
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
        $user = array();
        $user["uid"] = $this->getUid();
        $user["rid"] = $this->getRid()->getId();
        $user["logintime"] = $this->getLogintime();
        $user["loginip"] = $this->getLoginip();
        return array_merge($user, $data);
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    final public function encrypt($data)
    {
        $method = self::ENCRYPT;

        return $method($data);
    }

}
