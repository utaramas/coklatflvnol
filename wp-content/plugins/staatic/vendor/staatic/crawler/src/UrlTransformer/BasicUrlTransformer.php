<?php

namespace Staatic\Crawler\UrlTransformer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class BasicUrlTransformer implements UrlTransformerInterface
{
    /**
     * @var UriInterface
     */
    private $destinationUrl;
    public function __construct(UriInterface $destinationUrl)
    {
        $this->destinationUrl = $destinationUrl;
    }
    /**
     * @param UriInterface $url
     */
    public function transform($url) : UriInterface
    {
        $transformedUrl = clone $url;
        if ($url->getScheme() && $url->getScheme() !== $this->destinationUrl->getScheme()) {
            $transformedUrl = $transformedUrl->withScheme($this->destinationUrl->getScheme());
        }
        if ($url->getHost()) {
            if ($url->getHost() !== $this->destinationUrl->getHost()) {
                $transformedUrl = $transformedUrl->withHost($this->destinationUrl->getHost());
            }
            if ($url->getPort() !== $this->destinationUrl->getPort()) {
                $transformedUrl = $transformedUrl->withPort($this->destinationUrl->getPort());
            }
        }
        return $transformedUrl;
    }
}
