<?php

namespace Staatic\Vendor\Symfony\Component\Config\Resource;

class FileResource implements SelfCheckingResourceInterface
{
    private $resource;
    public function __construct(string $resource)
    {
        $this->resource = \realpath($resource) ?: (\file_exists($resource) ? $resource : \false);
        if (\false === $this->resource) {
            throw new \InvalidArgumentException(\sprintf('The file "%s" does not exist.', $resource));
        }
    }
    public function __toString() : string
    {
        return $this->resource;
    }
    public function getResource() : string
    {
        return $this->resource;
    }
    /**
     * @param int $timestamp
     */
    public function isFresh($timestamp) : bool
    {
        return \false !== ($filemtime = @\filemtime($this->resource)) && $filemtime <= $timestamp;
    }
}
