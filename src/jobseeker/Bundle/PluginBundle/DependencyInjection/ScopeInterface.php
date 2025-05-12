<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ScopeInterface
{

    public function setContainer(ContainerInterface $container);

    public function getConfig();

    public function resetConfig($serialized);

}
