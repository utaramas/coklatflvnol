<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Type;

use Staatic\Vendor\Ramsey\Uuid\Exception\UnsupportedOperationException;
use Staatic\Vendor\Ramsey\Uuid\Type\Integer as IntegerObject;
use stdClass;
use function json_decode;
use function json_encode;
final class Time implements TypeInterface
{
    private $seconds;
    private $microseconds;
    public function __construct($seconds, $microseconds = 0)
    {
        $this->seconds = new IntegerObject($seconds);
        $this->microseconds = new IntegerObject($microseconds);
    }
    public function getSeconds() : IntegerObject
    {
        return $this->seconds;
    }
    public function getMicroseconds() : IntegerObject
    {
        return $this->microseconds;
    }
    public function toString() : string
    {
        return $this->seconds->toString() . '.' . $this->microseconds->toString();
    }
    public function __toString() : string
    {
        return $this->toString();
    }
    public function jsonSerialize() : array
    {
        return ['seconds' => $this->getSeconds()->toString(), 'microseconds' => $this->getMicroseconds()->toString()];
    }
    public function serialize() : string
    {
        return (string) json_encode($this);
    }
    /**
     * @return void
     */
    public function unserialize($serialized)
    {
        $time = json_decode($serialized);
        if (!isset($time->seconds) || !isset($time->microseconds)) {
            throw new UnsupportedOperationException('Attempted to unserialize an invalid value');
        }
        $this->__construct($time->seconds, $time->microseconds);
    }
}
