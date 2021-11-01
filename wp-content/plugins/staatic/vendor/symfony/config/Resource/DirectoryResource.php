<?php

namespace Staatic\Vendor\Symfony\Component\Config\Resource;

class DirectoryResource implements SelfCheckingResourceInterface
{
    private $resource;
    private $pattern;
    public function __construct(string $resource, string $pattern = null)
    {
        $this->resource = \realpath($resource) ?: (\file_exists($resource) ? $resource : \false);
        $this->pattern = $pattern;
        if (\false === $this->resource || !\is_dir($this->resource)) {
            throw new \InvalidArgumentException(\sprintf('The directory "%s" does not exist.', $resource));
        }
    }
    public function __toString() : string
    {
        return \md5(\serialize([$this->resource, $this->pattern]));
    }
    public function getResource() : string
    {
        return $this->resource;
    }
    /**
     * @return string|null
     */
    public function getPattern()
    {
        return $this->pattern;
    }
    /**
     * @param int $timestamp
     */
    public function isFresh($timestamp) : bool
    {
        if (!\is_dir($this->resource)) {
            return \false;
        }
        if ($timestamp < \filemtime($this->resource)) {
            return \false;
        }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->resource), \RecursiveIteratorIterator::SELF_FIRST) as $file) {
            if ($this->pattern && $file->isFile() && !\preg_match($this->pattern, $file->getBasename())) {
                continue;
            }
            if ($file->isDir() && '/..' === \substr($file, -3)) {
                continue;
            }
            try {
                $fileMTime = $file->getMTime();
            } catch (\RuntimeException $e) {
                continue;
            }
            if ($timestamp < $fileMTime) {
                return \false;
            }
        }
        return \true;
    }
}
