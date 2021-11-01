<?php

namespace Staatic\Framework\ConfigGenerator;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\StreamInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Framework\Result;
final class NetlifyConfigGenerator extends AbstractConfigGenerator
{
    /**
     * @var mixed[]
     */
    private $headerRules = [];
    /**
     * @var mixed[]
     */
    private $redirectRules = [];
    /**
     * @param UriInterface|null $notFoundUrl
     */
    public function __construct($notFoundUrl = null)
    {
        if ($notFoundUrl) {
            $this->headerRules[$notFoundUrl->getPath()] = $this->generateRedirectRule('/*', new Uri($notFoundUrl->getPath()), 404, \false);
        }
    }
    /**
     * @param Result $result
     * @return void
     */
    public function processResult($result)
    {
        if ($result->redirectUrl()) {
            $this->redirectRules[$result->url()->getPath()] = $this->generateRedirectRule($result->url()->getPath(), $result->redirectUrl(), $result->statusCode());
        } elseif ($this->hasNonStandardMimeType($result) || $this->hasNonUtf8Charset($result)) {
            $this->headerRules[$result->url()->getPath()] = $this->generateHeaderRulesForResult($result);
        }
    }
    public function getFiles() : array
    {
        return ['/netlify.toml' => $this->generateConfigFile()];
    }
    private function generateRedirectRule(string $path, UriInterface $redirectUrl, int $statusCode, bool $force = \true) : string
    {
        $redirectRules = [\sprintf('from = "%s"', $path), \sprintf('to = "%s"', $redirectUrl), \sprintf('status = %d', $statusCode), \sprintf('force = %s', $force ? 'true' : 'false')];
        return \sprintf("[[redirects]]\n  %s\n", \implode("\n  ", $redirectRules));
    }
    private function generateHeaderRulesForResult(Result $result) : string
    {
        $headerValues = [\sprintf('Content-Type = "%s"', $result->charset() ? \sprintf('%s; charset=%s', $result->mimeType(), $result->charset()) : $result->mimeType())];
        $headerRules = [\sprintf('for = "%s"', $result->url()->getPath()), \sprintf('[headers.values]', $result->redirectUrl()), '  ' . \implode('  ', $headerValues)];
        return \sprintf("[[headers]]\n  %s\n", \implode("\n  ", $headerRules));
    }
    private function generateConfigFile() : StreamInterface
    {
        $stream = Utils::streamFor();
        foreach ($this->redirectRules as $redirectRule) {
            $stream->write($redirectRule);
        }
        foreach ($this->headerRules as $headerRule) {
            $stream->write($headerRule);
        }
        $stream->rewind();
        return $stream;
    }
}
