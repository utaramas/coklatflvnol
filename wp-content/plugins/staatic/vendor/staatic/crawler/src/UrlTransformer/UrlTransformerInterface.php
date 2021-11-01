<?php

namespace Staatic\Crawler\UrlTransformer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
interface UrlTransformerInterface
{
    /**
     * @param UriInterface $url
     */
    public function transform($url) : UriInterface;
}
