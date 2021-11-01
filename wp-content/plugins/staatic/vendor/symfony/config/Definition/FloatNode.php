<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition;

use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidTypeException;
class FloatNode extends NumericNode
{
    protected function validateType($value)
    {
        if (\is_int($value)) {
            $value = (float) $value;
        }
        if (!\is_float($value)) {
            $ex = new InvalidTypeException(\sprintf('Invalid type for path "%s". Expected "float", but got "%s".', $this->getPath(), \get_debug_type($value)));
            if ($hint = $this->getInfo()) {
                $ex->addHint($hint);
            }
            $ex->setPath($this->getPath());
            throw $ex;
        }
    }
    protected function getValidPlaceholderTypes() : array
    {
        return ['float'];
    }
}