<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait FileTrait
{
    /**
     * @param string $file
     */
    public final function file($file) : self
    {
        $this->definition->setFile($file);
        return $this;
    }
}
