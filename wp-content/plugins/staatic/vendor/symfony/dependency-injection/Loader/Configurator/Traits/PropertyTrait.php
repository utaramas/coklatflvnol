<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait PropertyTrait
{
    /**
     * @param string $name
     */
    public final function property($name, $value) : self
    {
        $this->definition->setProperty($name, static::processValue($value, \true));
        return $this;
    }
}
