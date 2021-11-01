<?php

namespace Staatic\Crawler\CrawlProfile;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
interface CrawlProfileInterface
{
    /**
     * @param UriInterface $url
     */
    public function shouldCrawl($url) : bool;
    /**
     * @param UriInterface $url
     */
    public function normalizeUrl($url) : UriInterface;
    /**
     * @param UriInterface $url
     */
    public function transformUrl($url) : UriInterface;
}
