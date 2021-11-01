<?php

namespace Staatic\Crawler\CrawlProfile;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\UrlNormalizer\InternalUrlNormalizer;
use Staatic\Crawler\UrlNormalizer\UrlNormalizerInterface;
use Staatic\Crawler\UrlTransformer\BasicUrlTransformer;
use Staatic\Crawler\UrlTransformer\UrlTransformerInterface;
final class InternalUrlsCrawlProfile implements CrawlProfileInterface
{
    /**
     * @var UriInterface
     */
    protected $baseUrl;
    /**
     * @var UrlNormalizerInterface
     */
    protected $urlNormalizer;
    /**
     * @var UrlTransformerInterface
     */
    protected $urlTransformer;
    public function __construct(UriInterface $baseUrl, UriInterface $destinationUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->urlNormalizer = new InternalUrlNormalizer();
        $this->urlTransformer = new BasicUrlTransformer($destinationUrl);
    }
    /**
     * @param UriInterface $url
     */
    public function shouldCrawl($url) : bool
    {
        return $this->baseUrl->getHost() === $url->getHost();
    }
    /**
     * @param UriInterface $url
     */
    public function normalizeUrl($url) : UriInterface
    {
        return $this->urlNormalizer->normalize($url);
    }
    /**
     * @param UriInterface $url
     */
    public function transformUrl($url) : UriInterface
    {
        return $this->urlTransformer->transform($url);
    }
}
