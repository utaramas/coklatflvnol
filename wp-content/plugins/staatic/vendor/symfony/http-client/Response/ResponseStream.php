<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Response;

use Staatic\Vendor\Symfony\Contracts\HttpClient\ChunkInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
final class ResponseStream implements ResponseStreamInterface
{
    private $generator;
    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
    }
    public function key() : ResponseInterface
    {
        return $this->generator->key();
    }
    public function current() : ChunkInterface
    {
        return $this->generator->current();
    }
    /**
     * @return void
     */
    public function next()
    {
        $this->generator->next();
    }
    /**
     * @return void
     */
    public function rewind()
    {
        $this->generator->rewind();
    }
    public function valid() : bool
    {
        return $this->generator->valid();
    }
}
