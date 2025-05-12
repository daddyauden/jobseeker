<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\Storage;

use jobseeker\Bundle\SsoBundle\DependencyInjection\UserInterface;

interface UserCredentialsInterface extends UserInterface
{

    public function checkUserCredentials($email, $password);

    public function getUserDetail($email);

    public function userExistsByUid($uid);

    public function generateUid();

    public function setUser($email, $password, $username = null, $firstName = null, $lastName = null, $country = null);

}
