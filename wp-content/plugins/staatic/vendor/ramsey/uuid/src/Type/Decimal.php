<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Type;

use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use function is_numeric;
final class Decimal implements NumberInterface
{
    private $value;
    private $isNegative = \false;
    public function __construct($value)
    {
        $value = (string) $value;
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Value must be a signed decimal or a string containing only ' . 'digits 0-9 and, optionally, a decimal point or sign (+ or -)');
        }
        if (\strpos($value, '+') === 0) {
            $value = \substr($value, 1);
        }
        if (\abs((float) $value) === 0.0) {
            $value = '0';
        }
        if (\strpos($value, '-') === 0) {
            $this->isNegative = \true;
        }
        $this->value = $value;
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
