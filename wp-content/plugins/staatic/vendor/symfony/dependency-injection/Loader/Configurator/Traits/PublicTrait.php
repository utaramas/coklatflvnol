<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait PublicTrait
{
    public final function public() : self
    {
        $this->definition->setPublic(\true);
        return $this;
    }
    public final function private() : self
    {
        $this->definition->setPublic(\false);
        return $this;
    }
}
