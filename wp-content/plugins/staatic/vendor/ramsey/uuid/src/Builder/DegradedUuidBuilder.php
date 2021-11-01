<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Builder;

use Staatic\Vendor\Ramsey\Uuid\Codec\CodecInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\NumberConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\Time\DegradedTimeConverter;
use Staatic\Vendor\Ramsey\Uuid\Converter\TimeConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\DegradedUuid;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\Fields as Rfc4122Fields;
use Staatic\Vendor\Ramsey\Uuid\UuidInterface;
class DegradedUuidBuilder implements UuidBuilderInterface
{
    private $numberConverter;
    private $timeConverter;
    /**
     * @param TimeConverterInterface|null $timeConverter
     */
    public function __construct(NumberConverterInterface $numberConverter, $timeConverter = null)
    {
        $this->numberConverter = $numberConverter;
        $this->timeConverter = $timeConverter ?: new DegradedTimeConverter();
    }
    /**
     * @param CodecInterface $codec
     * @param string $bytes
     */
    public function build($codec, $bytes) : UuidInterface
    {
        return new DegradedUuid(new Rfc4122Fields($bytes), $this->numberConverter, $codec, $this->timeConverter);
    }
}
