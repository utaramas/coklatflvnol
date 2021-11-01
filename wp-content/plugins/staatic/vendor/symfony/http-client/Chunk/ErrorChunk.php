<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Chunk;

use Staatic\Vendor\Symfony\Component\HttpClient\Exception\TimeoutException;
use Staatic\Vendor\Symfony\Component\HttpClient\Exception\TransportException;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ChunkInterface;
class ErrorChunk implements ChunkInterface
{
    private $didThrow = \false;
    private $offset;
    private $errorMessage;
    private $error;
    public function __construct(int $offset, $error)
    {
        $this->offset = $offset;
        if (\is_string($error)) {
            $this->errorMessage = $error;
        } else {
            $this->error = $error;
            $this->errorMessage = $error->getMessage();
        }
    }
    public function isTimeout() : bool
    {
        $this->didThrow = \true;
        if (null !== $this->error) {
            throw new TransportException($this->errorMessage, 0, $this->error);
        }
        return \true;
    }
    public function isFirst() : bool
    {
        $this->didThrow = \true;
        throw null !== $this->error ? new TransportException($this->errorMessage, 0, $this->error) : new TimeoutException($this->errorMessage);
    }
    public function isLast() : bool
    {
        $this->didThrow = \true;
        throw null !== $this->error ? new TransportException($this->errorMessage, 0, $this->error) : new TimeoutException($this->errorMessage);
    }
    /**
     * @return mixed[]|null
     */
    public function getInformationalStatus()
    {
        $this->didThrow = \true;
        throw null !== $this->error ? new TransportException($this->errorMessage, 0, $this->error) : new TimeoutException($this->errorMessage);
    }
    public function getContent() : string
    {
        $this->didThrow = \true;
        throw null !== $this->error ? new TransportException($this->errorMessage, 0, $this->error) : new TimeoutException($this->errorMessage);
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
        return $this->errorMessage;
    }
    /**
     * @param bool|null $didThrow
     */
    public function didThrow($didThrow = null) : bool
    {
        if (null !== $didThrow && $this->didThrow !== $didThrow) {
            return !($this->didThrow = $didThrow);
        }
        return $this->didThrow;
    }
    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize ' . __CLASS__);
    }
    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }
    public function __destruct()
    {
        if (!$this->didThrow) {
            $this->didThrow = \true;
            throw null !== $this->error ? new TransportException($this->errorMessage, 0, $this->error) : new TimeoutException($this->errorMessage);
        }
    }
}
