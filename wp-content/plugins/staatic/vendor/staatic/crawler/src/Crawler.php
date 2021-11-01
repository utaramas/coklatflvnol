<?php

namespace Staatic\Crawler;

use Staatic\Vendor\GuzzleHttp\Exception\RequestException;
use Staatic\Vendor\GuzzleHttp\ClientInterface;
use Staatic\Vendor\GuzzleHttp\Exception\TransferException;
use Staatic\Vendor\GuzzleHttp\Pool;
use Staatic\Vendor\GuzzleHttp\Psr7\Request;
use Staatic\Vendor\GuzzleHttp\RequestOptions;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\CrawlOptions;
use Staatic\Crawler\CrawlProfile\CrawlProfileInterface;
use Staatic\Crawler\CrawlQueue\CrawlQueueInterface;
use Staatic\Crawler\Event\CrawlRequestFulfilled;
use Staatic\Crawler\Event\CrawlRequestRejected;
use Staatic\Crawler\Event\EventInterface;
use Staatic\Crawler\Event\FinishedCrawling;
use Staatic\Crawler\Event\StartsCrawling;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderInterface;
use Staatic\Crawler\KnownUrlsContainer\KnownUrlsContainerInterface;
use Staatic\Crawler\ResponseHandler\ResponseHandlerInterface;
use Staatic\Crawler\ResponseHandler\ResponseHandlerFactory;
final class Crawler implements CrawlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    const PATTERN_ASSETS = '~\\.(js|css|png|jpg|gif|eot|woff|woff2|ttf|svg)$~';
    /**
     * @var \SplObjectStorage
     */
    private $observers;
    /**
     * @var ClientInterface
     */
    private $httpClient;
    /**
     * @var CrawlProfileInterface
     */
    private $crawlProfile;
    /**
     * @var CrawlQueueInterface
     */
    private $crawlQueue;
    /**
     * @var KnownUrlsContainerInterface
     */
    private $knownUrlsContainer;
    /**
     * @var mixed[]
     */
    private $crawlUrlProviders;
    /**
     * @var CrawlOptions
     */
    private $crawlOptions;
    /**
     * @var EventInterface|null
     */
    private $event;
    private $pendingCrawlUrlsById = [];
    /**
     * @var int
     */
    private $numCrawlProcessed = 0;
    public function __construct(ClientInterface $httpClient, CrawlProfileInterface $crawlProfile, CrawlQueueInterface $crawlQueue, KnownUrlsContainerInterface $knownUrlsContainer, array $crawlUrlProviders, CrawlOptions $crawlOptions)
    {
        $this->logger = new NullLogger();
        $this->observers = new \SplObjectStorage();
        $this->httpClient = $httpClient;
        $this->crawlProfile = $crawlProfile;
        $this->crawlQueue = $crawlQueue;
        $this->knownUrlsContainer = $knownUrlsContainer;
        $this->crawlUrlProviders = $crawlUrlProviders;
        $this->crawlOptions = $crawlOptions;
    }
    public function initialize() : int
    {
        $this->crawlQueue->clear();
        $this->knownUrlsContainer->clear();
        $numEnqueued = 0;
        foreach ($this->crawlUrlProviders as $crawlUrlProvider) {
            foreach ($crawlUrlProvider->provide($this) as $crawlUrl) {
                if (!$this->shouldCrawl($crawlUrl->url())) {
                    continue;
                }
                $this->addToCrawlQueue($crawlUrl);
                $numEnqueued++;
            }
        }
        return $numEnqueued;
    }
    public function crawl() : int
    {
        $this->notifyStartsCrawling();
        $this->numCrawlProcessed = 0;
        $this->crawlLoop();
        if ($this->isFinishedCrawling()) {
            $this->notifyFinishedCrawling();
        }
        return $this->numCrawlProcessed;
    }
    /**
     * @return void
     */
    private function crawlLoop()
    {
        while (\count($this->crawlQueue) && !$this->maxCrawlsReached()) {
            $this->startCrawlQueue();
        }
    }
    private function maxCrawlsReached() : bool
    {
        $maxCrawls = $this->crawlOptions->maxCrawls();
        return $maxCrawls !== null && $this->numCrawlProcessed >= $maxCrawls;
    }
    private function isFinishedCrawling() : bool
    {
        return \count($this->crawlQueue) === 0;
    }
    private function notifyStartsCrawling()
    {
        $this->setEvent(new StartsCrawling());
        $this->notify();
    }
    private function notifyFinishedCrawling()
    {
        $this->setEvent(new FinishedCrawling());
        $this->notify();
    }
    /**
     * @param UriInterface $normalizedUrl
     */
    public function shouldCrawl($normalizedUrl) : bool
    {
        if (!$this->hasCrawlableScheme($normalizedUrl)) {
            return \false;
        }
        if (!$this->crawlProfile->shouldCrawl($normalizedUrl)) {
            return \false;
        }
        return \true;
    }
    private function hasCrawlableScheme(UriInterface $url) : bool
    {
        return \in_array($url->getScheme(), ['http', 'https']);
    }
    /**
     * @param CrawlUrl $crawlUrl
     * @return void
     */
    public function addToCrawlQueue($crawlUrl)
    {
        if ($this->isKnownUrl($crawlUrl->url())) {
            return;
        }
        $this->addKnownUrl($crawlUrl->url());
        $maxDepth = $this->crawlOptions->maxDepth();
        $forceAssets = $this->crawlOptions->forceAssets();
        if ($forceAssets && $this->isAssetUrl($crawlUrl->url())) {
            $maxDepth = null;
        }
        if ($maxDepth !== null && $crawlUrl->depthLevel() >= $maxDepth) {
            return;
        }
        $priority = $this->determineCrawlUrlPriority($crawlUrl);
        $this->crawlQueue->enqueue($crawlUrl, $priority);
    }
    private function isKnownUrl(UriInterface $url) : bool
    {
        $normalizedUrl = $this->crawlProfile->normalizeUrl($url);
        return $this->knownUrlsContainer->isKnown($normalizedUrl);
    }
    /**
     * @return void
     */
    private function addKnownUrl(UriInterface $url)
    {
        $normalizedUrl = $this->crawlProfile->normalizeUrl($url);
        $this->knownUrlsContainer->add($normalizedUrl);
    }
    private function determineCrawlUrlPriority(CrawlUrl $crawlUrl) : int
    {
        if ($crawlUrl->hasTag(self::TAG_PRIORITY_HIGH)) {
            return 90;
        } elseif ($crawlUrl->hasTag(self::TAG_PRIORITY_LOW)) {
            return 30;
        } else {
            return 60;
        }
    }
    private function isAssetUrl(UriInterface $url) : bool
    {
        return \preg_match(self::PATTERN_ASSETS, $url->getPath()) === 1;
    }
    /**
     * @return void
     */
    private function startCrawlQueue()
    {
        $pool = new Pool($this->httpClient, $this->getHttpRequests(), ['concurrency' => $this->crawlOptions->concurrency(), 'fulfilled' => function (ResponseInterface $response, $index) {
            $this->handleRequestFulfilled($response, $index);
        }, 'rejected' => function (TransferException $transferException, $index) {
            $this->handleRequestRejected($transferException, $index);
        }, 'options' => [RequestOptions::ALLOW_REDIRECTS => \false]]);
        $promise = $pool->promise();
        $promise->wait();
    }
    private function getHttpRequests() : \Generator
    {
        while ($this->crawlQueue->count() && !$this->maxCrawlsReached()) {
            $crawlUrl = $this->crawlQueue->dequeue();
            $this->pendingCrawlUrlsById[$crawlUrl->id()] = $crawlUrl;
            $this->numCrawlProcessed++;
            $this->logger->debug(\sprintf('Preparing request for %s', $crawlUrl->url()), ['crawlUrlId' => $crawlUrl->id()]);
            (yield $crawlUrl->id() => new Request('GET', $crawlUrl->url()));
        }
    }
    /**
     * @return void
     */
    private function handleRequestFulfilled(ResponseInterface $response, string $crawlUrlId)
    {
        $crawlUrl = $this->pendingCrawlUrlsById[$crawlUrlId]->withResponse($response);
        unset($this->pendingCrawlUrlsById[$crawlUrlId]);
        $this->logger->debug(\sprintf('Fulfilled request for %s', $crawlUrl->url()), ['crawlUrlId' => $crawlUrl->id()]);
        $responseHandlers = $this->getChainOfResponseFulfilledHandlers();
        $crawlUrl = $responseHandlers->handle($crawlUrl);
        $this->notifyCrawlRequestFulfilled($crawlUrl);
    }
    private function getChainOfResponseFulfilledHandlers() : ResponseHandlerInterface
    {
        return ResponseHandlerFactory::createChain(['xmlSitemapTagger', 'redirect', 'html', 'css', 'xmlSitemap', 'rss', 'xml'], $this);
    }
    /**
     * @return void
     */
    private function notifyCrawlRequestFulfilled(CrawlUrl $crawlUrl)
    {
        $this->setEvent(new CrawlRequestFulfilled($crawlUrl->url(), $crawlUrl->transformedUrl(), $crawlUrl->response(), $crawlUrl->foundOnUrl(), $crawlUrl->tags()));
        $this->notify();
    }
    /**
     * @return void
     */
    private function handleRequestRejected(TransferException $transferException, string $crawlUrlId)
    {
        $crawlUrl = $this->pendingCrawlUrlsById[$crawlUrlId];
        unset($this->pendingCrawlUrlsById[$crawlUrlId]);
        if ($transferException instanceof RequestException) {
            $crawlUrl = $crawlUrl->withResponse($transferException->getResponse());
        }
        $this->logger->debug(\sprintf('Rejected request for %s', $crawlUrl->url()), ['crawlUrlId' => $crawlUrl->id()]);
        $responseHandlers = $this->getChainOfResponseRejectedHandlers();
        $crawlUrl = $responseHandlers->handle($crawlUrl);
        $this->notifyCrawlRequestRejected($crawlUrl, $transferException);
    }
    private function getChainOfResponseRejectedHandlers() : ResponseHandlerInterface
    {
        return ResponseHandlerFactory::createChain(['html', 'css', 'xml'], $this);
    }
    /**
     * @return void
     */
    private function notifyCrawlRequestRejected(CrawlUrl $crawlUrl, TransferException $transferException)
    {
        $this->setEvent(new CrawlRequestRejected($crawlUrl->url(), $crawlUrl->transformedUrl(), $transferException, $crawlUrl->foundOnUrl(), $crawlUrl->tags()));
        $this->notify();
    }
    /**
     * @param UriInterface $url
     */
    public function transformUrl($url) : UriInterface
    {
        return $this->crawlProfile->transformUrl($url);
    }
    /**
     * @return int|null
     */
    public function maxResponseBodyInBytes()
    {
        return $this->crawlOptions->maxResponseBodyInBytes();
    }
    public function maxRedirects() : int
    {
        return $this->crawlOptions->maxRedirects();
    }
    public function httpClient() : ClientInterface
    {
        return $this->httpClient;
    }
    public function crawlProfile() : CrawlProfileInterface
    {
        return $this->crawlProfile;
    }
    public function crawlQueue() : CrawlQueueInterface
    {
        return $this->crawlQueue;
    }
    public function knownUrlsContainer() : KnownUrlsContainerInterface
    {
        return $this->knownUrlsContainer;
    }
    public function crawlOptions() : CrawlOptions
    {
        return $this->crawlOptions;
    }
    public function numUrlsCrawlable() : int
    {
        return $this->knownUrlsContainer->count();
    }
    public function attach(\SplObserver $observer)
    {
        $this->logger->debug(\sprintf('Attaching observer %s', \get_class($observer)));
        $this->observers->attach($observer);
    }
    public function detach(\SplObserver $observer)
    {
        $this->logger->debug(\sprintf('Detaching observer %s', \get_class($observer)));
        $this->observers->detach($observer);
    }
    public function notify()
    {
        $this->logger->debug(\sprintf('Notifying %d observers about %s', \count($this->observers), \get_class($this->event)));
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
    /**
     * @return EventInterface|null
     */
    public function getEvent()
    {
        return $this->event;
    }
    /**
     * @param EventInterface $event
     * @return void
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }
}
