<?php

declare (strict_types=1);
namespace Staatic\Vendor\Brick\Math\Internal;

use Staatic\Vendor\Brick\Math\Internal\Calculator\GmpCalculator;
use Staatic\Vendor\Brick\Math\Internal\Calculator\BcMathCalculator;
use Staatic\Vendor\Brick\Math\Internal\Calculator\NativeCalculator;
use Staatic\Vendor\Brick\Math\Exception\RoundingNecessaryException;
use Staatic\Vendor\Brick\Math\RoundingMode;
abstract class Calculator
{
    const MAX_POWER = 1000000;
    const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';
    private static $instance;
    /**
     * @param \Staatic\Vendor\Brick\Math\Internal\Calculator|null $calculator
     * @return void
     */
    public static final function set($calculator)
    {
        self::$instance = $calculator;
    }
    public static final function get() : Calculator
    {
        if (self::$instance === null) {
            self::$instance = self::detect();
        }
        return self::$instance;
    }
    private static function detect() : Calculator
    {
        if (\extension_loaded('gmp')) {
            return new GmpCalculator();
        }
        if (\extension_loaded('bcmath')) {
            return new BcMathCalculator();
        }
        return new NativeCalculator();
    }
    /**
     * @param string $a
     * @param string $b
     */
    protected final function init($a, $b) : array
    {
        return [$aNeg = $a[0] === '-', $bNeg = $b[0] === '-', $aNeg ? \substr($a, 1) : $a, $bNeg ? \substr($b, 1) : $b];
    }
    /**
     * @param string $n
     */
    public final function abs($n) : string
    {
        return $n[0] === '-' ? \substr($n, 1) : $n;
    }
    /**
     * @param string $n
     */
    public final function neg($n) : string
    {
        if ($n === '0') {
            return '0';
        }
        if ($n[0] === '-') {
            return \substr($n, 1);
        }
        return '-' . $n;
    }
    /**
     * @param string $a
     * @param string $b
     */
    public final function cmp($a, $b) : int
    {
        list($aNeg, $bNeg, $aDig, $bDig) = $this->init($a, $b);
        if ($aNeg && !$bNeg) {
            return -1;
        }
        if ($bNeg && !$aNeg) {
            return 1;
        }
        $aLen = \strlen($aDig);
        $bLen = \strlen($bDig);
        if ($aLen < $bLen) {
            $result = -1;
        } elseif ($aLen > $bLen) {
            $result = 1;
        } else {
            $result = $aDig <=> $bDig;
        }
        return $aNeg ? -$result : $result;
    }
    /**
     * @param string $a
     * @param string $b
     */
    public abstract function add($a, $b) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public abstract function sub($a, $b) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public abstract function mul($a, $b) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public abstract function divQ($a, $b) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public abstract function divR($a, $b) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public abstract function divQR($a, $b) : array;
    /**
     * @param string $a
     * @param int $e
     */
    public abstract function pow($a, $e) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public function mod($a, $b) : string
    {
        return $this->divR($this->add($this->divR($a, $b), $b), $b);
    }
    /**
     * @param string $x
     * @param string $m
     * @return string|null
     */
    public function modInverse($x, $m)
    {
        if ($m === '1') {
            return '0';
        }
        $modVal = $x;
        if ($x[0] === '-' || $this->cmp($this->abs($x), $m) >= 0) {
            $modVal = $this->mod($x, $m);
        }
        $x = '0';
        $y = '0';
        $g = $this->gcdExtended($modVal, $m, $x, $y);
        if ($g !== '1') {
            return null;
        }
        return $this->mod($this->add($this->mod($x, $m), $m), $m);
    }
    /**
     * @param string $base
     * @param string $exp
     * @param string $mod
     */
    public abstract function modPow($base, $exp, $mod) : string;
    /**
     * @param string $a
     * @param string $b
     */
    public function gcd($a, $b) : string
    {
        if ($a === '0') {
            return $this->abs($b);
        }
        if ($b === '0') {
            return $this->abs($a);
        }
        return $this->gcd($b, $this->divR($a, $b));
    }
    private function gcdExtended(string $a, string $b, string &$x, string &$y) : string
    {
        if ($a === '0') {
            $x = '0';
            $y = '1';
            return $b;
        }
        $x1 = '0';
        $y1 = '0';
        $gcd = $this->gcdExtended($this->mod($b, $a), $a, $x1, $y1);
        $x = $this->sub($y1, $this->mul($this->divQ($b, $a), $x1));
        $y = $x1;
        return $gcd;
    }
    /**
     * @param string $n
     */
    public abstract function sqrt($n) : string;
    /**
     * @param string $number
     * @param int $base
     */
    public function fromBase($number, $base) : string
    {
        return $this->fromArbitraryBase(\strtolower($number), self::ALPHABET, $base);
    }
    /**
     * @param string $number
     * @param int $base
     */
    public function toBase($number, $base) : string
    {
        $negative = $number[0] === '-';
        if ($negative) {
            $number = \substr($number, 1);
        }
        $number = $this->toArbitraryBase($number, self::ALPHABET, $base);
        if ($negative) {
            return '-' . $number;
        }
        return $number;
    }
    /**
     * @param string $number
     * @param string $alphabet
     * @param int $base
     */
    public final function fromArbitraryBase($number, $alphabet, $base) : string
    {
        $number = \ltrim($number, $alphabet[0]);
        if ($number === '') {
            return '0';
        }
        if ($number === $alphabet[1]) {
            return '1';
        }
        $result = '0';
        $power = '1';
        $base = (string) $base;
        for ($i = \strlen($number) - 1; $i >= 0; $i--) {
            $index = \strpos($alphabet, $number[$i]);
            if ($index !== 0) {
                $result = $this->add($result, $index === 1 ? $power : $this->mul($power, (string) $index));
            }
            if ($i !== 0) {
                $power = $this->mul($power, $base);
            }
        }
        return $result;
    }
    /**
     * @param string $number
     * @param string $alphabet
     * @param int $base
     */
    public final function toArbitraryBase($number, $alphabet, $base) : string
    {
        if ($number === '0') {
            return $alphabet[0];
        }
        $base = (string) $base;
        $result = '';
        while ($number !== '0') {
            list($number, $remainder) = $this->divQR($number, $base);
            $remainder = (int) $remainder;
            $result .= $alphabet[$remainder];
        }
        return \strrev($result);
    }
    /**
     * @param string $a
     * @param string $b
     * @param int $roundingMode
     */
    public final function divRound($a, $b, $roundingMode) : string
    {
        list($quotient, $remainder) = $this->divQR($a, $b);
        $hasDiscardedFraction = $remainder !== '0';
        $isPositiveOrZero = ($a[0] === '-') === ($b[0] === '-');
        $discardedFractionSign = function () use($remainder, $b) : int {
            $r = $this->abs($this->mul($remainder, '2'));
            $b = $this->abs($b);
            return $this->cmp($r, $b);
        };
        $increment = \false;
        switch ($roundingMode) {
            case RoundingMode::UNNECESSARY:
                if ($hasDiscardedFraction) {
                    throw RoundingNecessaryException::roundingNecessary();
                }
                break;
            case RoundingMode::UP:
                $increment = $hasDiscardedFraction;
                break;
            case RoundingMode::DOWN:
                break;
            case RoundingMode::CEILING:
                $increment = $hasDiscardedFraction && $isPositiveOrZero;
                break;
            case RoundingMode::FLOOR:
                $increment = $hasDiscardedFraction && !$isPositiveOrZero;
                break;
            case RoundingMode::HALF_UP:
                $increment = $discardedFractionSign() >= 0;
                break;
            case RoundingMode::HALF_DOWN:
                $increment = $discardedFractionSign() > 0;
                break;
            case RoundingMode::HALF_CEILING:
                $increment = $isPositiveOrZero ? $discardedFractionSign() >= 0 : $discardedFractionSign() > 0;
                break;
            case RoundingMode::HALF_FLOOR:
                $increment = $isPositiveOrZero ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;
            case RoundingMode::HALF_EVEN:
                $lastDigit = (int) $quotient[strlen($quotient) - 1];
                $lastDigitIsEven = $lastDigit % 2 === 0;
                $increment = $lastDigitIsEven ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;
            default:
                throw new \InvalidArgumentException('Invalid rounding mode.');
        }
        if ($increment) {
            return $this->add($quotient, $isPositiveOrZero ? '1' : '-1');
        }
        return $quotient;
    }
    /**
     * @param string $a
     * @param string $b
     */
    public function and($a, $b) : string
    {
        return $this->bitwise('and', $a, $b);
    }
    /**
     * @param string $a
     * @param string $b
     */
    public function or($a, $b) : string
    {
        return $this->bitwise('or', $a, $b);
    }
    /**
     * @param string $a
     * @param string $b
     */
    public function xor($a, $b) : string
    {
        return $this->bitwise('xor', $a, $b);
    }
    private function bitwise(string $operator, string $a, string $b) : string
    {
        list($aNeg, $bNeg, $aDig, $bDig) = $this->init($a, $b);
        $aBin = $this->toBinary($aDig);
        $bBin = $this->toBinary($bDig);
        $aLen = \strlen($aBin);
        $bLen = \strlen($bBin);
        if ($aLen > $bLen) {
            $bBin = \str_repeat("\0", $aLen - $bLen) . $bBin;
        } elseif ($bLen > $aLen) {
            $aBin = \str_repeat("\0", $bLen - $aLen) . $aBin;
        }
        if ($aNeg) {
            $aBin = $this->twosComplement($aBin);
        }
        if ($bNeg) {
            $bBin = $this->twosComplement($bBin);
        }
        switch ($operator) {
            case 'and':
                $value = $aBin & $bBin;
                $negative = ($aNeg and $bNeg);
                break;
            case 'or':
                $value = $aBin | $bBin;
                $negative = ($aNeg or $bNeg);
                break;
            case 'xor':
                $value = $aBin ^ $bBin;
                $negative = ($aNeg xor $bNeg);
                break;
            default:
                throw new \InvalidArgumentException('Invalid bitwise operator.');
        }
        if ($negative) {
            $value = $this->twosComplement($value);
        }
        $result = $this->toDecimal($value);
        return $negative ? $this->neg($result) : $result;
    }
    private function twosComplement(string $number) : string
    {
        $xor = \str_repeat("�", \strlen($number));
        $number ^= $xor;
        for ($i = \strlen($number) - 1; $i >= 0; $i--) {
            $byte = \ord($number[$i]);
            if (++$byte !== 256) {
                $number[$i] = \chr($byte);
                break;
            }
            $number[$i] = "\0";
            if ($i === 0) {
                $number = "\1" . $number;
            }
        }
        return $number;
    }
    private function toBinary(string $number) : string
    {
        $result = '';
        while ($number !== '0') {
            list($number, $remainder) = $this->divQR($number, '256');
            $result .= \chr((int) $remainder);
        }
        return \strrev($result);
    }
    private function toDecimal(string $bytes) : string
    {
        $result = '0';
        $power = '1';
        for ($i = \strlen($bytes) - 1; $i >= 0; $i--) {
            $index = \ord($bytes[$i]);
            if ($index !== 0) {
                $result = $this->add($result, $index === 1 ? $power : $this->mul($power, (string) $index));
            }
            if ($i !== 0) {
                $power = $this->mul($power, '256');
            }
        }
        return $result;
    }
}
