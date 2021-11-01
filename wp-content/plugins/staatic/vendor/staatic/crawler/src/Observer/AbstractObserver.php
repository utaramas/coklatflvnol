<?php

namespace Staatic\Crawler\Observer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\GuzzleHttp\Exception\TransferException;
use Staatic\Crawler\Event\StartsCrawling;
use Staatic\Crawler\Event\CrawlRequestFulfilled;
use Staatic\Crawler\Event\CrawlRequestRejected;
use Staatic\Crawler\Event\FinishedCrawling;
abstract class AbstractObserver implements \SplObserver
{
    public function update(\SplSubject $crawler)
    {
        $event = $crawler->getEvent();
        switch (\get_class($event)) {
            case StartsCrawling::class:
                $this->startsCrawling();
                break;
            case CrawlRequestFulfilled::class:
                $this->crawlFulfilled($event->url(), $event->transformedUrl(), $event->response(), $event->foundOnUrl(), $event->tags());
                break;
            case CrawlRequestRejected::class:
                $this->crawlRejected($event->url(), $event->transformedUrl(), $event->transferException(), $event->foundOnUrl(), $event->tags());
                break;
            case FinishedCrawling::class:
                $this->finishedCrawling();
                break;
        }
    }
    /**
     * @return void
     */
    public function startsCrawling()
    {
    }
    /**
     * @param UriInterface $url
     * @param UriInterface $transformedUrl
     * @param ResponseInterface $response
     * @param UriInterface|null $foundOnUrl
     * @param mixed[] $tags
     * @return void
     */
    public abstract function crawlFulfilled($url, $transformedUrl, $response, $foundOnUrl, $tags);
    /**
     * @param UriInterface $url
     * @param UriInterface $transformedUrl
     * @param TransferException $transferException
     * @param UriInterface|null $foundOnUrl
     * @param mixed[] $tags
     * @return void
     */
    public abstract function crawlRejected($url, $transformedUrl, $transferException, $foundOnUrl, $tags);
    /**
     * @return void
     */
    public function finishedCrawling()
    {
    }
}
