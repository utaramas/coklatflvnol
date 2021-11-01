<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection;

class Parameter
{
    private $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function __toString()
    {
        return $this->id;
    }
}
