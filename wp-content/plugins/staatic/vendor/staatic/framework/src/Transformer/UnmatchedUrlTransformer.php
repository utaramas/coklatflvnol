<?php

namespace Staatic\Framework\Transformer;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\ResponseUtil;
use Staatic\Crawler\UrlTransformer\UrlTransformerInterface;
use Staatic\Framework\Resource;
use Staatic\Framework\Result;
final class UnmatchedUrlTransformer implements TransformerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var UriInterface
     */
    private $sourceUrl;
    /**
     * @var UrlTransformerInterface
     */
    private $urlTransformer;
    public function __construct(UriInterface $sourceUrl, UrlTransformerInterface $urlTransformer)
    {
        $this->logger = new NullLogger();
        $this->sourceUrl = $sourceUrl;
        $this->urlTransformer = $urlTransformer;
    }
    /**
     * @param Result $result
     */
    public function supports($result) : bool
    {
        if (!$result->size()) {
            return \false;
        }
        $supportedMimeTypes = \array_merge(ResponseUtil::JAVASCRIPT_MIME_TYPES, ResponseUtil::XML_MIME_TIMES, ['text/css', 'text/html']);
        return \in_array($result->mimeType(), $supportedMimeTypes);
    }
    /**
     * @param Result $result
     * @param Resource $resource
     * @return void
     */
    public function transform($result, $resource)
    {
        $this->logger->info(\sprintf('Applying unmatched url transformation on %s', $result->url()));
        $replacedContent = $this->replaceUrls((string) $resource->content());
        $resource->replace(Utils::streamFor($replacedContent));
    }
    private function replaceUrls(string $source) : string
    {
        $sourceUrlAuthority = $this->sourceUrl->getAuthority();
        $replacements = ['~((?:https?:)?//' . \preg_quote($sourceUrlAuthority, '~') . ')([/"\' ])~i' => function ($matches) {
            return $this->replaceUrl($matches[1]) . $matches[2];
        }, '~((?:https?:)?\\\\/\\\\/' . \preg_quote($this->jsEncode($sourceUrlAuthority), '~') . ')(\\\\/|["\' ])~i' => function ($matches) {
            $replacedUrl = $this->replaceUrl($this->jsDecode($matches[1]));
            return $this->jsEncode($replacedUrl) . $matches[2];
        }, '~((?:https?%3A)?%2F%2F' . \preg_quote(\rawurlencode($sourceUrlAuthority), '~') . ')(%2F|["\' ])~i' => function ($matches) {
            $replacedUrl = $this->replaceUrl(\rawurldecode($matches[1]));
            return \rawurlencode($replacedUrl) . $matches[2];
        }];
        $numReplacements = 0;
        $source = \preg_replace_callback_array($replacements, $source, -1, $numReplacements);
        $this->logger->debug(\sprintf('Applied %d unmatched url replacements', $numReplacements));
        return $source;
    }
    private function jsEncode(string $string) : string
    {
        return \str_replace('/', '\\/', $string);
    }
    private function jsDecode(string $string) : string
    {
        return \str_replace('\\/', '/', $string);
    }
    private function replaceUrl(string $matchedUrl) : string
    {
        return (string) $this->urlTransformer->transform(new Uri($matchedUrl));
    }
}
