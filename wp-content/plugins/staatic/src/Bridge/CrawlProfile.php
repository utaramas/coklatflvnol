<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge;

use Staatic\Crawler\CrawlProfile\CrawlProfileInterface;
use Staatic\Crawler\CrawlProfile\WordPressCrawlProfile;
use Staatic\Vendor\Psr\Http\Message\UriInterface;

final class CrawlProfile implements CrawlProfileInterface
{
    /**
     * @var CrawlProfileInterface
     */
    private $decoratedProfile;

    /**
     * @var mixed[]
     */
    private $simpleExcludeRules = [];

    /**
     * @var mixed[]
     */
    private $wildcardExcludeRules = [];

    public function __construct(UriInterface $baseUrl, UriInterface $destinationUrl, array $excludeUrls = [])
    {
        $this->decoratedProfile = new WordPressCrawlProfile($baseUrl, $destinationUrl);
        $this->initializeExcludeRules($excludeUrls);
    }

    /**
     * @return void
     */
    private function initializeExcludeRules(array $excludeUrls)
    {
        foreach ($excludeUrls as $excludeUrl) {
            if (\strstr($excludeUrl, '*') === \false) {
                $this->simpleExcludeRules[] = \mb_strtolower($excludeUrl);
            } else {
                $this->wildcardExcludeRules[] = \sprintf(
                    '~^%s$~i',
                    \str_replace('\\*', '.+?', \preg_quote($excludeUrl, '~'))
                );
            }
        }
    }

    /**
     * @param UriInterface $url
     */
    public function shouldCrawl($url) : bool
    {
        $withoutHost = (string) $url->withScheme('')->withHost('')->withPort(null);
        // Simple exclude rules.
        foreach ($this->simpleExcludeRules as $rule) {
            if (\strcasecmp($withoutHost, $rule) === 0) {
                return \false;
            }
        }
        // Wildcard exclude rules.
        foreach ($this->wildcardExcludeRules as $rule) {
            if (\preg_match($rule, $withoutHost) === 1) {
                return \false;
            }
        }
        return $this->decoratedProfile->shouldCrawl($url);
    }

    /**
     * @param UriInterface $url
     */
    public function normalizeUrl($url) : UriInterface
    {
        return $this->decoratedProfile->normalizeUrl($url);
    }

    /**
     * @param UriInterface $url
     */
    public function transformUrl($url) : UriInterface
    {
        return $this->decoratedProfile->transformUrl($url);
    }
}
