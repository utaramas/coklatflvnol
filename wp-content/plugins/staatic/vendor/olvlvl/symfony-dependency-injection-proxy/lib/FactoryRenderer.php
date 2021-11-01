<?php

namespace Staatic\Vendor\olvlvl\SymfonyDependencyInjectionProxy;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function array_map;
use function implode;
use const PHP_VERSION_ID;
class FactoryRenderer
{
    private $methodRenderer;
    public function __construct(MethodRenderer $methodRenderer)
    {
        $this->methodRenderer = $methodRenderer;
    }
    public function __invoke(string $interface, string $factoryCode) : string
    {
        $methods = $this->renderMethods((new ReflectionClass($interface))->getMethods(), PHP_VERSION_ID >= 70400 ? '($this->service ??= ($this->factory)())' : '($this->service ?: $this->service = ($this->factory)())');
        return <<<PHPTPL
            new class(
                function () {
                    return {$factoryCode};
                }
            ) implements \\{$interface}
            {
                private \$factory, \$service;

                public function __construct(callable \$factory)
                {
                    \$this->factory = \$factory;
                }

{$methods}
            };
PHPTPL;
    }
    private function renderMethods(array $methods, string $getterCode) : string
    {
        $renderMethod = $this->methodRenderer;
        return implode("\n", array_map(function (ReflectionMethod $method) use($renderMethod, $getterCode) {
            return $renderMethod($method, $getterCode);
        }, $methods));
    }
}
