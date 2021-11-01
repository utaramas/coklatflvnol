<?php

namespace Staatic\Crawler\UrlNormalizer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class InternalUrlNormalizer implements UrlNormalizerInterface
{
    /**
     * @var UrlNormalizerInterface
     */
    private $decoratedNormalizer;
    public function __construct()
    {
        $this->decoratedNormalizer = new BasicUrlNormalizer();
    }
    /**
     * @param UriInterface $url
     */
    public function normalize($url) : UriInterface
    {
        $url = $this->decoratedNormalizer->normalize($url);
        if ($url->getScheme()) {
            $url = $url->withScheme('');
        }
        if ($url->getUserInfo()) {
            $url = $url->withUserInfo('');
        }
        if ($url->getHost()) {
            $url = $url->withHost('');
        }
        if ($url->getPort()) {
            $url = $url->withPort(null);
        }
        return $url;
    }
}
