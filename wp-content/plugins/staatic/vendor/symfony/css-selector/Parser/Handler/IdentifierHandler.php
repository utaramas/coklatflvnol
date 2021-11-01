<?php

namespace Staatic\Vendor\Symfony\Component\CssSelector\Parser\Handler;

use Staatic\Vendor\Symfony\Component\CssSelector\Parser\Reader;
use Staatic\Vendor\Symfony\Component\CssSelector\Parser\Token;
use Staatic\Vendor\Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerEscaping;
use Staatic\Vendor\Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns;
use Staatic\Vendor\Symfony\Component\CssSelector\Parser\TokenStream;
class IdentifierHandler implements HandlerInterface
{
    private $patterns;
    private $escaping;
    public function __construct(TokenizerPatterns $patterns, TokenizerEscaping $escaping)
    {
        $this->patterns = $patterns;
        $this->escaping = $escaping;
    }
    /**
     * @param Reader $reader
     * @param TokenStream $stream
     */
    public function handle($reader, $stream) : bool
    {
        $match = $reader->findPattern($this->patterns->getIdentifierPattern());
        if (!$match) {
            return \false;
        }
        $value = $this->escaping->escapeUnicode($match[0]);
        $stream->push(new Token(Token::TYPE_IDENTIFIER, $value, $reader->getPosition()));
        $reader->moveForward(\strlen($match[0]));
        return \true;
    }
}
