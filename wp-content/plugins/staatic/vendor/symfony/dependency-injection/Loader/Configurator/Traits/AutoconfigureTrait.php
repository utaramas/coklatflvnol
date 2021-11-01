<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait AutoconfigureTrait
{
    /**
     * @param bool $autoconfigured
     */
    public final function autoconfigure($autoconfigured = \true) : self
    {
        $this->definition->setAutoconfigured($autoconfigured);
        return $this;
    }
}
