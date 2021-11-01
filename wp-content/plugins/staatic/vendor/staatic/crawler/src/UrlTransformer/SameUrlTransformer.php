<?php

namespace Staatic\Crawler\UrlTransformer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class SameUrlTransformer implements UrlTransformerInterface
{
    /**
     * @param UriInterface $url
     */
    public function transform($url) : UriInterface
    {
        return $url;
    }
}
