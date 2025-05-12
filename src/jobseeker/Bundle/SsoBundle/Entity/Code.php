<?php

namespace jobseeker\Bundle\SsoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Code
 */
class Code
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $authorizationCode;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $redirectUri;

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
     * Set authorizationCode
     *
     * @param string $authorizationCode
     * @return Code
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * Get authorizationCode
     *
     * @return string 
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Set clientId
     *
     * @param string $clientId
     * @return Code
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
     * Set redirectUri
     *
     * @param string $redirectUri
     * @return Code
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get redirectUri
     *
     * @return string 
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set expires
     *
     * @param integer $expires
     * @return Code
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
     * @return Code
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
     * @return Code
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
