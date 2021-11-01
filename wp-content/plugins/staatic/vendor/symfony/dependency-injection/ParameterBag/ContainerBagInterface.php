<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\ParameterBag;

use Staatic\Vendor\Psr\Container\ContainerInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
interface ContainerBagInterface extends ContainerInterface
{
    public function all();
    public function resolveValue($value);
    public function escapeValue($value);
    public function unescapeValue($value);
}
