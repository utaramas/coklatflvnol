<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
class ReferenceConfigurator extends AbstractConfigurator
{
    protected $id;
    protected $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public final function ignoreOnInvalid() : self
    {
        $this->invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
        return $this;
    }
    public final function nullOnInvalid() : self
    {
        $this->invalidBehavior = ContainerInterface::NULL_ON_INVALID_REFERENCE;
        return $this;
    }
    public final function ignoreOnUninitialized() : self
    {
        $this->invalidBehavior = ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE;
        return $this;
    }
    public function __toString()
    {
        return $this->id;
    }
}
