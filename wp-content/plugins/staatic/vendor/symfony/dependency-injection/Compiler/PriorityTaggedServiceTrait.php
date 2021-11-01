<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
use Staatic\Vendor\Symfony\Component\DependencyInjection\TypedReference;
trait PriorityTaggedServiceTrait
{
    private function findAndSortTaggedServices($tagName, ContainerBuilder $container) : array
    {
        $indexAttribute = $defaultIndexMethod = $needsIndexes = $defaultPriorityMethod = null;
        if ($tagName instanceof TaggedIteratorArgument) {
            $indexAttribute = $tagName->getIndexAttribute();
            $defaultIndexMethod = $tagName->getDefaultIndexMethod();
            $needsIndexes = $tagName->needsIndexes();
            $defaultPriorityMethod = $tagName->getDefaultPriorityMethod() ?? 'getDefaultPriority';
            $tagName = $tagName->getTag();
        }
        $i = 0;
        $services = [];
        foreach ($container->findTaggedServiceIds($tagName, \true) as $serviceId => $attributes) {
            $defaultPriority = null;
            $defaultIndex = null;
            $class = $container->getDefinition($serviceId)->getClass();
            $class = $container->getParameterBag()->resolveValue($class) ?: null;
            foreach ($attributes as $attribute) {
                $index = $priority = null;
                if (isset($attribute['priority'])) {
                    $priority = $attribute['priority'];
                } elseif (null === $defaultPriority && $defaultPriorityMethod && $class) {
                    $defaultPriority = PriorityTaggedServiceUtil::getDefaultPriority($container, $serviceId, $class, $defaultPriorityMethod, $tagName);
                }
                $priority = $priority ?? $defaultPriority ?? ($defaultPriority = 0);
                if (null === $indexAttribute && !$defaultIndexMethod && !$needsIndexes) {
                    $services[] = [$priority, ++$i, null, $serviceId, null];
                    continue 2;
                }
                if (null !== $indexAttribute && isset($attribute[$indexAttribute])) {
                    $index = $attribute[$indexAttribute];
                } elseif (null === $defaultIndex && $defaultPriorityMethod && $class) {
                    $defaultIndex = PriorityTaggedServiceUtil::getDefaultIndex($container, $serviceId, $class, $defaultIndexMethod ?? 'getDefaultName', $tagName, $indexAttribute);
                }
                $index = $index ?? $defaultIndex ?? ($defaultIndex = $serviceId);
                $services[] = [$priority, ++$i, $index, $serviceId, $class];
            }
        }
        \uasort($services, static function ($a, $b) {
            return $b[0] <=> $a[0] ?: $a[1] <=> $b[1];
        });
        $refs = [];
        foreach ($services as list(, , $index, $serviceId, $class)) {
            if (!$class) {
                $reference = new Reference($serviceId);
            } elseif ($index === $serviceId) {
                $reference = new TypedReference($serviceId, $class);
            } else {
                $reference = new TypedReference($serviceId, $class, ContainerBuilder::EXCEPTION_ON_INVALID_REFERENCE, $index);
            }
            if (null === $index) {
                $refs[] = $reference;
            } else {
                $refs[$index] = $reference;
            }
        }
        return $refs;
    }
}
class PriorityTaggedServiceUtil
{
    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param string $class
     * @param string $defaultIndexMethod
     * @param string $tagName
     * @param string|null $indexAttribute
     * @return string|null
     */
    public static function getDefaultIndex($container, $serviceId, $class, $defaultIndexMethod, $tagName, $indexAttribute)
    {
        if (!($r = $container->getReflectionClass($class)) || !$r->hasMethod($defaultIndexMethod)) {
            return null;
        }
        if (null !== $indexAttribute) {
            $service = $class !== $serviceId ? \sprintf('service "%s"', $serviceId) : 'on the corresponding service';
            $message = [\sprintf('Either method "%s::%s()" should ', $class, $defaultIndexMethod), \sprintf(' or tag "%s" on %s is missing attribute "%s".', $tagName, $service, $indexAttribute)];
        } else {
            $message = [\sprintf('Method "%s::%s()" should ', $class, $defaultIndexMethod), '.'];
        }
        if (!($rm = $r->getMethod($defaultIndexMethod))->isStatic()) {
            throw new InvalidArgumentException(\implode('be static', $message));
        }
        if (!$rm->isPublic()) {
            throw new InvalidArgumentException(\implode('be public', $message));
        }
        $defaultIndex = $rm->invoke(null);
        if (!\is_string($defaultIndex)) {
            throw new InvalidArgumentException(\implode(\sprintf('return a string (got "%s")', \get_debug_type($defaultIndex)), $message));
        }
        return $defaultIndex;
    }
    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param string $class
     * @param string $defaultPriorityMethod
     * @param string $tagName
     * @return int|null
     */
    public static function getDefaultPriority($container, $serviceId, $class, $defaultPriorityMethod, $tagName)
    {
        if (!($r = $container->getReflectionClass($class)) || !$r->hasMethod($defaultPriorityMethod)) {
            return null;
        }
        if (!($rm = $r->getMethod($defaultPriorityMethod))->isStatic()) {
            throw new InvalidArgumentException(\sprintf('Either method "%s::%s()" should be static or tag "%s" on service "%s" is missing attribute "priority".', $class, $defaultPriorityMethod, $tagName, $serviceId));
        }
        if (!$rm->isPublic()) {
            throw new InvalidArgumentException(\sprintf('Either method "%s::%s()" should be public or tag "%s" on service "%s" is missing attribute "priority".', $class, $defaultPriorityMethod, $tagName, $serviceId));
        }
        $defaultPriority = $rm->invoke(null);
        if (!\is_int($defaultPriority)) {
            throw new InvalidArgumentException(\sprintf('Method "%s::%s()" should return an integer (got "%s") or tag "%s" on service "%s" is missing attribute "priority".', $class, $defaultPriorityMethod, \get_debug_type($defaultPriority), $tagName, $serviceId));
        }
        return $defaultPriority;
    }
}