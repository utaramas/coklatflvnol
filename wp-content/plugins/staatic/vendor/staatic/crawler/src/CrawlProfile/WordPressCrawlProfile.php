<?php

namespace Staatic\Crawler\CrawlProfile;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\UrlNormalizer\UrlNormalizerInterface;
use Staatic\Crawler\UrlNormalizer\WordPressUrlNormalizer;
final class WordPressCrawlProfile implements CrawlProfileInterface
{
    /**
     * @var CrawlProfileInterface
     */
    private $decoratedProfile;
    /**
     * @var UrlNormalizerInterface
     */
    protected $urlNormalizer;
    public function __construct(UriInterface $baseUrl, UriInterface $destinationUrl)
    {
        $this->decoratedProfile = new InternalUrlsCrawlProfile($baseUrl, $destinationUrl);
        $this->urlNormalizer = new WordPressUrlNormalizer();
    }
    /**
     * @param UriInterface $url
     */
    public function shouldCrawl($url) : bool
    {
        $path = $url->getPath();
        if (\preg_match('~^/xmlrpc\\.php~', $path)) {
            return \false;
        }
        if (\preg_match('~^/wp-comments-post\\.php~', $path)) {
            return \false;
        }
        if (\preg_match('~^/wp-login\\.php~', $path)) {
            return \false;
        }
        $withoutHost = $url->withScheme('')->withHost('')->withPort(null);
        if (\preg_match('~^/\\?p=\\d+~', (string) $withoutHost)) {
            return \false;
        }
        return $this->decoratedProfile->shouldCrawl($url);
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
        return $this->decoratedProfile->transformUrl($url);
    }
}
