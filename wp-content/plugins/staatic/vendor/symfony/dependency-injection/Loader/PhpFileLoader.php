<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
class PhpFileLoader extends FileLoader
{
    protected $autoRegisterAliasesForSinglyImplementedInterfaces = \false;
    public function load($resource, $type = null)
    {
        $container = $this->container;
        $loader = $this;
        $path = $this->locator->locate($resource);
        $this->setCurrentDir(\dirname($path));
        $this->container->fileExists($path);
        $load = \Closure::bind(function ($path) use($container, $loader, $resource, $type) {
            return include $path;
        }, $this, ProtectedPhpFileLoader::class);
        try {
            $callback = $load($path);
            if (\is_object($callback) && \is_callable($callback)) {
                $callback(new ContainerConfigurator($this->container, $this, $this->instanceof, $path, $resource), $this->container, $this);
            }
        } finally {
            $this->instanceof = [];
            $this->registerAliasesForSinglyImplementedInterfaces();
        }
    }
    /**
     * @param string|null $type
     */
    public function supports($resource, $type = null)
    {
        if (!\is_string($resource)) {
            return \false;
        }
        if (null === $type && 'php' === \pathinfo($resource, \PATHINFO_EXTENSION)) {
            return \true;
        }
        return 'php' === $type;
    }
}
final class ProtectedPhpFileLoader extends PhpFileLoader
{
}
