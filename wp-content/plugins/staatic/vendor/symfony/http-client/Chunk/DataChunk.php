<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Chunk;

use Staatic\Vendor\Symfony\Contracts\HttpClient\ChunkInterface;
class DataChunk implements ChunkInterface
{
    private $offset = 0;
    private $content = '';
    public function __construct(int $offset = 0, string $content = '')
    {
        $this->offset = $offset;
        $this->content = $content;
    }
    public function isTimeout() : bool
    {
        return \false;
    }
    public function isFirst() : bool
    {
        return \false;
    }
    public function isLast() : bool
    {
        return \false;
    }
    /**
     * @return mixed[]|null
     */
    public function getInformationalStatus()
    {
        return null;
    }
    public function getContent() : string
    {
        return $this->content;
    }
    public function getOffset() : int
    {
        return $this->offset;
    }
    /**
     * @return string|null
     */
    public function getError()
    {
        return null;
    }
}
