<?php

namespace Staatic\Vendor\AsyncAws\Core\Stream;

final class RewindableStream implements RequestStream
{
    private $content;
    private $fallback;
    private function __construct(RequestStream $content)
    {
        $this->content = $content;
    }
    /**
     * @param RequestStream $content
     */
    public static function create($content) : RewindableStream
    {
        if ($content instanceof self) {
            return $content;
        }
        return new self($content);
    }
    /**
     * @return int|null
     */
    public function length()
    {
        if (null !== $this->fallback) {
            return $this->fallback->length();
        }
        return $this->content->length();
    }
    public function stringify() : string
    {
        if (null !== $this->fallback) {
            return $this->fallback->stringify();
        }
        return \implode('', \iterator_to_array($this));
    }
    public function getIterator() : \Traversable
    {
        if (null !== $this->fallback) {
            yield from $this->fallback;
            return;
        }
        $resource = \fopen('php://temp', 'r+b');
        $this->fallback = ResourceStream::create($resource);
        foreach ($this->content as $chunk) {
            \fwrite($resource, $chunk);
            (yield $chunk);
        }
    }
    /**
     * @param string $algo
     * @param bool $raw
     */
    public function hash($algo = 'sha256', $raw = \false) : string
    {
        if (null !== $this->fallback) {
            return $this->fallback->hash($algo, $raw);
        }
        $ctx = \hash_init($algo);
        foreach ($this as $chunk) {
            \hash_update($ctx, $chunk);
        }
        return \hash_final($ctx, $raw);
    }
    /**
     * @return void
     */
    public function read()
    {
        foreach ($this as $chunk) {
        }
    }
}
