<?php

namespace Staatic\Crawler\UrlExtractor;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\voku\helper\DomParserInterface;
use Staatic\Vendor\voku\helper\HtmlDomParser;
use Staatic\Crawler\UriHelper;
use Staatic\Crawler\UrlExtractor\Mapping\HtmlUrlExtractorMapping;
final class HtmlUrlExtractor implements UrlExtractorInterface
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
     * @var HtmlUrlExtractorMapping
     */
    private $mapping;
    /**
     * @var DomParserInterface
     */
    private $dom;
    /**
     * @var UrlExtractorInterface
     */
    private $cssExtractor;
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
        $this->mapping = new HtmlUrlExtractorMapping();
        $this->dom = new HtmlDomParser();
        $this->cssExtractor = new CssUrlExtractor($filterCallback, $replaceCallback);
    }
    /**
     * @param string $source
     * @param UriInterface $baseUrl
     */
    public function extract($source, $baseUrl) : \Generator
    {
        $this->dom->loadHtml($source);
        foreach ($this->mapping as $tagName => $attributes) {
            foreach ($this->dom->find($tagName) as $element) {
                yield from $this->handleElementAttributes($element, $attributes, $baseUrl);
            }
        }
        foreach ($this->dom->find('style') as $element) {
            $generator = $this->cssExtractor->extract($element->textContent, $baseUrl);
            yield from $generator;
            $element->textContent = $generator->getReturn();
        }
        $newSource = $this->dom->html();
        if (!$newSource) {
        }
        return $newSource ?: $source;
    }
    private function handleElementAttributes($element, array $attributes, UriInterface $baseUrl) : \Generator
    {
        if ($element->hasAttribute('style')) {
            $attributeValue = $element->getAttribute('style');
            $attributeValueBefore = $attributeValue;
            $generator = $this->cssExtractor->extract($attributeValue, $baseUrl);
            yield from $generator;
            $attributeValue = $generator->getReturn();
            if ($attributeValue !== $attributeValueBefore) {
                $element->setAttribute('style', $attributeValue);
            }
        }
        foreach ($attributes as $attributeName) {
            if (!$element->hasAttribute($attributeName)) {
                continue;
            }
            $attributeValue = $element->getAttribute($attributeName);
            $attributeValueBefore = $attributeValue;
            if ($attributeName === 'srcset') {
                $extractedUrls = $this->extractUrlsFromSrcset($attributeValue);
            } else {
                $extractedUrls = [$attributeValue];
            }
            foreach ($extractedUrls as $extractedUrl) {
                $extractedUrl = \trim($extractedUrl);
                if (!UriHelper::isValidUrl($extractedUrl)) {
                    continue;
                }
                $preserveEmptyFragment = \substr($extractedUrl, -1, 1) === '#';
                try {
                    $resolvedUrl = UriResolver::resolve($baseUrl, new Uri($extractedUrl));
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
                if ($this->filterCallback && !($this->filterCallback)($resolvedUrl)) {
                    $attributeValue = \str_replace($extractedUrl, (string) $resolvedUrl . ($preserveEmptyFragment ? '#' : ''), $attributeValue);
                    continue;
                }
                $transformedUrl = $this->replaceCallback ? ($this->replaceCallback)($resolvedUrl) : $resolvedUrl;
                (yield (string) $resolvedUrl => $transformedUrl);
                $attributeValue = \str_replace($extractedUrl, (string) $transformedUrl . ($preserveEmptyFragment ? '#' : ''), $attributeValue);
            }
            if ($attributeValue !== $attributeValueBefore) {
                $element->setAttribute($attributeName, $attributeValue);
            }
        }
    }
    private function extractUrlsFromSrcset(string $srcset) : array
    {
        \preg_match_all('~([^\\s]+)\\s*(?:[\\d\\.]+[wx])?,*~m', $srcset, $matches);
        return $matches[1];
    }
}
