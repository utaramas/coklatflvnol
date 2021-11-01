<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Symfony\Component\HttpClient\Response\AsyncResponse;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\ResponseStream;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
trait AsyncDecoratorTrait
{
    private $client;
    public function __construct(HttpClientInterface $client = null)
    {
        $this->client = $client ?? HttpClient::create();
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public abstract function request($method, $url, $options = []) : ResponseInterface;
    /**
     * @param float|null $timeout
     */
    public function stream($responses, $timeout = null) : ResponseStreamInterface
    {
        if ($responses instanceof AsyncResponse) {
            $responses = [$responses];
        } elseif (!(is_array($responses) || $responses instanceof \Traversable)) {
            throw new \TypeError(\sprintf('"%s()" expects parameter 1 to be an iterable of AsyncResponse objects, "%s" given.', __METHOD__, \get_debug_type($responses)));
        }
        return new ResponseStream(AsyncResponse::stream($responses, $timeout, static::class));
    }
}
