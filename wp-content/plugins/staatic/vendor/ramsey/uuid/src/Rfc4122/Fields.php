<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Rfc4122;

use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use Staatic\Vendor\Ramsey\Uuid\Fields\SerializableFieldsTrait;
use Staatic\Vendor\Ramsey\Uuid\Type\Hexadecimal;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use function bin2hex;
use function dechex;
use function hexdec;
use function sprintf;
use function str_pad;
use function strlen;
use function substr;
use function unpack;
use const STR_PAD_LEFT;
final class Fields implements FieldsInterface
{
    use NilTrait;
    use SerializableFieldsTrait;
    use VariantTrait;
    use VersionTrait;
    private $bytes;
    public function __construct(string $bytes)
    {
        if (strlen($bytes) !== 16) {
            throw new InvalidArgumentException('The byte string must be 16 bytes long; ' . 'received ' . strlen($bytes) . ' bytes');
        }
        $this->bytes = $bytes;
        if (!$this->isCorrectVariant()) {
            throw new InvalidArgumentException('The byte string received does not conform to the RFC 4122 variant');
        }
        if (!$this->isCorrectVersion()) {
            throw new InvalidArgumentException('The byte string received does not contain a valid RFC 4122 version');
        }
    }
    public function getBytes() : string
    {
        return $this->bytes;
    }
    public function getClockSeq() : Hexadecimal
    {
        $clockSeq = hexdec(bin2hex(substr($this->bytes, 8, 2))) & 0x3fff;
        return new Hexadecimal(str_pad(dechex($clockSeq), 4, '0', STR_PAD_LEFT));
    }
    public function getClockSeqHiAndReserved() : Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 8, 1)));
    }
    public function getClockSeqLow() : Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 9, 1)));
    }
    public function getNode() : Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 10)));
    }
    public function getTimeHiAndVersion() : Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 6, 2)));
    }
    public function getTimeLow() : Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 0, 4)));
    }
    public function getTimeMid() : Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 4, 2)));
    }
    public function getTimestamp() : Hexadecimal
    {
        switch ($this->getVersion()) {
            case Uuid::UUID_TYPE_DCE_SECURITY:
                $timestamp = sprintf('%03x%04s%08s', hexdec($this->getTimeHiAndVersion()->toString()) & 0xfff, $this->getTimeMid()->toString(), '');
                break;
            case Uuid::UUID_TYPE_PEABODY:
                $timestamp = sprintf('%08s%04s%03x', $this->getTimeLow()->toString(), $this->getTimeMid()->toString(), hexdec($this->getTimeHiAndVersion()->toString()) & 0xfff);
                break;
            default:
                $timestamp = sprintf('%03x%04s%08s', hexdec($this->getTimeHiAndVersion()->toString()) & 0xfff, $this->getTimeMid()->toString(), $this->getTimeLow()->toString());
        }
        return new Hexadecimal($timestamp);
    }
    /**
     * @return int|null
     */
    public function getVersion()
    {
        if ($this->isNil()) {
            return null;
        }
        $parts = unpack('n*', $this->bytes);
        return (int) $parts[4] >> 12;
    }
    private function isCorrectVariant() : bool
    {
        if ($this->isNil()) {
            return \true;
        }
        return $this->getVariant() === Uuid::RFC_4122;
    }
}
