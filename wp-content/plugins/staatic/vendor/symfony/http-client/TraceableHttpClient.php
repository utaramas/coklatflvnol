<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\ResponseStream;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\TraceableResponse;
use Staatic\Vendor\Symfony\Component\Stopwatch\Stopwatch;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Staatic\Vendor\Symfony\Contracts\Service\ResetInterface;
final class TraceableHttpClient implements HttpClientInterface, ResetInterface, LoggerAwareInterface
{
    private $client;
    private $tracedRequests = [];
    private $stopwatch;
    public function __construct(HttpClientInterface $client, Stopwatch $stopwatch = null)
    {
        $this->client = $client;
        $this->stopwatch = $stopwatch;
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public function request($method, $url, $options = []) : ResponseInterface
    {
        $content = null;
        $traceInfo = [];
        $this->tracedRequests[] = ['method' => $method, 'url' => $url, 'options' => $options, 'info' => &$traceInfo, 'content' => &$content];
        $onProgress = $options['on_progress'] ?? null;
        if (\false === ($options['extra']['trace_content'] ?? \true)) {
            unset($content);
            $content = \false;
        }
        $options['on_progress'] = function (int $dlNow, int $dlSize, array $info) use(&$traceInfo, $onProgress) {
            $traceInfo = $info;
            if (null !== $onProgress) {
                $onProgress($dlNow, $dlSize, $info);
            }
        };
        return new TraceableResponse($this->client, $this->client->request($method, $url, $options), $content, null === $this->stopwatch ? null : $this->stopwatch->start("{$method} {$url}", 'http_client'));
    }
    /**
     * @param float|null $timeout
     */
    public function stream($responses, $timeout = null) : ResponseStreamInterface
    {
        if ($responses instanceof TraceableResponse) {
            $responses = [$responses];
        } elseif (!(is_array($responses) || $responses instanceof \Traversable)) {
            throw new \TypeError(\sprintf('"%s()" expects parameter 1 to be an iterable of TraceableResponse objects, "%s" given.', __METHOD__, \get_debug_type($responses)));
        }
        return new ResponseStream(TraceableResponse::stream($this->client, $responses, $timeout));
    }
    public function getTracedRequests() : array
    {
        return $this->tracedRequests;
    }
    public function reset()
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
        $this->tracedRequests = [];
    }
    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger($logger)
    {
        if ($this->client instanceof LoggerAwareInterface) {
            $this->client->setLogger($logger);
        }
    }
}
