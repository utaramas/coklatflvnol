<?php

namespace Staatic\Crawler\UrlExtractor;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\UriHelper;
final class XmlSitemapIndexUrlExtractor implements UrlExtractorInterface
{
    /**
     * @var \Closure|null
     */
    private $filterCallback;
    /**
     * @var \Closure|null
     */
    private $replaceCallback;
    /**
     * @param callable|null $filterCallback
     * @param callable|null $replaceCallback
     */
    public function __construct($filterCallback = null, $replaceCallback = null)
    {
        $callable = $filterCallback;
        $this->filterCallback = $filterCallback ? function () use ($callable) {
            return $callable(...func_get_args());
        } : null;
        $callable = $replaceCallback;
        $this->replaceCallback = $replaceCallback ? function () use ($callable) {
            return $callable(...func_get_args());
        } : null;
    }
    /**
     * @param string $source
     * @param UriInterface $baseUrl
     */
    public function extract($source, $baseUrl) : \Generator
    {
        $yieldUrls = [];
        \preg_replace_callback('~<loc>([^<]+)</loc>~', function ($match) use(&$yieldUrls, $baseUrl) {
            $extractedUrl = \trim($match[1]);
            if (!UriHelper::isValidUrl($extractedUrl)) {
                return $match[0];
            }
            try {
                $resolvedUrl = UriResolver::resolve($baseUrl, new Uri($extractedUrl));
            } catch (\InvalidArgumentException $e) {
                return $match[0];
            }
            if ($this->filterCallback && !($this->filterCallback)($resolvedUrl)) {
                return \str_replace($extractedUrl, (string) $resolvedUrl, $match[0]);
            }
            $transformedUrl = $this->replaceCallback ? ($this->replaceCallback)($resolvedUrl) : $resolvedUrl;
            $yieldUrls[(string) $resolvedUrl] = $transformedUrl;
            return \str_replace($extractedUrl, (string) $transformedUrl, $match[0]);
        }, $source);
        foreach ($yieldUrls as $extractedUrl => $transformedUrl) {
            (yield $extractedUrl => $transformedUrl);
        }
        return $source;
    }
}
