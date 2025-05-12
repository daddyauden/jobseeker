<?php

namespace jobseeker\Bundle\ToolBundle\Service;

interface EncryptInterface
{

    const ENCRYPT = "sha1";
    const SALT = "Nk16b2ljbWxrSWp0cE9q0QxcDfmrX4";

    public function safeB64Encode($data);

    public function safeB64Decode($b64);

    public function encode($data, $salt = null);

    public function decode($data, $salt = null);

}
