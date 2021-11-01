<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Crawler\UrlTransformer\BasicUrlTransformer;
use Staatic\Crawler\UrlTransformer\UrlTransformerInterface;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\WordPress\Setting\Build\DestinationUrlSetting;

final class UrlTransformerFactory
{
    /**
     * @var DestinationUrlSetting
     */
    private $destinationUrl;

    public function __construct(DestinationUrlSetting $destinationUrl)
    {
        $this->destinationUrl = $destinationUrl;
    }

    public function __invoke() : UrlTransformerInterface
    {
        return new BasicUrlTransformer(new Uri($this->destinationUrl->value()));
    }
}
