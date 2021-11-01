<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Response;

use Staatic\Vendor\Symfony\Component\HttpClient\Chunk\DataChunk;
use Staatic\Vendor\Symfony\Component\HttpClient\Chunk\LastChunk;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ChunkInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
final class AsyncContext
{
    private $passthru;
    private $client;
    private $response;
    private $info = [];
    private $content;
    private $offset;
    public function __construct(&$passthru, HttpClientInterface $client, ResponseInterface &$response, array &$info, $content, int $offset)
    {
        $this->passthru =& $passthru;
        $this->client = $client;
        $this->response =& $response;
        $this->info =& $info;
        $this->content = $content;
        $this->offset = $offset;
    }
    public function getStatusCode() : int
    {
        return $this->response->getInfo('http_code');
    }
    public function getHeaders() : array
    {
        $headers = [];
        foreach ($this->response->getInfo('response_headers') as $h) {
            if (11 <= \strlen($h) && '/' === $h[4] && \preg_match('#^HTTP/\\d+(?:\\.\\d+)? ([123456789]\\d\\d)(?: |$)#', $h, $m)) {
                $headers = [];
            } elseif (2 === \count($m = \explode(':', $h, 2))) {
                $headers[\strtolower($m[0])][] = \ltrim($m[1]);
            }
        }
        return $headers;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function createChunk(string $data) : ChunkInterface
    {
        return new DataChunk($this->offset, $data);
    }
    /**
     * @return void
     */
    public function pause(float $duration)
    {
        if (\is_callable($pause = $this->response->getInfo('pause_handler'))) {
            $pause($duration);
        } elseif (0 < $duration) {
            \usleep(1000000.0 * $duration);
        }
    }
    public function cancel() : ChunkInterface
    {
        $this->info['canceled'] = \true;
        $this->info['error'] = 'Response has been canceled.';
        $this->response->cancel();
        return new LastChunk();
    }
    public function getInfo(string $type = null)
    {
        if (null !== $type) {
            return $this->info[$type] ?? $this->response->getInfo($type);
        }
        return $this->info + $this->response->getInfo();
    }
    public function setInfo(string $type, $value) : self
    {
        if ('canceled' === $type && $value !== $this->info['canceled']) {
            throw new \LogicException('You cannot set the "canceled" info directly.');
        }
        if (null === $value) {
            unset($this->info[$type]);
        } else {
            $this->info[$type] = $value;
        }
        return $this;
    }
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }
    public function replaceRequest(string $method, string $url, array $options = []) : ResponseInterface
    {
        $this->info['previous_info'][] = $this->response->getInfo();
        if (null !== ($onProgress = $options['on_progress'] ?? null)) {
            $thisInfo =& $this->info;
            $options['on_progress'] = static function (int $dlNow, int $dlSize, array $info) use(&$thisInfo, $onProgress) {
                $onProgress($dlNow, $dlSize, $thisInfo + $info);
            };
        }
        return $this->response = $this->client->request($method, $url, ['buffer' => \false] + $options);
    }
    public function replaceResponse(ResponseInterface $response) : ResponseInterface
    {
        $this->info['previous_info'][] = $this->response->getInfo();
        return $this->response = $response;
    }
    /**
     * @return void
     */
    public function passthru(callable $passthru = null)
    {
        $this->passthru = $passthru;
    }
}
