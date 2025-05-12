<?php

namespace jobseeker\Bundle\AdminBundle\Entity;

/**
 * System
 */
class System
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $skey;

    /**
     * @var string
     */
    private $svalue;

    /**
     * @var string
     */
    private $stype;

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
     * Set skey
     *
     * @param string $skey
     * @return System
     */
    public function setSkey($skey)
    {
        $this->skey = strtolower($skey);

        return $this;
    }

    /**
     * Get skey
     *
     * @return string 
     */
    public function getSkey()
    {
        return $this->skey;
    }

    /**
     * Set svalue
     *
     * @param string $svalue
     * @return System
     */
    public function setSvalue($svalue)
    {
        $this->svalue = $svalue;
        return $this;
    }

    /**
     * Get svalue
     *
     * @return string 
     */
    public function getSvalue()
    {
        return $this->svalue;
    }

    /**
     * Set stype
     *
     * @param string $stype
     * @return System
     */
    public function setStype($stype)
    {
        $this->stype = strtolower($stype);
        return $this;
    }

    /**
     * Get stype
     *
     * @return string 
     */
    public function getStype()
    {
        return $this->stype;
    }

}
