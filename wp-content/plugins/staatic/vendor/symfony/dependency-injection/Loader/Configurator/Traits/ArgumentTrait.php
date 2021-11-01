<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait ArgumentTrait
{
    /**
     * @param mixed[] $arguments
     */
    public final function args($arguments) : self
    {
        $this->definition->setArguments(static::processValue($arguments, \true));
        return $this;
    }
    public final function arg($key, $value) : self
    {
        $this->definition->setArgument($key, static::processValue($value, \true));
        return $this;
    }
}
