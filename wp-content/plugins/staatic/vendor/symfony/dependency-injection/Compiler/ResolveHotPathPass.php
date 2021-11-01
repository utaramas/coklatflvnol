<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
class ResolveHotPathPass extends AbstractRecursivePass
{
    private $tagName;
    private $resolvedIds = [];
    public function __construct(string $tagName = 'container.hot_path')
    {
        $this->tagName = $tagName;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        try {
            parent::process($container);
            $container->getDefinition('service_container')->clearTag($this->tagName);
        } finally {
            $this->resolvedIds = [];
        }
    }
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        if ($value instanceof ArgumentInterface) {
            return $value;
        }
        if ($value instanceof Definition && $isRoot) {
            if ($value->isDeprecated()) {
                return $value->clearTag($this->tagName);
            }
            $this->resolvedIds[$this->currentId] = \true;
            if (!$value->hasTag($this->tagName)) {
                return $value;
            }
        }
        if ($value instanceof Reference && ContainerBuilder::IGNORE_ON_UNINITIALIZED_REFERENCE !== $value->getInvalidBehavior() && $this->container->hasDefinition($id = (string) $value)) {
            $definition = $this->container->getDefinition($id);
            if ($definition->isDeprecated() || $definition->hasTag($this->tagName)) {
                return $value;
            }
            $definition->addTag($this->tagName);
            if (isset($this->resolvedIds[$id])) {
                parent::processValue($definition, \false);
            }
            return $value;
        }
        return parent::processValue($value, $isRoot);
    }
}
