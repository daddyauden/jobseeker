<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

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
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var integer
     */
    private $addtime;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $salt;

    public function __construct()
    {
        $this->source = "jobseeker";
        $this->addtime = time();
        $this->status = 0;
        $this->salt = $this->generateSalt();
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
     * Set source
     *
     * @param string $source
     * @return User
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
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
    public function setPassword($password, $isEncrypted = true)
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set addtime
     *
     * @param integer $addtime
     * @return User
     */
    public function setAddtime($addtime)
    {
        $this->addtime = $addtime;

        return $this;
    }

    /**
     * Get addtime
     *
     * @return integer 
     */
    public function getAddtime()
    {
        return $this->addtime;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return User
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
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    public function generateSalt()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    public function serialize()
    {
        $user = array();
        $user["uid"] = $this->getUid();
        $user["source"] = $this->getSource();
        $user['country'] = $this->getCountry();
        $user["email"] = $this->getEmail();
        $user['addtime'] = $this->getAddtime();
        $user["status"] = $this->getStatus();
        $user["salt"] = $this->getSalt();
        return serialize($user);
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
