<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\Config\Resource\ClassExistenceResource;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper;
use Staatic\Vendor\Symfony\Component\DependencyInjection\TypedReference;
class AutowirePass extends AbstractRecursivePass
{
    private $types;
    private $ambiguousServiceTypes;
    private $lastFailure;
    private $throwOnAutowiringException;
    private $decoratedClass;
    private $decoratedId;
    private $methodCalls;
    private $getPreviousValue;
    private $decoratedMethodIndex;
    private $decoratedMethodArgumentIndex;
    private $typesClone;
    public function __construct(bool $throwOnAutowireException = \true)
    {
        $this->throwOnAutowiringException = $throwOnAutowireException;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        try {
            $this->typesClone = clone $this;
            parent::process($container);
        } finally {
            $this->decoratedClass = null;
            $this->decoratedId = null;
            $this->methodCalls = null;
            $this->getPreviousValue = null;
            $this->decoratedMethodIndex = null;
            $this->decoratedMethodArgumentIndex = null;
            $this->typesClone = null;
        }
    }
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        try {
            return $this->doProcessValue($value, $isRoot);
        } catch (AutowiringFailedException $e) {
            if ($this->throwOnAutowiringException) {
                throw $e;
            }
            $this->container->getDefinition($this->currentId)->addError($e->getMessageCallback() ?? $e->getMessage());
            return parent::processValue($value, $isRoot);
        }
    }
    private function doProcessValue($value, bool $isRoot = \false)
    {
        if ($value instanceof TypedReference) {
            if ($ref = $this->getAutowiredReference($value)) {
                return $ref;
            }
            if (ContainerBuilder::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE === $value->getInvalidBehavior()) {
                $message = $this->createTypeNotFoundMessageCallback($value, 'it');
                $this->container->register($id = \sprintf('.errored.%s.%s', $this->currentId, (string) $value), $value->getType())->addError($message);
                return new TypedReference($id, $value->getType(), $value->getInvalidBehavior(), $value->getName());
            }
        }
        $value = parent::processValue($value, $isRoot);
        if (!$value instanceof Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
            return $value;
        }
        if (!($reflectionClass = $this->container->getReflectionClass($value->getClass(), \false))) {
            $this->container->log($this, \sprintf('Skipping service "%s": Class or interface "%s" cannot be loaded.', $this->currentId, $value->getClass()));
            return $value;
        }
        $this->methodCalls = $value->getMethodCalls();
        try {
            $constructor = $this->getConstructor($value, \false);
        } catch (RuntimeException $e) {
            throw new AutowiringFailedException($this->currentId, $e->getMessage(), 0, $e);
        }
        if ($constructor) {
            \array_unshift($this->methodCalls, [$constructor, $value->getArguments()]);
        }
        $this->methodCalls = $this->autowireCalls($reflectionClass, $isRoot);
        if ($constructor) {
            list(, $arguments) = \array_shift($this->methodCalls);
            if ($arguments !== $value->getArguments()) {
                $value->setArguments($arguments);
            }
        }
        if ($this->methodCalls !== $value->getMethodCalls()) {
            $value->setMethodCalls($this->methodCalls);
        }
        return $value;
    }
    private function autowireCalls(\ReflectionClass $reflectionClass, bool $isRoot) : array
    {
        $this->decoratedId = null;
        $this->decoratedClass = null;
        $this->getPreviousValue = null;
        if ($isRoot && ($definition = $this->container->getDefinition($this->currentId)) && null !== ($this->decoratedId = $definition->innerServiceId) && $this->container->has($this->decoratedId)) {
            $this->decoratedClass = $this->container->findDefinition($this->decoratedId)->getClass();
        }
        foreach ($this->methodCalls as $i => $call) {
            $this->decoratedMethodIndex = $i;
            list($method, $arguments) = $call;
            if ($method instanceof \ReflectionFunctionAbstract) {
                $reflectionMethod = $method;
            } else {
                $definition = new Definition($reflectionClass->name);
                try {
                    $reflectionMethod = $this->getReflectionMethod($definition, $method);
                } catch (RuntimeException $e) {
                    if ($definition->getFactory()) {
                        continue;
                    }
                    throw $e;
                }
            }
            $arguments = $this->autowireMethod($reflectionMethod, $arguments);
            if ($arguments !== $call[1]) {
                $this->methodCalls[$i][1] = $arguments;
            }
        }
        return $this->methodCalls;
    }
    private function autowireMethod(\ReflectionFunctionAbstract $reflectionMethod, array $arguments) : array
    {
        $class = $reflectionMethod instanceof \ReflectionMethod ? $reflectionMethod->class : $this->currentId;
        $method = $reflectionMethod->name;
        $parameters = $reflectionMethod->getParameters();
        if ($reflectionMethod->isVariadic()) {
            \array_pop($parameters);
        }
        foreach ($parameters as $index => $parameter) {
            if (\array_key_exists($index, $arguments) && '' !== $arguments[$index]) {
                continue;
            }
            $type = ProxyHelper::getTypeHint($reflectionMethod, $parameter, \true);
            if (!$type) {
                if (isset($arguments[$index])) {
                    continue;
                }
                if (!$parameter->isDefaultValueAvailable()) {
                    if ($parameter->isOptional()) {
                        continue;
                    }
                    $type = ProxyHelper::getTypeHint($reflectionMethod, $parameter, \false);
                    $type = $type ? \sprintf('is type-hinted "%s"', \ltrim($type, '\\')) : 'has no type-hint';
                    throw new AutowiringFailedException($this->currentId, \sprintf('Cannot autowire service "%s": argument "$%s" of method "%s()" %s, you should configure its value explicitly.', $this->currentId, $parameter->name, $class !== $this->currentId ? $class . '::' . $method : $method, $type));
                }
                $arguments[$index] = $parameter->getDefaultValue();
                continue;
            }
            $getValue = function () use($type, $parameter, $class, $method) {
                if (!($value = $this->getAutowiredReference($ref = new TypedReference($type, $type, ContainerBuilder::EXCEPTION_ON_INVALID_REFERENCE, $parameter->name)))) {
                    $failureMessage = $this->createTypeNotFoundMessageCallback($ref, \sprintf('argument "$%s" of method "%s()"', $parameter->name, $class !== $this->currentId ? $class . '::' . $method : $method));
                    if ($parameter->isDefaultValueAvailable()) {
                        $value = $parameter->getDefaultValue();
                    } elseif (!$parameter->allowsNull()) {
                        throw new AutowiringFailedException($this->currentId, $failureMessage);
                    }
                }
                return $value;
            };
            if ($this->decoratedClass && ($isDecorated = \is_a($this->decoratedClass, $type, \true))) {
                if ($this->getPreviousValue) {
                    $getPreviousValue = $this->getPreviousValue;
                    $this->methodCalls[$this->decoratedMethodIndex][1][$this->decoratedMethodArgumentIndex] = $getPreviousValue();
                    $this->decoratedClass = null;
                } else {
                    $arguments[$index] = new TypedReference($this->decoratedId, $this->decoratedClass);
                    $this->getPreviousValue = $getValue;
                    $this->decoratedMethodArgumentIndex = $index;
                    continue;
                }
            }
            $arguments[$index] = $getValue();
        }
        if ($parameters && !isset($arguments[++$index])) {
            while (0 <= --$index) {
                $parameter = $parameters[$index];
                if (!$parameter->isDefaultValueAvailable() || $parameter->getDefaultValue() !== $arguments[$index]) {
                    break;
                }
                unset($arguments[$index]);
            }
        }
        \ksort($arguments);
        return $arguments;
    }
    /**
     * @return TypedReference|null
     */
    private function getAutowiredReference(TypedReference $reference)
    {
        $this->lastFailure = null;
        $type = $reference->getType();
        if ($type !== (string) $reference) {
            return $reference;
        }
        if (null !== ($name = $reference->getName())) {
            if ($this->container->has($alias = $type . ' $' . $name) && !$this->container->findDefinition($alias)->isAbstract()) {
                return new TypedReference($alias, $type, $reference->getInvalidBehavior());
            }
            if ($this->container->has($name) && !$this->container->findDefinition($name)->isAbstract()) {
                foreach ($this->container->getAliases() as $id => $alias) {
                    if ($name === (string) $alias && 0 === \strpos($id, $type . ' $')) {
                        return new TypedReference($name, $type, $reference->getInvalidBehavior());
                    }
                }
            }
        }
        if ($this->container->has($type) && !$this->container->findDefinition($type)->isAbstract()) {
            return new TypedReference($type, $type, $reference->getInvalidBehavior());
        }
        return null;
    }
    private function populateAvailableTypes(ContainerBuilder $container)
    {
        $this->types = [];
        $this->ambiguousServiceTypes = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            $this->populateAvailableType($container, $id, $definition);
        }
    }
    private function populateAvailableType(ContainerBuilder $container, string $id, Definition $definition)
    {
        if ($definition->isAbstract()) {
            return;
        }
        if ('' === $id || '.' === $id[0] || $definition->isDeprecated() || !($reflectionClass = $container->getReflectionClass($definition->getClass(), \false))) {
            return;
        }
        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $this->set($reflectionInterface->name, $id);
        }
        do {
            $this->set($reflectionClass->name, $id);
        } while ($reflectionClass = $reflectionClass->getParentClass());
    }
    private function set(string $type, string $id)
    {
        if (isset($this->ambiguousServiceTypes[$type])) {
            $this->ambiguousServiceTypes[$type][] = $id;
            return;
        }
        if (!isset($this->types[$type]) || $this->types[$type] === $id) {
            $this->types[$type] = $id;
            return;
        }
        if (!isset($this->ambiguousServiceTypes[$type])) {
            $this->ambiguousServiceTypes[$type] = [$this->types[$type]];
            unset($this->types[$type]);
        }
        $this->ambiguousServiceTypes[$type][] = $id;
    }
    private function createTypeNotFoundMessageCallback(TypedReference $reference, string $label) : callable
    {
        if (null === $this->typesClone->container) {
            $this->typesClone->container = new ContainerBuilder($this->container->getParameterBag());
            $this->typesClone->container->setAliases($this->container->getAliases());
            $this->typesClone->container->setDefinitions($this->container->getDefinitions());
            $this->typesClone->container->setResourceTracking(\false);
        }
        $currentId = $this->currentId;
        return (function () use($reference, $label, $currentId) {
            return $this->createTypeNotFoundMessage($reference, $label, $currentId);
        })->bindTo($this->typesClone);
    }
    private function createTypeNotFoundMessage(TypedReference $reference, string $label, string $currentId) : string
    {
        if (!($r = $this->container->getReflectionClass($type = $reference->getType(), \false))) {
            try {
                $resource = new ClassExistenceResource($type, \false);
                $resource->isFresh(0);
                $parentMsg = \false;
            } catch (\ReflectionException $e) {
                $parentMsg = $e->getMessage();
            }
            $message = \sprintf('has type "%s" but this class %s.', $type, $parentMsg ? \sprintf('is missing a parent class (%s)', $parentMsg) : 'was not found');
        } else {
            $alternatives = $this->createTypeAlternatives($this->container, $reference);
            $message = $this->container->has($type) ? 'this service is abstract' : 'no such service exists';
            $message = \sprintf('references %s "%s" but %s.%s', $r->isInterface() ? 'interface' : 'class', $type, $message, $alternatives);
            if ($r->isInterface() && !$alternatives) {
                $message .= ' Did you create a class that implements this interface?';
            }
        }
        $message = \sprintf('Cannot autowire service "%s": %s %s', $currentId, $label, $message);
        if (null !== $this->lastFailure) {
            $message = $this->lastFailure . "\n" . $message;
            $this->lastFailure = null;
        }
        return $message;
    }
    private function createTypeAlternatives(ContainerBuilder $container, TypedReference $reference) : string
    {
        if ($message = $this->getAliasesSuggestionForType($container, $type = $reference->getType())) {
            return ' ' . $message;
        }
        if (null === $this->ambiguousServiceTypes) {
            $this->populateAvailableTypes($container);
        }
        $servicesAndAliases = $container->getServiceIds();
        if (!$container->has($type) && \false !== ($key = \array_search(\strtolower($type), \array_map('strtolower', $servicesAndAliases)))) {
            return \sprintf(' Did you mean "%s"?', $servicesAndAliases[$key]);
        } elseif (isset($this->ambiguousServiceTypes[$type])) {
            $message = \sprintf('one of these existing services: "%s"', \implode('", "', $this->ambiguousServiceTypes[$type]));
        } elseif (isset($this->types[$type])) {
            $message = \sprintf('the existing "%s" service', $this->types[$type]);
        } else {
            return '';
        }
        return \sprintf(' You should maybe alias this %s to %s.', \class_exists($type, \false) ? 'class' : 'interface', $message);
    }
    /**
     * @return string|null
     */
    private function getAliasesSuggestionForType(ContainerBuilder $container, string $type)
    {
        $aliases = [];
        foreach (\class_parents($type) + \class_implements($type) as $parent) {
            if ($container->has($parent) && !$container->findDefinition($parent)->isAbstract()) {
                $aliases[] = $parent;
            }
        }
        if (1 < ($len = \count($aliases))) {
            $message = 'Try changing the type-hint to one of its parents: ';
            for ($i = 0, --$len; $i < $len; ++$i) {
                $message .= \sprintf('%s "%s", ', \class_exists($aliases[$i], \false) ? 'class' : 'interface', $aliases[$i]);
            }
            $message .= \sprintf('or %s "%s".', \class_exists($aliases[$i], \false) ? 'class' : 'interface', $aliases[$i]);
            return $message;
        }
        if ($aliases) {
            return \sprintf('Try changing the type-hint to "%s" instead.', $aliases[0]);
        }
        return null;
    }
}
