<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\StreamInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\ResponseUtil;
use Staatic\Crawler\UrlExtractor\UrlExtractorInterface;
use Staatic\Crawler\UrlExtractor\XmlSitemapIndexUrlExtractor;
use Staatic\Crawler\UrlExtractor\XmlSitemapUrlSetUrlExtractor;
class XmlSitemapResponseHandler extends AbstractResponseHandler
{
    const SITEMAP_XML_TAG = CrawlerInterface::TAG_SITEMAP_XML;
    /**
     * @var UrlExtractorInterface
     */
    private $indexExtractor;
    /**
     * @var UrlExtractorInterface
     */
    private $urlSetExtractor;
    public function __construct(CrawlerInterface $crawler)
    {
        parent::__construct($crawler);
        $this->indexExtractor = new XmlSitemapIndexUrlExtractor($this->urlFilterCallback(), $this->urlReplaceCallback());
        $this->urlSetExtractor = new XmlSitemapUrlSetUrlExtractor($this->urlFilterCallback(), $this->urlReplaceCallback());
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        $isXmlSitemapResponse = $this->isXmlSitemapResponse($crawlUrl);
        if ($isXmlSitemapResponse && $this->isXmlSitemapIndexResponse($crawlUrl)) {
            $crawlUrl = $this->handleXmlSitemapIndexResponse($crawlUrl);
        } elseif ($isXmlSitemapResponse && $this->isXmlSitemapUrlSetResponse($crawlUrl)) {
            $crawlUrl = $this->handleXmlSitemapUrlSetResponse($crawlUrl);
        }
        return parent::handle($crawlUrl);
    }
    private function isXmlSitemapResponse(CrawlUrl $crawlUrl) : bool
    {
        if (!$crawlUrl->hasTag(self::SITEMAP_XML_TAG)) {
            return \false;
        }
        if (!$crawlUrl->response()) {
            return \false;
        }
        if (ResponseUtil::getMimeType($crawlUrl->response()) !== 'text/xml') {
            return \false;
        }
        return \true;
    }
    private function isXmlSitemapIndexResponse(CrawlUrl $crawlUrl) : bool
    {
        return $this->responseBodyContains($crawlUrl->response()->getBody(), '<sitemapindex');
    }
    private function isXmlSitemapUrlSetResponse(CrawlUrl $crawlUrl) : bool
    {
        return $this->responseBodyContains($crawlUrl->response()->getBody(), '<urlset');
    }
    /**
     * @param int|null $readMaximumBytes
     */
    private function responseBodyContains(StreamInterface $bodyStream, string $search, $readMaximumBytes = 4096) : bool
    {
        $responseBody = ResponseUtil::convertBodyToString($bodyStream, $readMaximumBytes);
        return \strstr($responseBody, $search) !== \false;
    }
    private function handleXmlSitemapIndexResponse(CrawlUrl $crawlUrl) : CrawlUrl
    {
        $readMaximumBytes = $this->crawler->maxResponseBodyInBytes();
        $responseBody = ResponseUtil::convertBodyToString($crawlUrl->response()->getBody(), $readMaximumBytes);
        $generator = $this->indexExtractor->extract($responseBody, $crawlUrl->url());
        foreach ($generator as $url => $transformedUrl) {
            $this->crawler->addToCrawlQueue(CrawlUrl::create(new Uri($url), $transformedUrl, $crawlUrl, \false, $crawlUrl->tags()));
        }
        $responseBody = Utils::streamFor($generator->getReturn());
        return $crawlUrl->withResponse($crawlUrl->response()->withBody($responseBody));
    }
    private function handleXmlSitemapUrlSetResponse(CrawlUrl $crawlUrl) : CrawlUrl
    {
        $readMaximumBytes = $this->crawler->maxResponseBodyInBytes();
        $responseBody = ResponseUtil::convertBodyToString($crawlUrl->response()->getBody(), $readMaximumBytes);
        $generator = $this->urlSetExtractor->extract($responseBody, $crawlUrl->url());
        foreach ($generator as $url => $transformedUrl) {
            $this->crawler->addToCrawlQueue(CrawlUrl::create(new Uri($url), $transformedUrl, $crawlUrl));
        }
        $responseBody = Utils::streamFor($generator->getReturn());
        return $crawlUrl->withResponse($crawlUrl->response()->withBody($responseBody));
    }
}
