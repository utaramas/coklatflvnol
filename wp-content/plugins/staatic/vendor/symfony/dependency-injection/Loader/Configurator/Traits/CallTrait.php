<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait CallTrait
{
    /**
     * @param string $method
     * @param mixed[] $arguments
     * @param bool $returnsClone
     */
    public final function call($method, $arguments = [], $returnsClone = \false) : self
    {
        $this->definition->addMethodCall($method, static::processValue($arguments, \true), $returnsClone);
        return $this;
    }
}
