<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait AutowireTrait
{
    /**
     * @param bool $autowired
     */
    public final function autowire($autowired = \true) : self
    {
        $this->definition->setAutowired($autowired);
        return $this;
    }
}
