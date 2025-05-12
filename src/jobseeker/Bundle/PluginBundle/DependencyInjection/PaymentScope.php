<?php

namespace jobseeker\Bundle\PluginBundle\DependencyInjection;

abstract class PaymentScope extends AbstractScope
{

    const SCOPE = "payment";

    public function getScope()
    {
        return $this->detectScope(self::SCOPE);
    }

}
