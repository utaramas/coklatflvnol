<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait ConfiguratorTrait
{
    public final function configurator($configurator) : self
    {
        $this->definition->setConfigurator(static::processValue($configurator, \true));
        return $this;
    }
}
