<?php

namespace jobseeker\Bundle\ToolBundle\Service;

abstract class Encrypt implements EncryptInterface
{

    public function safeB64Encode($data)
    {
        return str_replace(array('+', '/', "\r", "\n", '='), array('-', '_'), base64_encode($data));
    }

    public function safeB64Decode($b64)
    {
        $b64 = str_replace(array('-', '_'), array('+', '/'), $b64);

        return base64_decode($b64);
    }

    public function encode($data, $salt = null)
    {

        $salt = $salt === null ? self::SALT : $salt;
        $b64 = $this->safeB64Encode($data) . $salt;
        $len1 = strlen($b64);
        $len2 = strlen($salt);
        $digital = "";

        for ($i = 0; $i < $len1; $i++) {
            $digital .= chr(ord($b64[$i]) ^ $len2);
        }

        return $this->safeB64Encode($digital);
    }

    public function decode($data, $salt = null)
    {
        $salt = $salt === null ? self::SALT : $salt;
        $b64 = $this->safeB64Decode($data);
        $len1 = strlen($b64);
        $len2 = strlen($salt);
        $digital = "";

        for ($i = 0; $i < $len1; $i++) {
            $digital .= chr(ord($b64[$i]) ^ $len2);
        }

        return $this->safeB64Decode(substr($digital, 0, -$len2));
    }

    abstract public function encrypt($data);

}
