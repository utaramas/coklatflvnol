<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
interface DumperInterface
{
    /**
     * @param Definition $definition
     */
    public function isProxyCandidate($definition);
    /**
     * @param Definition $definition
     * @param string $id
     * @param string $factoryCode
     */
    public function getProxyFactoryCode($definition, $id, $factoryCode);
    /**
     * @param Definition $definition
     */
    public function getProxyCode($definition);
}
