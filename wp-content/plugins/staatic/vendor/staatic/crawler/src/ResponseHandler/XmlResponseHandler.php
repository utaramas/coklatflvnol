<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\ResponseUtil;
use Staatic\Crawler\UrlExtractor\UrlExtractorInterface;
use Staatic\Crawler\UrlExtractor\XmlUrlExtractor;
class XmlResponseHandler extends AbstractResponseHandler
{
    /**
     * @var UrlExtractorInterface
     */
    private $extractor;
    public function __construct(CrawlerInterface $crawler)
    {
        parent::__construct($crawler);
        $this->extractor = new XmlUrlExtractor($this->urlFilterCallback(), $this->urlReplaceCallback());
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if ($crawlUrl->response() && $this->isXmlResponse($crawlUrl->response())) {
            return $this->handleXmlResponse($crawlUrl);
        } else {
            return parent::handle($crawlUrl);
        }
    }
    private function isXmlResponse(ResponseInterface $response) : bool
    {
        return ResponseUtil::isXmlResponse($response);
    }
    private function handleXmlResponse(CrawlUrl $crawlUrl) : CrawlUrl
    {
        $readMaximumBytes = $this->crawler->maxResponseBodyInBytes();
        $responseBody = ResponseUtil::convertBodyToString($crawlUrl->response()->getBody(), $readMaximumBytes);
        $generator = $this->extractor->extract($responseBody, $crawlUrl->url());
        foreach ($generator as $url => $transformedUrl) {
            $this->crawler->addToCrawlQueue(CrawlUrl::create(new Uri($url), $transformedUrl, $crawlUrl));
        }
        $responseBody = Utils::streamFor($generator->getReturn());
        return $crawlUrl->withResponse($crawlUrl->response()->withBody($responseBody));
    }
}
