<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait AbstractTrait
{
    /**
     * @param bool $abstract
     */
    public final function abstract($abstract = \true) : self
    {
        $this->definition->setAbstract($abstract);
        return $this;
    }
}
