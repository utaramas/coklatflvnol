<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleHttp\Psr7;

use Staatic\Vendor\Psr\Http\Message\StreamInterface;
final class FnStream implements StreamInterface
{
    const SLOTS = ['__toString', 'close', 'detach', 'rewind', 'getSize', 'tell', 'eof', 'isSeekable', 'seek', 'isWritable', 'write', 'isReadable', 'read', 'getContents', 'getMetadata'];
    private $methods;
    public function __construct(array $methods)
    {
        $this->methods = $methods;
        foreach ($methods as $name => $fn) {
            $this->{'_fn_' . $name} = $fn;
        }
    }
    /**
     * @return void
     */
    public function __get(string $name)
    {
        throw new \BadMethodCallException(\str_replace('_fn_', '', $name) . '() is not implemented in the FnStream');
    }
    public function __destruct()
    {
        if (isset($this->_fn_close)) {
            \call_user_func($this->_fn_close);
        }
    }
    /**
     * @return void
     */
    public function __wakeup()
    {
        throw new \LogicException('FnStream should never be unserialized');
    }
    /**
     * @param StreamInterface $stream
     * @param mixed[] $methods
     */
    public static function decorate($stream, $methods)
    {
        foreach (\array_diff(self::SLOTS, \array_keys($methods)) as $diff) {
            $callable = [$stream, $diff];
            $methods[$diff] = $callable;
        }
        return new self($methods);
    }
    public function __toString() : string
    {
        try {
            return \call_user_func($this->_fn___toString);
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            \trigger_error(\sprintf('%s::__toString exception: %s', self::class, (string) $e), \E_USER_ERROR);
            return '';
        }
    }
    /**
     * @return void
     */
    public function close()
    {
        \call_user_func($this->_fn_close);
    }
    public function detach()
    {
        return \call_user_func($this->_fn_detach);
    }
    /**
     * @return int|null
     */
    public function getSize()
    {
        return \call_user_func($this->_fn_getSize);
    }
    public function tell() : int
    {
        return \call_user_func($this->_fn_tell);
    }
    public function eof() : bool
    {
        return \call_user_func($this->_fn_eof);
    }
    public function isSeekable() : bool
    {
        return \call_user_func($this->_fn_isSeekable);
    }
    /**
     * @return void
     */
    public function rewind()
    {
        \call_user_func($this->_fn_rewind);
    }
    /**
     * @return void
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        \call_user_func($this->_fn_seek, $offset, $whence);
    }
    public function isWritable() : bool
    {
        return \call_user_func($this->_fn_isWritable);
    }
    public function write($string) : int
    {
        return \call_user_func($this->_fn_write, $string);
    }
    public function isReadable() : bool
    {
        return \call_user_func($this->_fn_isReadable);
    }
    public function read($length) : string
    {
        return \call_user_func($this->_fn_read, $length);
    }
    public function getContents() : string
    {
        return \call_user_func($this->_fn_getContents);
    }
    public function getMetadata($key = null)
    {
        return \call_user_func($this->_fn_getMetadata, $key);
    }
}
