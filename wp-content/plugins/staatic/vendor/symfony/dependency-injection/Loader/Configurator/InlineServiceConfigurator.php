<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ArgumentTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\AutowireTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\BindTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\CallTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ConfiguratorTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\FactoryTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\FileTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\LazyTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ParentTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\PropertyTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\TagTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
class InlineServiceConfigurator extends AbstractConfigurator
{
    const FACTORY = 'service';
    use ArgumentTrait;
    use AutowireTrait;
    use BindTrait;
    use CallTrait;
    use ConfiguratorTrait;
    use FactoryTrait;
    use FileTrait;
    use LazyTrait;
    use ParentTrait;
    use PropertyTrait;
    use TagTrait;
    private $id = '[inline]';
    private $allowParent = \true;
    private $path = null;
    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
    }
}
