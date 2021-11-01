<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\ResponseUtil;
use Staatic\Crawler\UrlExtractor\HtmlUrlExtractor;
use Staatic\Crawler\UrlExtractor\UrlExtractorInterface;
class HtmlResponseHandler extends AbstractResponseHandler
{
    /**
     * @var UrlExtractorInterface
     */
    private $extractor;
    public function __construct(CrawlerInterface $crawler)
    {
        parent::__construct($crawler);
        $this->extractor = new HtmlUrlExtractor($this->urlFilterCallback(), $this->urlReplaceCallback());
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if ($crawlUrl->response() && $this->isHtmlResponse($crawlUrl->response())) {
            return $this->handleHtmlResponse($crawlUrl);
        } else {
            return parent::handle($crawlUrl);
        }
    }
    private function isHtmlResponse(ResponseInterface $response) : bool
    {
        return ResponseUtil::getMimeType($response) === 'text/html';
    }
    private function handleHtmlResponse(CrawlUrl $crawlUrl) : CrawlUrl
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
