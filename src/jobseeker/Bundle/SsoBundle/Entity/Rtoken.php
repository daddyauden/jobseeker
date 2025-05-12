<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rtoken
 */
class Rtoken
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var integer
     */
    private $expires;

    /**
     * @var integer
     */
    private $uid;

    /**
     * @var string
     */
    private $scope;

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
     * Set refreshToken
     *
     * @param string $refreshToken
     * @return Rtoken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken
     *
     * @return string 
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set clientId
     *
     * @param string $clientId
     * @return Rtoken
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return string 
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set expires
     *
     * @param integer $expires
     * @return Rtoken
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return integer 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set uid
     *
     * @param integer $uid
     * @return Rtoken
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
     * Set scope
     *
     * @param string $scope
     * @return Rtoken
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string 
     */
    public function getScope()
    {
        return $this->scope;
    }

}
