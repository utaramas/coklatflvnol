<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
final class AliasDeprecatedPublicServicesPass extends AbstractRecursivePass
{
    private $tagName;
    private $aliases = [];
    public function __construct(string $tagName = 'container.private')
    {
        $this->tagName = $tagName;
    }
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        if ($value instanceof Reference && isset($this->aliases[$id = (string) $value])) {
            return new Reference($this->aliases[$id], $value->getInvalidBehavior());
        }
        return parent::processValue($value, $isRoot);
    }
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tags) {
            if (null === ($package = $tags[0]['package'] ?? null)) {
                throw new InvalidArgumentException(\sprintf('The "package" attribute is mandatory for the "%s" tag on the "%s" service.', $this->tagName, $id));
            }
            if (null === ($version = $tags[0]['version'] ?? null)) {
                throw new InvalidArgumentException(\sprintf('The "version" attribute is mandatory for the "%s" tag on the "%s" service.', $this->tagName, $id));
            }
            $definition = $container->getDefinition($id);
            if (!$definition->isPublic() || $definition->isPrivate()) {
                continue;
            }
            $container->setAlias($id, $aliasId = '.' . $this->tagName . '.' . $id)->setPublic(\true)->setDeprecated($package, $version, 'Accessing the "%alias_id%" service directly from the container is deprecated, use dependency injection instead.');
            $container->setDefinition($aliasId, $definition);
            $this->aliases[$id] = $aliasId;
        }
        parent::process($container);
    }
}
