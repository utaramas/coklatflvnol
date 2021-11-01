<?php

namespace Staatic\Vendor\Symfony\Component\Config\Loader;

class LoaderResolver implements LoaderResolverInterface
{
    private $loaders = [];
    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }
    /**
     * @param string|null $type
     */
    public function resolve($resource, $type = null)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource, $type)) {
                return $loader;
            }
        }
        return \false;
    }
    /**
     * @param LoaderInterface $loader
     */
    public function addLoader($loader)
    {
        $this->loaders[] = $loader;
        $loader->setResolver($this);
    }
    public function getLoaders()
    {
        return $this->loaders;
    }
}
