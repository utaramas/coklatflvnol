<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\WordPress\Bridge\CrawlProfile;
use Staatic\WordPress\Setting\Build\ExcludeUrlsSetting;

final class CrawlProfileFactory
{
    /**
     * @var ExcludeUrlsSetting
     */
    private $excludeUrls;

    public function __construct(ExcludeUrlsSetting $excludeUrls)
    {
        $this->excludeUrls = $excludeUrls;
    }

    public function __invoke(UriInterface $baseUrl, UriInterface $destinationUrl) : CrawlProfile
    {
        $excludeUrls = ExcludeUrlsSetting::resolvedValue($this->excludeUrls->value());
        $excludeUrls = apply_filters('staatic_exclude_urls', $excludeUrls);
        return new CrawlProfile($baseUrl, $destinationUrl, $excludeUrls);
    }
}
