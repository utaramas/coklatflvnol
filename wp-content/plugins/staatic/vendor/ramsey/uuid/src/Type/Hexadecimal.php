<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Type;

use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use function ctype_xdigit;
use function strpos;
use function strtolower;
use function substr;
final class Hexadecimal implements TypeInterface
{
    private $value;
    public function __construct(string $value)
    {
        $value = strtolower($value);
        if (strpos($value, '0x') === 0) {
            $value = substr($value, 2);
        }
        if (!ctype_xdigit($value)) {
            throw new InvalidArgumentException('Value must be a hexadecimal number');
        }
        $this->value = $value;
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
