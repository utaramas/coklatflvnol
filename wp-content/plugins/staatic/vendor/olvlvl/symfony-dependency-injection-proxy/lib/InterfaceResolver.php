<?php

namespace Staatic\Vendor\olvlvl\SymfonyDependencyInjectionProxy;

use Exception;
interface InterfaceResolver
{
    /**
     * @param string $class
     */
    public function resolveInterface($class) : string;
}
