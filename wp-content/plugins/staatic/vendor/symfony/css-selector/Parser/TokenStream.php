<?php

namespace Staatic\Vendor\Symfony\Component\CssSelector\Parser;

use Staatic\Vendor\Symfony\Component\CssSelector\Exception\InternalErrorException;
use Staatic\Vendor\Symfony\Component\CssSelector\Exception\SyntaxErrorException;
class TokenStream
{
    private $tokens = [];
    private $used = [];
    private $cursor = 0;
    private $peeked;
    private $peeking = \false;
    /**
     * @param Token $token
     */
    public function push($token) : self
    {
        $this->tokens[] = $token;
        return $this;
    }
    public function freeze() : self
    {
        return $this;
    }
    public function getNext() : Token
    {
        if ($this->peeking) {
            $this->peeking = \false;
            $this->used[] = $this->peeked;
            return $this->peeked;
        }
        if (!isset($this->tokens[$this->cursor])) {
            throw new InternalErrorException('Unexpected token stream end.');
        }
        return $this->tokens[$this->cursor++];
    }
    public function getPeek() : Token
    {
        if (!$this->peeking) {
            $this->peeked = $this->getNext();
            $this->peeking = \true;
        }
        return $this->peeked;
    }
    public function getUsed() : array
    {
        return $this->used;
    }
    public function getNextIdentifier() : string
    {
        $next = $this->getNext();
        if (!$next->isIdentifier()) {
            throw SyntaxErrorException::unexpectedToken('identifier', $next);
        }
        return $next->getValue();
    }
    /**
     * @return string|null
     */
    public function getNextIdentifierOrStar()
    {
        $next = $this->getNext();
        if ($next->isIdentifier()) {
            return $next->getValue();
        }
        if ($next->isDelimiter(['*'])) {
            return null;
        }
        throw SyntaxErrorException::unexpectedToken('identifier or "*"', $next);
    }
    public function skipWhitespace()
    {
        $peek = $this->getPeek();
        if ($peek->isWhitespace()) {
            $this->getNext();
        }
    }
}
