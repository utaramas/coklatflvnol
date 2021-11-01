<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition;

use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidTypeException;
interface NodeInterface
{
    public function getName();
    public function getPath();
    public function isRequired();
    public function hasDefaultValue();
    public function getDefaultValue();
    public function normalize($value);
    public function merge($leftSide, $rightSide);
    public function finalize($value);
}
