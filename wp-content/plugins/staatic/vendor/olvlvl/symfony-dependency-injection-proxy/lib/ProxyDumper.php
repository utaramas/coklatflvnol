<?php

namespace Staatic\Vendor\olvlvl\SymfonyDependencyInjectionProxy;

use Exception;
use InvalidArgumentException;
use Staatic\Vendor\olvlvl\SymfonyDependencyInjectionProxy\InterfaceResolver\BasicInterfaceResolver;
use ReflectionException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\LogicException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface;
use function class_exists;
use function ltrim;
use function sprintf;
final class ProxyDumper implements DumperInterface
{
    private $interfaceResolver;
    private $factoryRenderer;
    public function __construct(InterfaceResolver $interfaceResolver = null, FactoryRenderer $factoryRenderer = null)
    {
        $this->interfaceResolver = $interfaceResolver ?? new BasicInterfaceResolver();
        $this->factoryRenderer = $factoryRenderer ?? new FactoryRenderer(new MethodRenderer());
    }
    /**
     * @param Definition $definition
     */
    public function isProxyCandidate($definition) : bool
    {
        $class = $definition->getClass();
        return $definition->isLazy() && ($definition->getFactory() || $class && class_exists($class));
    }
    /**
     * @param Definition $definition
     * @param string $id
     * @param string $factoryCode
     */
    public function getProxyFactoryCode($definition, $id, $factoryCode) : string
    {
        if (!$factoryCode) {
            throw new InvalidArgumentException("Missing factory code to construct the service `{$id}`.");
        }
        $store = '';
        if ($definition->isShared()) {
            $store = sprintf('$this->%s[\'%s\'] = ', $definition->isPublic() && !$definition->isPrivate() ? 'services' : 'privates', $id);
        }
        $interface = $this->findInterface($definition);
        $proxy = ltrim($this->renderFactory($interface, $factoryCode));
        return <<<PHPTPL
        if (\$lazyLoad) {
            return {$store}{$proxy}
        }


PHPTPL;
    }
    /**
     * @param Definition $definition
     */
    public function getProxyCode($definition) : string
    {
        return '';
    }
    private function findInterface(Definition $definition) : string
    {
        $interface = $this->resolveInterfaceFromTags($definition);
        if ($interface) {
            return $interface;
        }
        $class = $definition->getClass();
        if (!$class) {
            throw new LogicException("Unable to resolve interface, class is missing.");
        }
        return $this->interfaceResolver->resolveInterface($class);
    }
    /**
     * @return string|null
     */
    private function resolveInterfaceFromTags(Definition $definition)
    {
        $proxy = $definition->getTag('proxy');
        return $proxy[0]['interface'] ?? null;
    }
    private function renderFactory(string $interface, string $factoryCode) : string
    {
        return ($this->factoryRenderer)($interface, $factoryCode);
    }
}
