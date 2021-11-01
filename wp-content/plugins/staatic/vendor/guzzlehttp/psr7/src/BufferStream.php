<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleHttp\Psr7;

use Staatic\Vendor\Psr\Http\Message\StreamInterface;
final class BufferStream implements StreamInterface
{
    private $hwm;
    private $buffer = '';
    public function __construct(int $hwm = 16384)
    {
        $this->hwm = $hwm;
    }
    public function __toString() : string
    {
        return $this->getContents();
    }
    public function getContents() : string
    {
        $buffer = $this->buffer;
        $this->buffer = '';
        return $buffer;
    }
    /**
     * @return void
     */
    public function close()
    {
        $this->buffer = '';
    }
    public function detach()
    {
        $this->close();
        return null;
    }
    /**
     * @return int|null
     */
    public function getSize()
    {
        return \strlen($this->buffer);
    }
    public function isReadable() : bool
    {
        return \true;
    }
    public function isWritable() : bool
    {
        return \true;
    }
    public function isSeekable() : bool
    {
        return \false;
    }
    /**
     * @return void
     */
    public function rewind()
    {
        $this->seek(0);
    }
    /**
     * @return void
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a BufferStream');
    }
    public function eof() : bool
    {
        return \strlen($this->buffer) === 0;
    }
    public function tell() : int
    {
        throw new \RuntimeException('Cannot determine the position of a BufferStream');
    }
    public function read($length) : string
    {
        $currentLength = \strlen($this->buffer);
        if ($length >= $currentLength) {
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            $result = \substr($this->buffer, 0, $length);
            $this->buffer = \substr($this->buffer, $length);
        }
        return $result;
    }
    public function write($string) : int
    {
        $this->buffer .= $string;
        if (\strlen($this->buffer) >= $this->hwm) {
            return 0;
        }
        return \strlen($string);
    }
    public function getMetadata($key = null)
    {
        if ($key === 'hwm') {
            return $this->hwm;
        }
        return $key ? null : [];
    }
}
