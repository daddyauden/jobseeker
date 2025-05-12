<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Atoken
 */
class Atoken
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $accessToken;

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
     * Set accessToken
     *
     * @param string $accessToken
     * @return Atoken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string 
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set clientId
     *
     * @param string $clientId
     * @return Atoken
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
     * @return Atoken
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
     * @return Atoken
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
     * @return Atoken
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
