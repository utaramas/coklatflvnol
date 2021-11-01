<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Generator;

use Staatic\Vendor\Ramsey\Uuid\Converter\NumberConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use function bin2hex;
use function explode;
use function hex2bin;
use function microtime;
use function str_pad;
use function substr;
use const STR_PAD_LEFT;
class CombGenerator implements RandomGeneratorInterface
{
    const TIMESTAMP_BYTES = 6;
    private $randomGenerator;
    private $converter;
    public function __construct(RandomGeneratorInterface $generator, NumberConverterInterface $numberConverter)
    {
        $this->converter = $numberConverter;
        $this->randomGenerator = $generator;
    }
    /**
     * @param int $length
     */
    public function generate($length) : string
    {
        if ($length < self::TIMESTAMP_BYTES || $length < 0) {
            throw new InvalidArgumentException('Length must be a positive integer greater than or equal to ' . self::TIMESTAMP_BYTES);
        }
        $hash = '';
        if (self::TIMESTAMP_BYTES > 0 && $length > self::TIMESTAMP_BYTES) {
            $hash = $this->randomGenerator->generate($length - self::TIMESTAMP_BYTES);
        }
        $lsbTime = str_pad($this->converter->toHex($this->timestamp()), self::TIMESTAMP_BYTES * 2, '0', STR_PAD_LEFT);
        return (string) hex2bin(str_pad(bin2hex($hash), $length - self::TIMESTAMP_BYTES, '0') . $lsbTime);
    }
    private function timestamp() : string
    {
        $time = explode(' ', microtime(\false));
        return $time[1] . substr($time[0], 2, 5);
    }
}
