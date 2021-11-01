<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid;

use DateTimeInterface;
use Staatic\Vendor\Ramsey\Uuid\Builder\UuidBuilderInterface;
use Staatic\Vendor\Ramsey\Uuid\Codec\CodecInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\NumberConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\TimeConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\DefaultTimeGenerator;
use Staatic\Vendor\Ramsey\Uuid\Generator\NameGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\RandomGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\TimeGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Lazy\LazyUuidFromString;
use Staatic\Vendor\Ramsey\Uuid\Provider\NodeProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Provider\Time\FixedTimeProvider;
use Staatic\Vendor\Ramsey\Uuid\Type\Hexadecimal;
use Staatic\Vendor\Ramsey\Uuid\Type\Integer as IntegerObject;
use Staatic\Vendor\Ramsey\Uuid\Type\Time;
use Staatic\Vendor\Ramsey\Uuid\Validator\ValidatorInterface;
use function bin2hex;
use function hex2bin;
use function pack;
use function str_pad;
use function strtolower;
use function substr;
use function substr_replace;
use function unpack;
use const STR_PAD_LEFT;
class UuidFactory implements UuidFactoryInterface
{
    private $codec;
    private $dceSecurityGenerator;
    private $nameGenerator;
    private $nodeProvider;
    private $numberConverter;
    private $randomGenerator;
    private $timeConverter;
    private $timeGenerator;
    private $uuidBuilder;
    private $validator;
    private $isDefaultFeatureSet;
    /**
     * @param FeatureSet|null $features
     */
    public function __construct($features = null)
    {
        $this->isDefaultFeatureSet = $features === null;
        $features = $features ?: new FeatureSet();
        $this->codec = $features->getCodec();
        $this->dceSecurityGenerator = $features->getDceSecurityGenerator();
        $this->nameGenerator = $features->getNameGenerator();
        $this->nodeProvider = $features->getNodeProvider();
        $this->numberConverter = $features->getNumberConverter();
        $this->randomGenerator = $features->getRandomGenerator();
        $this->timeConverter = $features->getTimeConverter();
        $this->timeGenerator = $features->getTimeGenerator();
        $this->uuidBuilder = $features->getBuilder();
        $this->validator = $features->getValidator();
    }
    public function getCodec() : CodecInterface
    {
        return $this->codec;
    }
    /**
     * @param CodecInterface $codec
     * @return void
     */
    public function setCodec($codec)
    {
        $this->isDefaultFeatureSet = \false;
        $this->codec = $codec;
    }
    public function getNameGenerator() : NameGeneratorInterface
    {
        return $this->nameGenerator;
    }
    /**
     * @param NameGeneratorInterface $nameGenerator
     * @return void
     */
    public function setNameGenerator($nameGenerator)
    {
        $this->isDefaultFeatureSet = \false;
        $this->nameGenerator = $nameGenerator;
    }
    public function getNodeProvider() : NodeProviderInterface
    {
        return $this->nodeProvider;
    }
    public function getRandomGenerator() : RandomGeneratorInterface
    {
        return $this->randomGenerator;
    }
    public function getTimeGenerator() : TimeGeneratorInterface
    {
        return $this->timeGenerator;
    }
    /**
     * @param TimeGeneratorInterface $generator
     * @return void
     */
    public function setTimeGenerator($generator)
    {
        $this->isDefaultFeatureSet = \false;
        $this->timeGenerator = $generator;
    }
    public function getDceSecurityGenerator() : DceSecurityGeneratorInterface
    {
        return $this->dceSecurityGenerator;
    }
    /**
     * @param DceSecurityGeneratorInterface $generator
     * @return void
     */
    public function setDceSecurityGenerator($generator)
    {
        $this->isDefaultFeatureSet = \false;
        $this->dceSecurityGenerator = $generator;
    }
    public function getNumberConverter() : NumberConverterInterface
    {
        return $this->numberConverter;
    }
    /**
     * @param RandomGeneratorInterface $generator
     * @return void
     */
    public function setRandomGenerator($generator)
    {
        $this->isDefaultFeatureSet = \false;
        $this->randomGenerator = $generator;
    }
    /**
     * @param NumberConverterInterface $converter
     * @return void
     */
    public function setNumberConverter($converter)
    {
        $this->isDefaultFeatureSet = \false;
        $this->numberConverter = $converter;
    }
    public function getUuidBuilder() : UuidBuilderInterface
    {
        return $this->uuidBuilder;
    }
    /**
     * @param UuidBuilderInterface $builder
     * @return void
     */
    public function setUuidBuilder($builder)
    {
        $this->isDefaultFeatureSet = \false;
        $this->uuidBuilder = $builder;
    }
    public function getValidator() : ValidatorInterface
    {
        return $this->validator;
    }
    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function setValidator($validator)
    {
        $this->isDefaultFeatureSet = \false;
        $this->validator = $validator;
    }
    /**
     * @param string $bytes
     */
    public function fromBytes($bytes) : UuidInterface
    {
        return $this->codec->decodeBytes($bytes);
    }
    /**
     * @param string $uuid
     */
    public function fromString($uuid) : UuidInterface
    {
        $uuid = strtolower($uuid);
        return $this->codec->decode($uuid);
    }
    /**
     * @param string $integer
     */
    public function fromInteger($integer) : UuidInterface
    {
        $hex = $this->numberConverter->toHex($integer);
        $hex = str_pad($hex, 32, '0', STR_PAD_LEFT);
        return $this->fromString($hex);
    }
    /**
     * @param \DateTimeInterface $dateTime
     * @param Hexadecimal|null $node
     * @param int|null $clockSeq
     */
    public function fromDateTime($dateTime, $node = null, $clockSeq = null) : UuidInterface
    {
        $timeProvider = new FixedTimeProvider(new Time($dateTime->format('U'), $dateTime->format('u')));
        $timeGenerator = new DefaultTimeGenerator($this->nodeProvider, $this->timeConverter, $timeProvider);
        $nodeHex = $node ? $node->toString() : null;
        $bytes = $timeGenerator->generate($nodeHex, $clockSeq);
        return $this->uuidFromBytesAndVersion($bytes, 1);
    }
    /**
     * @param int|null $clockSeq
     */
    public function uuid1($node = null, $clockSeq = null) : UuidInterface
    {
        $bytes = $this->timeGenerator->generate($node, $clockSeq);
        return $this->uuidFromBytesAndVersion($bytes, 1);
    }
    /**
     * @param int $localDomain
     * @param IntegerObject|null $localIdentifier
     * @param Hexadecimal|null $node
     * @param int|null $clockSeq
     */
    public function uuid2($localDomain, $localIdentifier = null, $node = null, $clockSeq = null) : UuidInterface
    {
        $bytes = $this->dceSecurityGenerator->generate($localDomain, $localIdentifier, $node, $clockSeq);
        return $this->uuidFromBytesAndVersion($bytes, 2);
    }
    /**
     * @param string $name
     */
    public function uuid3($ns, $name) : UuidInterface
    {
        return $this->uuidFromNsAndName($ns, $name, 3, 'md5');
    }
    public function uuid4() : UuidInterface
    {
        $bytes = $this->randomGenerator->generate(16);
        return $this->uuidFromBytesAndVersion($bytes, 4);
    }
    /**
     * @param string $name
     */
    public function uuid5($ns, $name) : UuidInterface
    {
        return $this->uuidFromNsAndName($ns, $name, 5, 'sha1');
    }
    /**
     * @param Hexadecimal|null $node
     * @param int|null $clockSeq
     */
    public function uuid6($node = null, $clockSeq = null) : UuidInterface
    {
        $nodeHex = $node ? $node->toString() : null;
        $bytes = $this->timeGenerator->generate($nodeHex, $clockSeq);
        $v6 = $bytes[6] . $bytes[7] . $bytes[4] . $bytes[5] . $bytes[0] . $bytes[1] . $bytes[2] . $bytes[3];
        $v6 = bin2hex($v6);
        $v6Bytes = hex2bin(substr($v6, 1, 12) . '0' . substr($v6, -3));
        $v6Bytes .= substr($bytes, 8);
        return $this->uuidFromBytesAndVersion($v6Bytes, 6);
    }
    /**
     * @param string $bytes
     */
    public function uuid($bytes) : UuidInterface
    {
        return $this->uuidBuilder->build($this->codec, $bytes);
    }
    private function uuidFromNsAndName($ns, string $name, int $version, string $hashAlgorithm) : UuidInterface
    {
        if (!$ns instanceof UuidInterface) {
            $ns = $this->fromString($ns);
        }
        $bytes = $this->nameGenerator->generate($ns, $name, $hashAlgorithm);
        return $this->uuidFromBytesAndVersion(substr($bytes, 0, 16), $version);
    }
    private function uuidFromBytesAndVersion(string $bytes, int $version) : UuidInterface
    {
        $unpackedTime = unpack('n*', substr($bytes, 6, 2));
        $timeHi = (int) $unpackedTime[1];
        $timeHiAndVersion = pack('n*', BinaryUtils::applyVersion($timeHi, $version));
        $unpackedClockSeq = unpack('n*', substr($bytes, 8, 2));
        $clockSeqHi = (int) $unpackedClockSeq[1];
        $clockSeqHiAndReserved = pack('n*', BinaryUtils::applyVariant($clockSeqHi));
        $bytes = substr_replace($bytes, $timeHiAndVersion, 6, 2);
        $bytes = substr_replace($bytes, $clockSeqHiAndReserved, 8, 2);
        if ($this->isDefaultFeatureSet) {
            return LazyUuidFromString::fromBytes($bytes);
        }
        return $this->uuid($bytes);
    }
}
