<?php

namespace Staatic\Crawler\UrlExtractor;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\UriHelper;
final class CssUrlExtractor implements UrlExtractorInterface
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
     * @var string
     */
    private $source;
    /**
     * @var UriInterface
     */
    private $baseUrl;
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
        $this->source = $source;
        $this->baseUrl = $baseUrl;
        foreach ($this->getPatterns() as $pattern) {
            yield from $this->extractUsingPattern($pattern);
        }
        return $this->source;
    }
    private function extractUsingPattern(string $pattern) : \Generator
    {
        $yieldUrls = [];
        $this->source = \preg_replace_callback($pattern, function ($match) use(&$yieldUrls) {
            list($fullMatch, $extractedUrl) = $match;
            if (!UriHelper::isValidUrl($extractedUrl)) {
                return $fullMatch;
            }
            $preserveEmptyFragment = \substr($extractedUrl, -1, 1) === '#';
            try {
                $resolvedUrl = UriResolver::resolve($this->baseUrl, new Uri($extractedUrl));
            } catch (\InvalidArgumentException $e) {
                return $fullMatch;
            }
            if ($this->filterCallback && !($this->filterCallback)($resolvedUrl)) {
                return \str_replace($extractedUrl, (string) $resolvedUrl . ($preserveEmptyFragment ? '#' : ''), $fullMatch);
            }
            $transformedUrl = $this->replaceCallback ? ($this->replaceCallback)($resolvedUrl) : $resolvedUrl;
            $yieldUrls[(string) $resolvedUrl] = $transformedUrl;
            return \str_replace($extractedUrl, (string) $transformedUrl . ($preserveEmptyFragment ? '#' : ''), $fullMatch);
        }, $this->source);
        foreach ($yieldUrls as $extractedUrl => $transformedUrl) {
            (yield $extractedUrl => $transformedUrl);
        }
    }
    private function getPatterns() : array
    {
        $patterns = [];
        $patterns[] = '~url\\(\\s*["\']?([^)"\']+\\s*)~';
        $patterns[] = '~@import\\s+["\'](\\s*[^"\']+\\s*)~';
        return $patterns;
    }
}
