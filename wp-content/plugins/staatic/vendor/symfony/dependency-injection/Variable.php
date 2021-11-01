<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection;

class Variable
{
    private $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function __toString()
    {
        return $this->name;
    }
}
