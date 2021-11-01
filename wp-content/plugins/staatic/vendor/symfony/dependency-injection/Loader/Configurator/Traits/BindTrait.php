<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\DefaultsConfigurator;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\InstanceofConfigurator;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
trait BindTrait
{
    /**
     * @param string $nameOrFqcn
     */
    public final function bind($nameOrFqcn, $valueOrRef) : self
    {
        $valueOrRef = static::processValue($valueOrRef, \true);
        if (!\preg_match('/^(?:(?:array|bool|float|int|string)[ \\t]*+)?\\$/', $nameOrFqcn) && !$valueOrRef instanceof Reference) {
            throw new InvalidArgumentException(\sprintf('Invalid binding for service "%s": named arguments must start with a "$", and FQCN must map to references. Neither applies to binding "%s".', $this->id, $nameOrFqcn));
        }
        $bindings = $this->definition->getBindings();
        $type = $this instanceof DefaultsConfigurator ? BoundArgument::DEFAULTS_BINDING : ($this instanceof InstanceofConfigurator ? BoundArgument::INSTANCEOF_BINDING : BoundArgument::SERVICE_BINDING);
        $bindings[$nameOrFqcn] = new BoundArgument($valueOrRef, \true, $type, $this->path ?? null);
        $this->definition->setBindings($bindings);
        return $this;
    }
}
