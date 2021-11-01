<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\AbstractTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ArgumentTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\AutoconfigureTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\AutowireTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\BindTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\CallTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ConfiguratorTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\DeprecateTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\FactoryTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\LazyTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ParentTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\PropertyTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\PublicTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ShareTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\TagTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
class PrototypeConfigurator extends AbstractServiceConfigurator
{
    const FACTORY = 'load';
    use AbstractTrait;
    use ArgumentTrait;
    use AutoconfigureTrait;
    use AutowireTrait;
    use BindTrait;
    use CallTrait;
    use ConfiguratorTrait;
    use DeprecateTrait;
    use FactoryTrait;
    use LazyTrait;
    use ParentTrait;
    use PropertyTrait;
    use PublicTrait;
    use ShareTrait;
    use TagTrait;
    private $loader;
    private $resource;
    private $excludes;
    private $allowParent;
    public function __construct(ServicesConfigurator $parent, PhpFileLoader $loader, Definition $defaults, string $namespace, string $resource, bool $allowParent)
    {
        $definition = new Definition();
        if (!$defaults->isPublic() || !$defaults->isPrivate()) {
            $definition->setPublic($defaults->isPublic());
        }
        $definition->setAutowired($defaults->isAutowired());
        $definition->setAutoconfigured($defaults->isAutoconfigured());
        $definition->setBindings(\unserialize(\serialize($defaults->getBindings())));
        $definition->setChanges([]);
        $this->loader = $loader;
        $this->resource = $resource;
        $this->allowParent = $allowParent;
        parent::__construct($parent, $definition, $namespace, $defaults->getTags());
    }
    public function __destruct()
    {
        parent::__destruct();
        if ($this->loader) {
            $this->loader->registerClasses($this->definition, $this->id, $this->resource, $this->excludes);
        }
        $this->loader = null;
    }
    public final function exclude($excludes) : self
    {
        $this->excludes = (array) $excludes;
        return $this;
    }
}
