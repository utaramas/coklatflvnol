<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Staatic\Vendor\Symfony\Component\ExpressionLanguage\Expression;
class ContainerConfigurator extends AbstractConfigurator
{
    const FACTORY = 'container';
    private $container;
    private $loader;
    private $instanceof;
    private $path;
    private $file;
    private $anonymousCount = 0;
    public function __construct(ContainerBuilder $container, PhpFileLoader $loader, array &$instanceof, string $path, string $file)
    {
        $this->container = $container;
        $this->loader = $loader;
        $this->instanceof =& $instanceof;
        $this->path = $path;
        $this->file = $file;
    }
    /**
     * @param string $namespace
     * @param mixed[] $config
     */
    public final function extension($namespace, $config)
    {
        if (!$this->container->hasExtension($namespace)) {
            $extensions = \array_filter(\array_map(function (ExtensionInterface $ext) {
                return $ext->getAlias();
            }, $this->container->getExtensions()));
            throw new InvalidArgumentException(\sprintf('There is no extension able to load the configuration for "%s" (in "%s"). Looked for namespace "%s", found "%s".', $namespace, $this->file, $namespace, $extensions ? \implode('", "', $extensions) : 'none'));
        }
        $this->container->loadFromExtension($namespace, static::processValue($config));
    }
    /**
     * @param string $resource
     * @param string|null $type
     */
    public final function import($resource, $type = null, $ignoreErrors = \false)
    {
        $this->loader->setCurrentDir(\dirname($this->path));
        $this->loader->import($resource, $type, $ignoreErrors, $this->file);
    }
    public final function parameters() : ParametersConfigurator
    {
        return new ParametersConfigurator($this->container);
    }
    public final function services() : ServicesConfigurator
    {
        return new ServicesConfigurator($this->container, $this->loader, $this->instanceof, $this->path, $this->anonymousCount);
    }
    /**
     * @param string $path
     */
    public final function withPath($path) : self
    {
        $clone = clone $this;
        $clone->path = $clone->file = $path;
        return $clone;
    }
}
function param(string $name) : string
{
    return '%' . $name . '%';
}
function ref(string $id) : ReferenceConfigurator
{
    trigger_deprecation('symfony/dependency-injection', '5.1', '"%s()" is deprecated, use "service()" instead.', __FUNCTION__);
    return new ReferenceConfigurator($id);
}
function service(string $serviceId) : ReferenceConfigurator
{
    return new ReferenceConfigurator($serviceId);
}
function inline(string $class = null) : InlineServiceConfigurator
{
    trigger_deprecation('symfony/dependency-injection', '5.1', '"%s()" is deprecated, use "inline_service()" instead.', __FUNCTION__);
    return new InlineServiceConfigurator(new Definition($class));
}
function inline_service(string $class = null) : InlineServiceConfigurator
{
    return new InlineServiceConfigurator(new Definition($class));
}
function service_locator(array $values) : ServiceLocatorArgument
{
    return new ServiceLocatorArgument(AbstractConfigurator::processValue($values, \true));
}
function iterator(array $values) : IteratorArgument
{
    return new IteratorArgument(AbstractConfigurator::processValue($values, \true));
}
function tagged_iterator(string $tag, string $indexAttribute = null, string $defaultIndexMethod = null, string $defaultPriorityMethod = null) : TaggedIteratorArgument
{
    return new TaggedIteratorArgument($tag, $indexAttribute, $defaultIndexMethod, \false, $defaultPriorityMethod);
}
function tagged_locator(string $tag, string $indexAttribute = null, string $defaultIndexMethod = null) : ServiceLocatorArgument
{
    return new ServiceLocatorArgument(new TaggedIteratorArgument($tag, $indexAttribute, $defaultIndexMethod, \true));
}
function expr(string $expression) : Expression
{
    return new Expression($expression);
}
function abstract_arg(string $description) : AbstractArgument
{
    return new AbstractArgument($description);
}
