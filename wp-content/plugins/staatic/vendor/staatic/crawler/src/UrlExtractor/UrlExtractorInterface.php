<?php

namespace Staatic\Crawler\UrlExtractor;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
interface UrlExtractorInterface
{
    /**
     * @param string $source
     * @param UriInterface $baseUrl
     */
    public function extract($source, $baseUrl) : \Generator;
}
