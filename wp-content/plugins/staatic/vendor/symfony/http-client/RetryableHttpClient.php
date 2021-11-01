<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\AsyncContext;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\AsyncResponse;
use Staatic\Vendor\Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Staatic\Vendor\Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ChunkInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
class RetryableHttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;
    private $strategy;
    private $maxRetries;
    private $logger;
    public function __construct(HttpClientInterface $client, RetryStrategyInterface $strategy = null, int $maxRetries = 3, LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->strategy = $strategy ?? new GenericRetryStrategy();
        $this->maxRetries = $maxRetries;
        $this->logger = $logger ?? new NullLogger();
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public function request($method, $url, $options = []) : ResponseInterface
    {
        if ($this->maxRetries <= 0) {
            return new AsyncResponse($this->client, $method, $url, $options);
        }
        $retryCount = 0;
        $content = '';
        $firstChunk = null;
        return new AsyncResponse($this->client, $method, $url, $options, function (ChunkInterface $chunk, AsyncContext $context) use($method, $url, $options, &$retryCount, &$content, &$firstChunk) {
            $exception = null;
            try {
                if ($chunk->isTimeout() || null !== $chunk->getInformationalStatus()) {
                    (yield $chunk);
                    return;
                }
            } catch (TransportExceptionInterface $exception) {
            }
            if (null !== $exception) {
                if ('' !== $context->getInfo('primary_ip')) {
                    $shouldRetry = $this->strategy->shouldRetry($context, null, $exception);
                    if (null === $shouldRetry) {
                        throw new \LogicException(\sprintf('The "%s::shouldRetry()" method must not return null when called with an exception.', \get_class($this->decider)));
                    }
                    if (\false === $shouldRetry) {
                        $context->passthru();
                        if (null !== $firstChunk) {
                            (yield $firstChunk);
                            (yield $context->createChunk($content));
                            (yield $chunk);
                        } else {
                            (yield $chunk);
                        }
                        $content = '';
                        return;
                    }
                }
            } elseif ($chunk->isFirst()) {
                if (\false === ($shouldRetry = $this->strategy->shouldRetry($context, null, null))) {
                    $context->passthru();
                    (yield $chunk);
                    return;
                }
                if (null === $shouldRetry) {
                    $firstChunk = $chunk;
                    $content = '';
                    return;
                }
            } else {
                $content .= $chunk->getContent();
                if (!$chunk->isLast()) {
                    return;
                }
                if (null === ($shouldRetry = $this->strategy->shouldRetry($context, $content, null))) {
                    throw new \LogicException(\sprintf('The "%s::shouldRetry()" method must not return null when called with a body.', \get_class($this->strategy)));
                }
                if (\false === $shouldRetry) {
                    $context->passthru();
                    (yield $firstChunk);
                    (yield $context->createChunk($content));
                    $content = '';
                    return;
                }
            }
            $context->getResponse()->cancel();
            $delay = $this->getDelayFromHeader($context->getHeaders()) ?? $this->strategy->getDelay($context, !$exception && $chunk->isLast() ? $content : null, $exception);
            ++$retryCount;
            $this->logger->info('Try #{count} after {delay}ms' . ($exception ? ': ' . $exception->getMessage() : ', status code: ' . $context->getStatusCode()), ['count' => $retryCount, 'delay' => $delay]);
            $context->setInfo('retry_count', $retryCount);
            $context->replaceRequest($method, $url, $options);
            $context->pause($delay / 1000);
            if ($retryCount >= $this->maxRetries) {
                $context->passthru();
            }
        });
    }
    /**
     * @return int|null
     */
    private function getDelayFromHeader(array $headers)
    {
        if (null !== ($after = $headers['retry-after'][0] ?? null)) {
            if (\is_numeric($after)) {
                return (int) $after * 1000;
            }
            if (\false !== ($time = \strtotime($after))) {
                return \max(0, $time - \time()) * 1000;
            }
        }
        return null;
    }
}
