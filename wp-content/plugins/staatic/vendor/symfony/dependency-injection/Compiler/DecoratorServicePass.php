<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Alias;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
class DecoratorServicePass extends AbstractRecursivePass
{
    private $innerId = '.inner';
    /**
     * @param string|null $innerId
     */
    public function __construct($innerId = '.inner')
    {
        $this->innerId = $innerId;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        $definitions = new \SplPriorityQueue();
        $order = \PHP_INT_MAX;
        foreach ($container->getDefinitions() as $id => $definition) {
            if (!($decorated = $definition->getDecoratedService())) {
                continue;
            }
            $definitions->insert([$id, $definition], [$decorated[2], --$order]);
        }
        $decoratingDefinitions = [];
        foreach ($definitions as list($id, $definition)) {
            $decoratedService = $definition->getDecoratedService();
            list($inner, $renamedId) = $decoratedService;
            $invalidBehavior = $decoratedService[3] ?? ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            $definition->setDecoratedService(null);
            if (!$renamedId) {
                $renamedId = $id . '.inner';
            }
            $this->currentId = $renamedId;
            $this->processValue($definition);
            $definition->innerServiceId = $renamedId;
            $definition->decorationOnInvalid = $invalidBehavior;
            if ($container->hasAlias($inner)) {
                $alias = $container->getAlias($inner);
                $public = $alias->isPublic();
                $private = $alias->isPrivate();
                $container->setAlias($renamedId, new Alias((string) $alias, \false));
            } elseif ($container->hasDefinition($inner)) {
                $decoratedDefinition = $container->getDefinition($inner);
                $public = $decoratedDefinition->isPublic();
                $private = $decoratedDefinition->isPrivate();
                $decoratedDefinition->setPublic(\false);
                $container->setDefinition($renamedId, $decoratedDefinition);
                $decoratingDefinitions[$inner] = $decoratedDefinition;
            } elseif (ContainerInterface::IGNORE_ON_INVALID_REFERENCE === $invalidBehavior) {
                $container->removeDefinition($id);
                continue;
            } elseif (ContainerInterface::NULL_ON_INVALID_REFERENCE === $invalidBehavior) {
                $public = $definition->isPublic();
                $private = $definition->isPrivate();
            } else {
                throw new ServiceNotFoundException($inner, $id);
            }
            if (isset($decoratingDefinitions[$inner])) {
                $decoratingDefinition = $decoratingDefinitions[$inner];
                $decoratingTags = $decoratingDefinition->getTags();
                $resetTags = [];
                foreach (['container.service_locator', 'container.service_subscriber'] as $containerTag) {
                    if (isset($decoratingTags[$containerTag])) {
                        $resetTags[$containerTag] = $decoratingTags[$containerTag];
                        unset($decoratingTags[$containerTag]);
                    }
                }
                $definition->setTags(\array_merge($decoratingTags, $definition->getTags()));
                $decoratingDefinition->setTags($resetTags);
                $decoratingDefinitions[$inner] = $definition;
            }
            $container->setAlias($inner, $id)->setPublic($public);
        }
    }
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        if ($value instanceof Reference && $this->innerId === (string) $value) {
            return new Reference($this->currentId, $value->getInvalidBehavior());
        }
        return parent::processValue($value, $isRoot);
    }
}
