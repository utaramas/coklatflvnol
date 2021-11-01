<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection;

use Staatic\Vendor\Psr\Container\ContainerInterface as PsrContainerInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
interface ContainerInterface extends PsrContainerInterface
{
    const RUNTIME_EXCEPTION_ON_INVALID_REFERENCE = 0;
    const EXCEPTION_ON_INVALID_REFERENCE = 1;
    const NULL_ON_INVALID_REFERENCE = 2;
    const IGNORE_ON_INVALID_REFERENCE = 3;
    const IGNORE_ON_UNINITIALIZED_REFERENCE = 4;
    /**
     * @param object|null $service
     * @param string $id
     */
    public function set($id, $service);
    /**
     * @param int $invalidBehavior
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE);
    public function has($id);
    /**
     * @param string $id
     */
    public function initialized($id);
    /**
     * @param string $name
     */
    public function getParameter($name);
    /**
     * @param string $name
     */
    public function hasParameter($name);
    /**
     * @param string $name
     */
    public function setParameter($name, $value);
}
