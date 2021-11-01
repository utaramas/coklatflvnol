<?php

namespace Staatic\Vendor\Symfony\Component\CssSelector\Exception;

use Staatic\Vendor\Symfony\Component\CssSelector\Parser\Token;
class SyntaxErrorException extends ParseException
{
    /**
     * @param string $expectedValue
     * @param Token $foundToken
     */
    public static function unexpectedToken($expectedValue, $foundToken)
    {
        return new self(\sprintf('Expected %s, but %s found.', $expectedValue, $foundToken));
    }
    /**
     * @param string $pseudoElement
     * @param string $unexpectedLocation
     */
    public static function pseudoElementFound($pseudoElement, $unexpectedLocation)
    {
        return new self(\sprintf('Unexpected pseudo-element "::%s" found %s.', $pseudoElement, $unexpectedLocation));
    }
    /**
     * @param int $position
     */
    public static function unclosedString($position)
    {
        return new self(\sprintf('Unclosed/invalid string at %s.', $position));
    }
    public static function nestedNot()
    {
        return new self('Got nested ::not().');
    }
    public static function stringAsFunctionArgument()
    {
        return new self('String not allowed as function argument.');
    }
}
