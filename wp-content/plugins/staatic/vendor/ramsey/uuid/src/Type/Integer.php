<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Type;

use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use function ctype_digit;
use function ltrim;
use function strpos;
use function substr;
final class Integer implements NumberInterface
{
    private $value;
    private $isNegative = \false;
    public function __construct($value)
    {
        $value = (string) $value;
        $sign = '+';
        if (strpos($value, '-') === 0 || strpos($value, '+') === 0) {
            $sign = substr($value, 0, 1);
            $value = substr($value, 1);
        }
        if (!ctype_digit($value)) {
            throw new InvalidArgumentException('Value must be a signed integer or a string containing only ' . 'digits 0-9 and, optionally, a sign (+ or -)');
        }
        $value = ltrim($value, '0');
        if ($value === '') {
            $value = '0';
        }
        if ($sign === '-' && $value !== '0') {
            $value = $sign . $value;
            $this->isNegative = \true;
        }
        $numericValue = $value;
        $this->value = $numericValue;
    }
    public function isNegative() : bool
    {
        return $this->isNegative;
    }
    public function toString() : string
    {
        return $this->value;
    }
    public function __toString() : string
    {
        return $this->toString();
    }
    public function jsonSerialize() : string
    {
        return $this->toString();
    }
    public function serialize() : string
    {
        return $this->toString();
    }
    /**
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->__construct($serialized);
    }
}
