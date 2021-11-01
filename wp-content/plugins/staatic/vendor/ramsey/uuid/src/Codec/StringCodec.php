<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Codec;

use Staatic\Vendor\Ramsey\Uuid\Builder\UuidBuilderInterface;
use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidUuidStringException;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\FieldsInterface;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Vendor\Ramsey\Uuid\UuidInterface;
use function hex2bin;
use function implode;
use function str_replace;
use function strlen;
use function substr;
class StringCodec implements CodecInterface
{
    private $builder;
    public function __construct(UuidBuilderInterface $builder)
    {
        $this->builder = $builder;
    }
    /**
     * @param UuidInterface $uuid
     */
    public function encode($uuid) : string
    {
        $fields = $uuid->getFields();
        return $fields->getTimeLow()->toString() . '-' . $fields->getTimeMid()->toString() . '-' . $fields->getTimeHiAndVersion()->toString() . '-' . $fields->getClockSeqHiAndReserved()->toString() . $fields->getClockSeqLow()->toString() . '-' . $fields->getNode()->toString();
    }
    /**
     * @param UuidInterface $uuid
     */
    public function encodeBinary($uuid) : string
    {
        return $uuid->getFields()->getBytes();
    }
    /**
     * @param string $encodedUuid
     */
    public function decode($encodedUuid) : UuidInterface
    {
        return $this->builder->build($this, $this->getBytes($encodedUuid));
    }
    /**
     * @param string $bytes
     */
    public function decodeBytes($bytes) : UuidInterface
    {
        if (strlen($bytes) !== 16) {
            throw new InvalidArgumentException('$bytes string should contain 16 characters.');
        }
        return $this->builder->build($this, $bytes);
    }
    protected function getBuilder() : UuidBuilderInterface
    {
        return $this->builder;
    }
    /**
     * @param string $encodedUuid
     */
    protected function getBytes($encodedUuid) : string
    {
        $parsedUuid = str_replace(['urn:', 'uuid:', 'URN:', 'UUID:', '{', '}', '-'], '', $encodedUuid);
        $components = [substr($parsedUuid, 0, 8), substr($parsedUuid, 8, 4), substr($parsedUuid, 12, 4), substr($parsedUuid, 16, 4), substr($parsedUuid, 20)];
        if (!Uuid::isValid(implode('-', $components))) {
            throw new InvalidUuidStringException('Invalid UUID string: ' . $encodedUuid);
        }
        return (string) hex2bin($parsedUuid);
    }
}
