<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait LazyTrait
{
    public final function lazy($lazy = \true) : self
    {
        $this->definition->setLazy((bool) $lazy);
        if (\is_string($lazy)) {
            $this->definition->addTag('proxy', ['interface' => $lazy]);
        }
        return $this;
    }
}
