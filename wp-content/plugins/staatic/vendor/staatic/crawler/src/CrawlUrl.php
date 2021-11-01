<?php

namespace Staatic\Crawler;

use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
final class CrawlUrl
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var UriInterface
     */
    private $originUrl;
    /**
     * @var UriInterface
     */
    private $transformedUrl;
    /**
     * @var UriInterface|null
     */
    private $foundOnUrl;
    /**
     * @var int
     */
    private $depthLevel;
    /**
     * @var int
     */
    private $redirectLevel;
    /**
     * @var mixed[]
     */
    private $tags;
    /**
     * @var ResponseInterface|null
     */
    private $response;
    /**
     * @param UriInterface|null $foundOnUrl
     */
    public function __construct(string $id, UriInterface $url, UriInterface $originUrl, UriInterface $transformedUrl, $foundOnUrl = null, int $depthLevel = 0, int $redirectLevel = 0, array $tags = [])
    {
        $this->id = $id;
        $this->url = $url;
        $this->originUrl = $originUrl;
        $this->transformedUrl = $transformedUrl;
        $this->foundOnUrl = $foundOnUrl;
        $this->depthLevel = $depthLevel;
        $this->redirectLevel = $redirectLevel;
        $this->tags = $tags;
    }
    public static function create(UriInterface $url, UriInterface $transformedUrl, self $parentCrawlUrl = null, bool $isRedirected = \false, array $tags = []) : self
    {
        if ($isRedirected && $parentCrawlUrl === null) {
            throw new \LogicException('A redirected crawl URL required a parentCrawlUrl.');
        }
        return new static((string) Uuid::uuid5(Uuid::NAMESPACE_URL, (string) $url), $url, $isRedirected ? $parentCrawlUrl->originUrl() : $url, $transformedUrl, $parentCrawlUrl ? $parentCrawlUrl->url() : null, $parentCrawlUrl ? $parentCrawlUrl->depthLevel() + 1 : 0, $isRedirected ? $parentCrawlUrl->redirectLevel() + 1 : 0, $tags);
    }
    public function id() : string
    {
        return $this->id;
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function originUrl() : UriInterface
    {
        return $this->originUrl;
    }
    public function transformedUrl() : UriInterface
    {
        return $this->transformedUrl;
    }
    /**
     * @return UriInterface|null
     */
    public function foundOnUrl()
    {
        return $this->foundOnUrl;
    }
    public function depthLevel() : int
    {
        return $this->depthLevel;
    }
    public function redirectLevel() : int
    {
        return $this->redirectLevel;
    }
    public function tags() : array
    {
        return $this->tags;
    }
    public function hasTag(string $tag) : bool
    {
        return \in_array($tag, $this->tags);
    }
    public function withTags(array $tags) : self
    {
        $newCrawlUrl = clone $this;
        $newCrawlUrl->tags = $tags;
        return $newCrawlUrl;
    }
    /**
     * @return ResponseInterface|null
     */
    public function response()
    {
        return $this->response;
    }
    /**
     * @param ResponseInterface|null $response
     */
    public function withResponse($response) : self
    {
        $newCrawlUrl = clone $this;
        $newCrawlUrl->response = $response;
        return $newCrawlUrl;
    }
}
