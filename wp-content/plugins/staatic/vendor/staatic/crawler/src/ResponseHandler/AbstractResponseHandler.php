<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
abstract class AbstractResponseHandler implements ResponseHandlerInterface
{
    /**
     * @var CrawlerInterface
     */
    protected $crawler;
    /**
     * @var ResponseHandlerInterface|null
     */
    private $nextHandler;
    public function __construct(CrawlerInterface $crawler)
    {
        $this->crawler = $crawler;
    }
    /**
     * @param ResponseHandlerInterface $nextHandler
     */
    public function setNext($nextHandler) : ResponseHandlerInterface
    {
        $this->nextHandler = $nextHandler;
        return $nextHandler;
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($crawlUrl);
        }
        return $crawlUrl;
    }
    protected function urlFilterCallback() : callable
    {
        return function (UriInterface $url) {
            return $this->crawler->shouldCrawl($url);
        };
    }
    protected function urlReplaceCallback() : callable
    {
        return function (UriInterface $url) {
            return $this->crawler->transformUrl($url);
        };
    }
}
