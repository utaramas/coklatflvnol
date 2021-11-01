<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
interface ResponseHandlerInterface
{
    public function __construct(CrawlerInterface $crawler);
    /**
     * @param \Staatic\Crawler\ResponseHandler\ResponseHandlerInterface $nextHandler
     */
    public function setNext($nextHandler) : \Staatic\Crawler\ResponseHandler\ResponseHandlerInterface;
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl;
}
