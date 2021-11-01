<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\DeprecateTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\PublicTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Alias;
class AliasConfigurator extends AbstractServiceConfigurator
{
    const FACTORY = 'alias';
    use DeprecateTrait;
    use PublicTrait;
    public function __construct(ServicesConfigurator $parent, Alias $alias)
    {
        $this->parent = $parent;
        $this->definition = $alias;
    }
}
