<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition;

use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidTypeException;
class BooleanNode extends ScalarNode
{
    protected function validateType($value)
    {
        if (!\is_bool($value)) {
            $ex = new InvalidTypeException(\sprintf('Invalid type for path "%s". Expected "bool", but got "%s".', $this->getPath(), \get_debug_type($value)));
            if ($hint = $this->getInfo()) {
                $ex->addHint($hint);
            }
            $ex->setPath($this->getPath());
            throw $ex;
        }
    }
    protected function isValueEmpty($value)
    {
        return \false;
    }
    protected function getValidPlaceholderTypes() : array
    {
        return ['bool'];
    }
}
