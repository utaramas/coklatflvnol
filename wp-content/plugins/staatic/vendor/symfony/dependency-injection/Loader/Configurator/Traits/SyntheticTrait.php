<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait SyntheticTrait
{
    /**
     * @param bool $synthetic
     */
    public final function synthetic($synthetic = \true) : self
    {
        $this->definition->setSynthetic($synthetic);
        return $this;
    }
}
