<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\AutoconfigureTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\AutowireTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\BindTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits\PublicTrait;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
class DefaultsConfigurator extends AbstractServiceConfigurator
{
    const FACTORY = 'defaults';
    use AutoconfigureTrait;
    use AutowireTrait;
    use BindTrait;
    use PublicTrait;
    private $path;
    public function __construct(ServicesConfigurator $parent, Definition $definition, string $path = null)
    {
        parent::__construct($parent, $definition, null, []);
        $this->path = $path;
    }
    /**
     * @param string $name
     * @param mixed[] $attributes
     */
    public final function tag($name, $attributes = []) : self
    {
        if ('' === $name) {
            throw new InvalidArgumentException('The tag name in "_defaults" must be a non-empty string.');
        }
        foreach ($attributes as $attribute => $value) {
            if (null !== $value && !\is_scalar($value)) {
                throw new InvalidArgumentException(\sprintf('Tag "%s", attribute "%s" in "_defaults" must be of a scalar-type.', $name, $attribute));
            }
        }
        $this->definition->addTag($name, $attributes);
        return $this;
    }
    /**
     * @param string $fqcn
     */
    public final function instanceof($fqcn) : InstanceofConfigurator
    {
        return $this->parent->instanceof($fqcn);
    }
}
