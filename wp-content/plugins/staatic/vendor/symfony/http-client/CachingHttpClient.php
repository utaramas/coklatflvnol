<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Symfony\Component\HttpClient\Response\MockResponse;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\ResponseStream;
use Staatic\Vendor\Symfony\Component\HttpFoundation\Request;
use Staatic\Vendor\Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Staatic\Vendor\Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Staatic\Vendor\Symfony\Component\HttpKernel\HttpClientKernel;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
class CachingHttpClient implements HttpClientInterface
{
    use HttpClientTrait;
    private $client;
    private $cache;
    private $defaultOptions = self::OPTIONS_DEFAULTS;
    public function __construct(HttpClientInterface $client, StoreInterface $store, array $defaultOptions = [])
    {
        if (!\class_exists(HttpClientKernel::class)) {
            throw new \LogicException(\sprintf('Using "%s" requires that the HttpKernel component version 4.3 or higher is installed, try running "composer require symfony/http-kernel:^4.3".', __CLASS__));
        }
        $this->client = $client;
        $kernel = new HttpClientKernel($client);
        $this->cache = new HttpCache($kernel, $store, null, $defaultOptions);
        unset($defaultOptions['debug']);
        unset($defaultOptions['default_ttl']);
        unset($defaultOptions['private_headers']);
        unset($defaultOptions['allow_reload']);
        unset($defaultOptions['allow_revalidate']);
        unset($defaultOptions['stale_while_revalidate']);
        unset($defaultOptions['stale_if_error']);
        unset($defaultOptions['trace_level']);
        unset($defaultOptions['trace_header']);
        if ($defaultOptions) {
            list(, $this->defaultOptions) = self::prepareRequest(null, null, $defaultOptions, $this->defaultOptions);
        }
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public function request($method, $url, $options = []) : ResponseInterface
    {
        list($url, $options) = $this->prepareRequest($method, $url, $options, $this->defaultOptions, \true);
        $url = \implode('', $url);
        if (!empty($options['body']) || !empty($options['extra']['no_cache']) || !\in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return $this->client->request($method, $url, $options);
        }
        $request = Request::create($url, $method);
        $request->attributes->set('http_client_options', $options);
        foreach ($options['normalized_headers'] as $name => $values) {
            if ('cookie' !== $name) {
                foreach ($values as $value) {
                    $request->headers->set($name, \substr($value, 2 + \strlen($name)), \false);
                }
                continue;
            }
            foreach ($values as $cookies) {
                foreach (\explode('; ', \substr($cookies, \strlen('Cookie: '))) as $cookie) {
                    if ('' !== $cookie) {
                        $cookie = \explode('=', $cookie, 2);
                        $request->cookies->set($cookie[0], $cookie[1] ?? '');
                    }
                }
            }
        }
        $response = $this->cache->handle($request);
        $response = new MockResponse($response->getContent(), ['http_code' => $response->getStatusCode(), 'response_headers' => $response->headers->allPreserveCase()]);
        return MockResponse::fromRequest($method, $url, $options, $response);
    }
    /**
     * @param float|null $timeout
     */
    public function stream($responses, $timeout = null) : ResponseStreamInterface
    {
        if ($responses instanceof ResponseInterface) {
            $responses = [$responses];
        } elseif (!(is_array($responses) || $responses instanceof \Traversable)) {
            throw new \TypeError(\sprintf('"%s()" expects parameter 1 to be an iterable of ResponseInterface objects, "%s" given.', __METHOD__, \get_debug_type($responses)));
        }
        $mockResponses = [];
        $clientResponses = [];
        foreach ($responses as $response) {
            if ($response instanceof MockResponse) {
                $mockResponses[] = $response;
            } else {
                $clientResponses[] = $response;
            }
        }
        if (!$mockResponses) {
            return $this->client->stream($clientResponses, $timeout);
        }
        if (!$clientResponses) {
            return new ResponseStream(MockResponse::stream($mockResponses, $timeout));
        }
        return new ResponseStream((function () use($mockResponses, $clientResponses, $timeout) {
            yield from MockResponse::stream($mockResponses, $timeout);
            (yield $this->client->stream($clientResponses, $timeout));
        })());
    }
}
