<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader;

use Staatic\Vendor\Symfony\Component\Config\Loader\Loader;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
class ClosureLoader extends Loader
{
    private $container;
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }
    /**
     * @param string|null $type
     */
    public function load($resource, $type = null)
    {
        $resource($this->container);
    }
    /**
     * @param string|null $type
     */
    public function supports($resource, $type = null)
    {
        return $resource instanceof \Closure;
    }
}
