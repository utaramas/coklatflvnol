<?php

namespace Staatic\Crawler\CrawlUrlProvider;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
class EntryCrawlUrlProvider implements CrawlUrlProviderInterface
{
    /**
     * @var UriInterface
     */
    private $url;
    public function __construct(UriInterface $url)
    {
        $this->url = $url;
    }
    /**
     * @param CrawlerInterface $crawler
     */
    public function provide($crawler) : \Generator
    {
        (yield CrawlUrl::create($this->url, $crawler->transformUrl($this->url)));
    }
}
