<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

trigger_deprecation('symfony/dependency-injection', '5.2', 'The "%s" class is deprecated.', ResolvePrivatesPass::class);
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
class ResolvePrivatesPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isPrivate()) {
                $definition->setPublic(\false);
                $definition->setPrivate(\true);
            }
        }
        foreach ($container->getAliases() as $id => $alias) {
            if ($alias->isPrivate()) {
                $alias->setPublic(\false);
                $alias->setPrivate(\true);
            }
        }
    }
}
