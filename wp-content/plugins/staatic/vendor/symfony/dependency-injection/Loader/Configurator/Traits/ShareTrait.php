<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait ShareTrait
{
    /**
     * @param bool $shared
     */
    public final function share($shared = \true) : self
    {
        $this->definition->setShared($shared);
        return $this;
    }
}
