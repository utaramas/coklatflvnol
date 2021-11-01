<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait ClassTrait
{
    /**
     * @param string|null $class
     */
    public final function class($class) : self
    {
        $this->definition->setClass($class);
        return $this;
    }
}
