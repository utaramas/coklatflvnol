<?php

namespace Staatic\Crawler\Event;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
class CrawlRequestFulfilled implements EventInterface
{
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var UriInterface
     */
    private $transformedUrl;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var UriInterface|null
     */
    private $foundOnUrl;
    /**
     * @var mixed[]
     */
    private $tags;
    /**
     * @param UriInterface|null $foundOnUrl
     */
    public function __construct(UriInterface $url, UriInterface $transformedUrl, ResponseInterface $response, $foundOnUrl = null, array $tags = [])
    {
        $this->url = $url;
        $this->transformedUrl = $transformedUrl;
        $this->response = $response;
        $this->foundOnUrl = $foundOnUrl;
        $this->tags = $tags;
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function transformedUrl() : UriInterface
    {
        return $this->transformedUrl;
    }
    public function response() : ResponseInterface
    {
        return $this->response;
    }
    /**
     * @return UriInterface|null
     */
    public function foundOnUrl()
    {
        return $this->foundOnUrl;
    }
    public function tags() : array
    {
        return $this->tags;
    }
}
