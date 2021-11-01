<?php

namespace Staatic\Crawler\Observer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\GuzzleHttp\Exception\TransferException;
final class CallbackObserver extends AbstractObserver
{
    private $crawlFulfilled;
    private $crawlRejected;
    private $startsCrawling;
    private $finishedCrawling;
    /**
     * @param callable|null $startsCrawling
     * @param callable|null $finishedCrawling
     */
    public function __construct(callable $crawlFulfilled, callable $crawlRejected, $startsCrawling = null, $finishedCrawling = null)
    {
        $this->crawlFulfilled = $crawlFulfilled;
        $this->crawlRejected = $crawlRejected;
        $this->startsCrawling = $startsCrawling;
        $this->finishedCrawling = $finishedCrawling;
    }
    /**
     * @return void
     */
    public function startsCrawling()
    {
        if ($this->startsCrawling) {
            ($this->startsCrawling)();
        }
    }
    /**
     * @param UriInterface $url
     * @param UriInterface $transformedUrl
     * @param ResponseInterface $response
     * @param UriInterface|null $foundOnUrl
     * @param mixed[] $tags
     * @return void
     */
    public function crawlFulfilled($url, $transformedUrl, $response, $foundOnUrl, $tags)
    {
        ($this->crawlFulfilled)($url, $transformedUrl, $response, $foundOnUrl, $tags);
    }
    /**
     * @param UriInterface $url
     * @param UriInterface $transformedUrl
     * @param TransferException $transferException
     * @param UriInterface|null $foundOnUrl
     * @param mixed[] $tags
     * @return void
     */
    public function crawlRejected($url, $transformedUrl, $transferException, $foundOnUrl, $tags)
    {
        ($this->crawlRejected)($url, $transformedUrl, $transferException, $foundOnUrl, $tags);
    }
    /**
     * @return void
     */
    public function finishedCrawling()
    {
        if ($this->finishedCrawling) {
            ($this->finishedCrawling)();
        }
    }
}
