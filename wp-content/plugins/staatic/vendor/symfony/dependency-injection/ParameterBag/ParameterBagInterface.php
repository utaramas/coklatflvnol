<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\ParameterBag;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\LogicException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
interface ParameterBagInterface
{
    public function clear();
    /**
     * @param mixed[] $parameters
     */
    public function add($parameters);
    public function all();
    /**
     * @param string $name
     */
    public function get($name);
    /**
     * @param string $name
     */
    public function remove($name);
    /**
     * @param string $name
     */
    public function set($name, $value);
    /**
     * @param string $name
     */
    public function has($name);
    public function resolve();
    public function resolveValue($value);
    public function escapeValue($value);
    public function unescapeValue($value);
}
