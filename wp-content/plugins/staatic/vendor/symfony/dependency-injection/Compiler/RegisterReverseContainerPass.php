<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
class RegisterReverseContainerPass implements CompilerPassInterface
{
    private $beforeRemoving;
    private $serviceId;
    private $tagName;
    public function __construct(bool $beforeRemoving, string $serviceId = 'reverse_container', string $tagName = 'container.reversible')
    {
        $this->beforeRemoving = $beforeRemoving;
        $this->serviceId = $serviceId;
        $this->tagName = $tagName;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        if (!$container->hasDefinition($this->serviceId)) {
            return;
        }
        $refType = $this->beforeRemoving ? ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE : ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services = [];
        foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tags) {
            $services[$id] = new Reference($id, $refType);
        }
        if ($this->beforeRemoving) {
            $services[$this->serviceId] = new Reference($this->serviceId, $refType);
        }
        $locator = $container->getDefinition($this->serviceId)->getArgument(1);
        if ($locator instanceof Reference) {
            $locator = $container->getDefinition((string) $locator);
        }
        if ($locator instanceof Definition) {
            foreach ($services as $id => $ref) {
                $services[$id] = new ServiceClosureArgument($ref);
            }
            $locator->replaceArgument(0, $services);
        } else {
            $locator->setValues($services);
        }
    }
}
