<?php

namespace Staatic\Crawler\CrawlUrlProvider;

use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
interface CrawlUrlProviderInterface
{
    /**
     * @param CrawlerInterface $crawler
     */
    public function provide($crawler) : \Generator;
}
