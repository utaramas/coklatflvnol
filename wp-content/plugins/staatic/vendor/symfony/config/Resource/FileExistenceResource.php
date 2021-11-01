<?php

namespace Staatic\Vendor\Symfony\Component\Config\Resource;

class FileExistenceResource implements SelfCheckingResourceInterface
{
    private $resource;
    private $exists;
    public function __construct(string $resource)
    {
        $this->resource = $resource;
        $this->exists = \file_exists($resource);
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
        return \file_exists($this->resource) === $this->exists;
    }
}
