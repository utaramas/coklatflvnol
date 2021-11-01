<?php

namespace Staatic\Crawler\CrawlQueue;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\CrawlUrl;
class InMemoryCrawlQueue implements CrawlQueueInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var \SplPriorityQueue
     */
    private $decoratedQueue;
    public function __construct()
    {
        $this->logger = new NullLogger();
        $this->decoratedQueue = new \SplPriorityQueue();
    }
    /**
     * @return void
     */
    public function clear()
    {
        $this->logger->debug('Clearing crawl queue');
        $this->decoratedQueue = new \SplPriorityQueue();
    }
    /**
     * @param CrawlUrl $crawlUrl
     * @param int $priority
     * @return void
     */
    public function enqueue($crawlUrl, $priority)
    {
        $this->logger->debug(\sprintf('Enqueueing crawl url "%s" (priority %d)', $crawlUrl->url(), $priority), ['crawlUrlId' => $crawlUrl->id()]);
        $this->decoratedQueue->insert($crawlUrl, $priority);
    }
    public function dequeue() : CrawlUrl
    {
        if (!$this->decoratedQueue->valid()) {
            throw new \RuntimeException('Unable to dequeue; queue was empty');
        }
        $crawlUrl = $this->decoratedQueue->extract();
        $this->logger->debug(\sprintf('Dequeued crawl url "%s"', $crawlUrl->url()), ['crawlUrlId' => $crawlUrl->id()]);
        return $crawlUrl;
    }
    public function count()
    {
        return $this->decoratedQueue->count();
    }
}
