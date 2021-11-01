<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid;

use Staatic\Vendor\Ramsey\Uuid\Builder\BuilderCollection;
use Staatic\Vendor\Ramsey\Uuid\Builder\FallbackBuilder;
use Staatic\Vendor\Ramsey\Uuid\Builder\UuidBuilderInterface;
use Staatic\Vendor\Ramsey\Uuid\Codec\CodecInterface;
use Staatic\Vendor\Ramsey\Uuid\Codec\GuidStringCodec;
use Staatic\Vendor\Ramsey\Uuid\Codec\StringCodec;
use Staatic\Vendor\Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Staatic\Vendor\Ramsey\Uuid\Converter\NumberConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use Staatic\Vendor\Ramsey\Uuid\Converter\Time\PhpTimeConverter;
use Staatic\Vendor\Ramsey\Uuid\Converter\TimeConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\DceSecurityGenerator;
use Staatic\Vendor\Ramsey\Uuid\Generator\DceSecurityGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\NameGeneratorFactory;
use Staatic\Vendor\Ramsey\Uuid\Generator\NameGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\PeclUuidNameGenerator;
use Staatic\Vendor\Ramsey\Uuid\Generator\PeclUuidRandomGenerator;
use Staatic\Vendor\Ramsey\Uuid\Generator\PeclUuidTimeGenerator;
use Staatic\Vendor\Ramsey\Uuid\Generator\RandomGeneratorFactory;
use Staatic\Vendor\Ramsey\Uuid\Generator\RandomGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Generator\TimeGeneratorFactory;
use Staatic\Vendor\Ramsey\Uuid\Generator\TimeGeneratorInterface;
use Staatic\Vendor\Ramsey\Uuid\Guid\GuidBuilder;
use Staatic\Vendor\Ramsey\Uuid\Math\BrickMathCalculator;
use Staatic\Vendor\Ramsey\Uuid\Math\CalculatorInterface;
use Staatic\Vendor\Ramsey\Uuid\Nonstandard\UuidBuilder as NonstandardUuidBuilder;
use Staatic\Vendor\Ramsey\Uuid\Provider\Dce\SystemDceSecurityProvider;
use Staatic\Vendor\Ramsey\Uuid\Provider\DceSecurityProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Provider\Node\FallbackNodeProvider;
use Staatic\Vendor\Ramsey\Uuid\Provider\Node\NodeProviderCollection;
use Staatic\Vendor\Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use Staatic\Vendor\Ramsey\Uuid\Provider\Node\SystemNodeProvider;
use Staatic\Vendor\Ramsey\Uuid\Provider\NodeProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Provider\Time\SystemTimeProvider;
use Staatic\Vendor\Ramsey\Uuid\Provider\TimeProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\UuidBuilder as Rfc4122UuidBuilder;
use Staatic\Vendor\Ramsey\Uuid\Validator\GenericValidator;
use Staatic\Vendor\Ramsey\Uuid\Validator\ValidatorInterface;
use const PHP_INT_SIZE;
class FeatureSet
{
    private $disableBigNumber = \false;
    private $disable64Bit = \false;
    private $ignoreSystemNode = \false;
    private $enablePecl = \false;
    private $builder;
    private $codec;
    private $dceSecurityGenerator;
    private $nameGenerator;
    private $nodeProvider;
    private $numberConverter;
    private $timeConverter;
    private $randomGenerator;
    private $timeGenerator;
    private $timeProvider;
    private $validator;
    private $calculator;
    public function __construct(bool $useGuids = \false, bool $force32Bit = \false, bool $forceNoBigNumber = \false, bool $ignoreSystemNode = \false, bool $enablePecl = \false)
    {
        $this->disableBigNumber = $forceNoBigNumber;
        $this->disable64Bit = $force32Bit;
        $this->ignoreSystemNode = $ignoreSystemNode;
        $this->enablePecl = $enablePecl;
        $this->setCalculator(new BrickMathCalculator());
        $this->builder = $this->buildUuidBuilder($useGuids);
        $this->codec = $this->buildCodec($useGuids);
        $this->nodeProvider = $this->buildNodeProvider();
        $this->nameGenerator = $this->buildNameGenerator();
        $this->randomGenerator = $this->buildRandomGenerator();
        $this->setTimeProvider(new SystemTimeProvider());
        $this->setDceSecurityProvider(new SystemDceSecurityProvider());
        $this->validator = new GenericValidator();
    }
    public function getBuilder() : UuidBuilderInterface
    {
        return $this->builder;
    }
    public function getCalculator() : CalculatorInterface
    {
        return $this->calculator;
    }
    public function getCodec() : CodecInterface
    {
        return $this->codec;
    }
    public function getDceSecurityGenerator() : DceSecurityGeneratorInterface
    {
        return $this->dceSecurityGenerator;
    }
    public function getNameGenerator() : NameGeneratorInterface
    {
        return $this->nameGenerator;
    }
    public function getNodeProvider() : NodeProviderInterface
    {
        return $this->nodeProvider;
    }
    public function getNumberConverter() : NumberConverterInterface
    {
        return $this->numberConverter;
    }
    public function getRandomGenerator() : RandomGeneratorInterface
    {
        return $this->randomGenerator;
    }
    public function getTimeConverter() : TimeConverterInterface
    {
        return $this->timeConverter;
    }
    public function getTimeGenerator() : TimeGeneratorInterface
    {
        return $this->timeGenerator;
    }
    public function getValidator() : ValidatorInterface
    {
        return $this->validator;
    }
    /**
     * @param CalculatorInterface $calculator
     * @return void
     */
    public function setCalculator($calculator)
    {
        $this->calculator = $calculator;
        $this->numberConverter = $this->buildNumberConverter($calculator);
        $this->timeConverter = $this->buildTimeConverter($calculator);
        if (isset($this->timeProvider)) {
            $this->timeGenerator = $this->buildTimeGenerator($this->timeProvider);
        }
    }
    /**
     * @param DceSecurityProviderInterface $dceSecurityProvider
     * @return void
     */
    public function setDceSecurityProvider($dceSecurityProvider)
    {
        $this->dceSecurityGenerator = $this->buildDceSecurityGenerator($dceSecurityProvider);
    }
    /**
     * @param NodeProviderInterface $nodeProvider
     * @return void
     */
    public function setNodeProvider($nodeProvider)
    {
        $this->nodeProvider = $nodeProvider;
        $this->timeGenerator = $this->buildTimeGenerator($this->timeProvider);
    }
    /**
     * @param TimeProviderInterface $timeProvider
     * @return void
     */
    public function setTimeProvider($timeProvider)
    {
        $this->timeProvider = $timeProvider;
        $this->timeGenerator = $this->buildTimeGenerator($timeProvider);
    }
    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }
    private function buildCodec(bool $useGuids = \false) : CodecInterface
    {
        if ($useGuids) {
            return new GuidStringCodec($this->builder);
        }
        return new StringCodec($this->builder);
    }
    private function buildDceSecurityGenerator(DceSecurityProviderInterface $dceSecurityProvider) : DceSecurityGeneratorInterface
    {
        return new DceSecurityGenerator($this->numberConverter, $this->timeGenerator, $dceSecurityProvider);
    }
    private function buildNodeProvider() : NodeProviderInterface
    {
        if ($this->ignoreSystemNode) {
            return new RandomNodeProvider();
        }
        return new FallbackNodeProvider(new NodeProviderCollection([new SystemNodeProvider(), new RandomNodeProvider()]));
    }
    private function buildNumberConverter(CalculatorInterface $calculator) : NumberConverterInterface
    {
        return new GenericNumberConverter($calculator);
    }
    private function buildRandomGenerator() : RandomGeneratorInterface
    {
        if ($this->enablePecl) {
            return new PeclUuidRandomGenerator();
        }
        return (new RandomGeneratorFactory())->getGenerator();
    }
    private function buildTimeGenerator(TimeProviderInterface $timeProvider) : TimeGeneratorInterface
    {
        if ($this->enablePecl) {
            return new PeclUuidTimeGenerator();
        }
        return (new TimeGeneratorFactory($this->nodeProvider, $this->timeConverter, $timeProvider))->getGenerator();
    }
    private function buildNameGenerator() : NameGeneratorInterface
    {
        if ($this->enablePecl) {
            return new PeclUuidNameGenerator();
        }
        return (new NameGeneratorFactory())->getGenerator();
    }
    private function buildTimeConverter(CalculatorInterface $calculator) : TimeConverterInterface
    {
        $genericConverter = new GenericTimeConverter($calculator);
        if ($this->is64BitSystem()) {
            return new PhpTimeConverter($calculator, $genericConverter);
        }
        return $genericConverter;
    }
    private function buildUuidBuilder(bool $useGuids = \false) : UuidBuilderInterface
    {
        if ($useGuids) {
            return new GuidBuilder($this->numberConverter, $this->timeConverter);
        }
        return new FallbackBuilder(new BuilderCollection([new Rfc4122UuidBuilder($this->numberConverter, $this->timeConverter), new NonstandardUuidBuilder($this->numberConverter, $this->timeConverter)]));
    }
    private function is64BitSystem() : bool
    {
        return PHP_INT_SIZE === 8 && !$this->disable64Bit;
    }
}
