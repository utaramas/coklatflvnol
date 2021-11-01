<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Parameter;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
use Staatic\Vendor\Symfony\Component\ExpressionLanguage\Expression;
abstract class AbstractConfigurator
{
    const FACTORY = 'unknown';
    public static $valuePreProcessor;
    protected $definition;
    public function __call(string $method, array $args)
    {
        if (\method_exists($this, 'set' . $method)) {
            return $this->{'set' . $method}(...$args);
        }
        throw new \BadMethodCallException(\sprintf('Call to undefined method "%s::%s()".', static::class, $method));
    }
    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize ' . __CLASS__);
    }
    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }
    public static function processValue($value, $allowServices = \false)
    {
        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::processValue($v, $allowServices);
            }
            return self::$valuePreProcessor ? (self::$valuePreProcessor)($value, $allowServices) : $value;
        }
        if (self::$valuePreProcessor) {
            $value = (self::$valuePreProcessor)($value, $allowServices);
        }
        if ($value instanceof ReferenceConfigurator) {
            return new Reference($value->id, $value->invalidBehavior);
        }
        if ($value instanceof InlineServiceConfigurator) {
            $def = $value->definition;
            $value->definition = null;
            return $def;
        }
        if ($value instanceof self) {
            throw new InvalidArgumentException(\sprintf('"%s()" can be used only at the root of service configuration files.', $value::FACTORY));
        }
        switch (\true) {
            case null === $value:
            case \is_scalar($value):
                return $value;
            case $value instanceof ArgumentInterface:
            case $value instanceof Definition:
            case $value instanceof Expression:
            case $value instanceof Parameter:
            case $value instanceof AbstractArgument:
            case $value instanceof Reference:
                if ($allowServices) {
                    return $value;
                }
        }
        throw new InvalidArgumentException(\sprintf('Cannot use values of type "%s" in service configuration files.', \get_debug_type($value)));
    }
}
