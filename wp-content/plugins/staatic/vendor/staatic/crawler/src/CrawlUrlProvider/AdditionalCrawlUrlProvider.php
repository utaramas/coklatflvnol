<?php

namespace Staatic\Crawler\CrawlUrlProvider;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
class AdditionalCrawlUrlProvider implements CrawlUrlProviderInterface
{
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_LOW = 'low';
    private $additionalUrls;
    /**
     * @var string
     */
    private $priority;
    public function __construct(array $additionalUrls, $priority = self::PRIORITY_NORMAL)
    {
        $this->additionalUrls = $additionalUrls;
        $this->priority = $priority;
    }
    /**
     * @param CrawlerInterface $crawler
     */
    public function provide($crawler) : \Generator
    {
        $tags = [];
        if ($this->priority === self::PRIORITY_HIGH) {
            $tags[] = CrawlerInterface::TAG_PRIORITY_HIGH;
        } elseif ($this->priority === self::PRIORITY_LOW) {
            $tags[] = CrawlerInterface::TAG_PRIORITY_LOW;
        }
        foreach ($this->additionalUrls as $additionalUrl) {
            (yield CrawlUrl::create($additionalUrl, $crawler->transformUrl($additionalUrl), null, \false, $tags));
        }
    }
}
