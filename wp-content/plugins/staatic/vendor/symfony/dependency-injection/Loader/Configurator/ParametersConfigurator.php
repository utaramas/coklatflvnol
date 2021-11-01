<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
class ParametersConfigurator extends AbstractConfigurator
{
    const FACTORY = 'parameters';
    private $container;
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }
    /**
     * @param string $name
     */
    public final function set($name, $value) : self
    {
        $this->container->setParameter($name, static::processValue($value, \true));
        return $this;
    }
    public final function __invoke(string $name, $value) : self
    {
        return $this->set($name, $value);
    }
}
