<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Response;

use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
class StreamWrapper
{
    public $context;
    private $client;
    private $response;
    private $content;
    private $handle;
    private $blocking = \true;
    private $timeout;
    private $eof = \false;
    private $offset = 0;
    /**
     * @param ResponseInterface $response
     * @param HttpClientInterface|null $client
     */
    public static function createResource($response, $client = null)
    {
        if ($response instanceof StreamableInterface) {
            $stack = \debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT | \DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            if ($response !== ($stack[1]['object'] ?? null)) {
                return $response->toStream(\false);
            }
        }
        if (null === $client && !\method_exists($response, 'stream')) {
            throw new \InvalidArgumentException(\sprintf('Providing a client to "%s()" is required when the response doesn\'t have any "stream()" method.', __CLASS__));
        }
        if (\false === \stream_wrapper_register('symfony', __CLASS__, \STREAM_IS_URL)) {
            throw new \RuntimeException(\error_get_last()['message'] ?? 'Registering the "symfony" stream wrapper failed.');
        }
        try {
            $context = ['client' => $client ?? $response, 'response' => $response];
            return \fopen('symfony://' . $response->getInfo('url'), 'r', \false, \stream_context_create(['symfony' => $context])) ?: null;
        } finally {
            \stream_wrapper_unregister('symfony');
        }
    }
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }
    /**
     * @return void
     */
    public function bindHandles(&$handle, &$content)
    {
        $this->handle =& $handle;
        $this->content =& $content;
    }
    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     */
    public function stream_open($path, $mode, $options) : bool
    {
        if ('r' !== $mode) {
            if ($options & \STREAM_REPORT_ERRORS) {
                \trigger_error(\sprintf('Invalid mode "%s": only "r" is supported.', $mode), \E_USER_WARNING);
            }
            return \false;
        }
        $context = \stream_context_get_options($this->context)['symfony'] ?? null;
        $this->client = $context['client'] ?? null;
        $this->response = $context['response'] ?? null;
        $this->context = null;
        if (null !== $this->client && null !== $this->response) {
            return \true;
        }
        if ($options & \STREAM_REPORT_ERRORS) {
            \trigger_error('Missing options "client" or "response" in "symfony" stream context.', \E_USER_WARNING);
        }
        return \false;
    }
    /**
     * @param int $count
     */
    public function stream_read($count)
    {
        if (\is_resource($this->content)) {
            foreach ($this->client->stream([$this->response], 0) as $chunk) {
                try {
                    if (!$chunk->isTimeout() && $chunk->isFirst()) {
                        $this->response->getStatusCode();
                    }
                } catch (ExceptionInterface $e) {
                    \trigger_error($e->getMessage(), \E_USER_WARNING);
                    return \false;
                }
            }
            if (0 !== \fseek($this->content, $this->offset)) {
                return \false;
            }
            if ('' !== ($data = \fread($this->content, $count))) {
                \fseek($this->content, 0, \SEEK_END);
                $this->offset += \strlen($data);
                return $data;
            }
        }
        if (\is_string($this->content)) {
            if (\strlen($this->content) <= $count) {
                $data = $this->content;
                $this->content = null;
            } else {
                $data = \substr($this->content, 0, $count);
                $this->content = \substr($this->content, $count);
            }
            $this->offset += \strlen($data);
            return $data;
        }
        foreach ($this->client->stream([$this->response], $this->blocking ? $this->timeout : 0) as $chunk) {
            try {
                $this->eof = \true;
                $this->eof = !$chunk->isTimeout();
                $this->eof = $chunk->isLast();
                if ($chunk->isFirst()) {
                    $this->response->getStatusCode();
                }
                if ('' !== ($data = $chunk->getContent())) {
                    if (\strlen($data) > $count) {
                        if (null === $this->content) {
                            $this->content = \substr($data, $count);
                        }
                        $data = \substr($data, 0, $count);
                    }
                    $this->offset += \strlen($data);
                    return $data;
                }
            } catch (ExceptionInterface $e) {
                \trigger_error($e->getMessage(), \E_USER_WARNING);
                return \false;
            }
        }
        return '';
    }
    /**
     * @param int $option
     * @param int $arg1
     * @param int|null $arg2
     */
    public function stream_set_option($option, $arg1, $arg2) : bool
    {
        if (\STREAM_OPTION_BLOCKING === $option) {
            $this->blocking = (bool) $arg1;
        } elseif (\STREAM_OPTION_READ_TIMEOUT === $option) {
            $this->timeout = $arg1 + $arg2 / 1000000.0;
        } else {
            return \false;
        }
        return \true;
    }
    public function stream_tell() : int
    {
        return $this->offset;
    }
    public function stream_eof() : bool
    {
        return $this->eof && !\is_string($this->content);
    }
    /**
     * @param int $offset
     * @param int $whence
     */
    public function stream_seek($offset, $whence = \SEEK_SET) : bool
    {
        if (!\is_resource($this->content) || 0 !== \fseek($this->content, 0, \SEEK_END)) {
            return \false;
        }
        $size = \ftell($this->content);
        if (\SEEK_CUR === $whence) {
            $offset += $this->offset;
        }
        if (\SEEK_END === $whence || $size < $offset) {
            foreach ($this->client->stream([$this->response]) as $chunk) {
                try {
                    if ($chunk->isFirst()) {
                        $this->response->getStatusCode();
                    }
                    $size += \strlen($chunk->getContent());
                    if (\SEEK_END !== $whence && $offset <= $size) {
                        break;
                    }
                } catch (ExceptionInterface $e) {
                    \trigger_error($e->getMessage(), \E_USER_WARNING);
                    return \false;
                }
            }
            if (\SEEK_END === $whence) {
                $offset += $size;
            }
        }
        if (0 <= $offset && $offset <= $size) {
            $this->eof = \false;
            $this->offset = $offset;
            return \true;
        }
        return \false;
    }
    /**
     * @param int $castAs
     */
    public function stream_cast($castAs)
    {
        if (\STREAM_CAST_FOR_SELECT === $castAs) {
            $this->response->getHeaders(\false);
            return (\is_callable($this->handle) ? ($this->handle)() : $this->handle) ?? \false;
        }
        return \false;
    }
    public function stream_stat() : array
    {
        try {
            $headers = $this->response->getHeaders(\false);
        } catch (ExceptionInterface $e) {
            \trigger_error($e->getMessage(), \E_USER_WARNING);
            $headers = [];
        }
        return ['dev' => 0, 'ino' => 0, 'mode' => 33060, 'nlink' => 0, 'uid' => 0, 'gid' => 0, 'rdev' => 0, 'size' => (int) ($headers['content-length'][0] ?? -1), 'atime' => 0, 'mtime' => \strtotime($headers['last-modified'][0] ?? '') ?: 0, 'ctime' => 0, 'blksize' => 0, 'blocks' => 0];
    }
    private function __construct()
    {
    }
}
