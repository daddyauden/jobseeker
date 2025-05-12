<?php

namespace jobseeker\Bundle\UserBundle\DependencyInjection;

interface RoleInterface
{

    const NORMAL = "normal";

    public function addPrivilege($privilege);

    public function removePrivilege($privilege);

    public function hasPrivilege($privilege);

    public function getPrivileges();

    public function setPrivileges(array $privileges);

}
