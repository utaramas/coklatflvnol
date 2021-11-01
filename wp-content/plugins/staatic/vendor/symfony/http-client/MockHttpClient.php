<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Symfony\Component\HttpClient\Exception\TransportException;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\MockResponse;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\ResponseStream;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
class MockHttpClient implements HttpClientInterface
{
    use HttpClientTrait;
    private $responseFactory;
    private $baseUri;
    private $requestsCount = 0;
    public function __construct($responseFactory = null, string $baseUri = null)
    {
        if ($responseFactory instanceof ResponseInterface) {
            $responseFactory = [$responseFactory];
        }
        if (!$responseFactory instanceof \Iterator && null !== $responseFactory && !\is_callable($responseFactory)) {
            $responseFactory = (static function () use($responseFactory) {
                yield from $responseFactory;
            })();
        }
        $this->responseFactory = $responseFactory;
        $this->baseUri = $baseUri;
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public function request($method, $url, $options = []) : ResponseInterface
    {
        list($url, $options) = $this->prepareRequest($method, $url, $options, ['base_uri' => $this->baseUri], \true);
        $url = \implode('', $url);
        if (null === $this->responseFactory) {
            $response = new MockResponse();
        } elseif (\is_callable($this->responseFactory)) {
            $response = ($this->responseFactory)($method, $url, $options);
        } elseif (!$this->responseFactory->valid()) {
            throw new TransportException('The response factory iterator passed to MockHttpClient is empty.');
        } else {
            $responseFactory = $this->responseFactory->current();
            $response = \is_callable($responseFactory) ? $responseFactory($method, $url, $options) : $responseFactory;
            $this->responseFactory->next();
        }
        ++$this->requestsCount;
        if (!$response instanceof ResponseInterface) {
            throw new TransportException(\sprintf('The response factory passed to MockHttpClient must return/yield an instance of ResponseInterface, "%s" given.', \is_object($response) ? \get_class($response) : \gettype($response)));
        }
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
            throw new \TypeError(\sprintf('"%s()" expects parameter 1 to be an iterable of MockResponse objects, "%s" given.', __METHOD__, \get_debug_type($responses)));
        }
        return new ResponseStream(MockResponse::stream($responses, $timeout));
    }
    public function getRequestsCount() : int
    {
        return $this->requestsCount;
    }
}
