<?php

namespace Staatic\Crawler;

use Staatic\Vendor\GuzzleHttp\ClientInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlOptions;
use Staatic\Crawler\CrawlProfile\CrawlProfileInterface;
use Staatic\Crawler\CrawlQueue\CrawlQueueInterface;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderInterface;
use Staatic\Crawler\Event\EventInterface;
use Staatic\Crawler\KnownUrlsContainer\KnownUrlsContainerInterface;
interface CrawlerInterface extends \SplSubject
{
    const TAG_PRIORITY_HIGH = 'priority_high';
    const TAG_PRIORITY_LOW = 'priority_low';
    const TAG_SITEMAP_XML = 'sitemap_xml';
    const TAG_PAGE_NOT_FOUND = 'page_not_found';
    public function __construct(ClientInterface $httpClient, CrawlProfileInterface $crawlProfile, CrawlQueueInterface $crawlQueue, KnownUrlsContainerInterface $knownUrlsContainer, array $crawlUrlProviders, CrawlOptions $crawlOptions);
    public function initialize() : int;
    public function crawl() : int;
    /**
     * @param UriInterface $url
     */
    public function shouldCrawl($url) : bool;
    /**
     * @param CrawlUrl $crawlUrl
     * @return void
     */
    public function addToCrawlQueue($crawlUrl);
    /**
     * @param UriInterface $url
     */
    public function transformUrl($url) : UriInterface;
    /**
     * @return int|null
     */
    public function maxResponseBodyInBytes();
    public function maxRedirects() : int;
    public function httpClient() : ClientInterface;
    public function crawlProfile() : CrawlProfileInterface;
    public function crawlQueue() : CrawlQueueInterface;
    public function knownUrlsContainer() : KnownUrlsContainerInterface;
    public function crawlOptions() : CrawlOptions;
    public function numUrlsCrawlable() : int;
    /**
     * @return EventInterface|null
     */
    public function getEvent();
    /**
     * @param EventInterface $event
     * @return void
     */
    public function setEvent($event);
}
