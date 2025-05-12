<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

interface PluginInterface
{

    public function getName();

    public function getLogo();

    public function getDescription();

    public function getConfigForInstall();

}
