<?php

namespace Staatic\Vendor\Symfony\Component\Config\Loader;

use Staatic\Vendor\Symfony\Component\Config\Exception\LoaderLoadException;
abstract class Loader implements LoaderInterface
{
    protected $resolver;
    public function getResolver()
    {
        return $this->resolver;
    }
    /**
     * @param LoaderResolverInterface $resolver
     */
    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
    }
    /**
     * @param string|null $type
     */
    public function import($resource, $type = null)
    {
        return $this->resolve($resource, $type)->load($resource, $type);
    }
    /**
     * @param string|null $type
     */
    public function resolve($resource, $type = null)
    {
        if ($this->supports($resource, $type)) {
            return $this;
        }
        $loader = null === $this->resolver ? \false : $this->resolver->resolve($resource, $type);
        if (\false === $loader) {
            throw new LoaderLoadException($resource, null, 0, null, $type);
        }
        return $loader;
    }
}
