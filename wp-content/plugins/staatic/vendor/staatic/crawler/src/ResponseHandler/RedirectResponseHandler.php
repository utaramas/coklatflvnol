<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\ResponseUtil;
class RedirectResponseHandler extends AbstractResponseHandler
{
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if ($crawlUrl->response() && ResponseUtil::isRedirectResponse($crawlUrl->response())) {
            return $this->handleRedirectResponse($crawlUrl);
        } else {
            return parent::handle($crawlUrl);
        }
    }
    private function handleRedirectResponse(CrawlUrl $crawlUrl) : CrawlUrl
    {
        $redirectUrl = ResponseUtil::getRedirectUrl($crawlUrl->response());
        if (!$redirectUrl) {
            return $crawlUrl;
        }
        $resolvedUrl = UriResolver::resolve($crawlUrl->url(), $redirectUrl);
        if (!$this->crawler->shouldCrawl($resolvedUrl)) {
            return $crawlUrl;
        }
        $transformedUrl = $this->crawler->transformUrl($resolvedUrl);
        $crawlUrl = $crawlUrl->withResponse($crawlUrl->response()->withHeader('Location', (string) $transformedUrl));
        if ($this->hasExceededMaxRedirects($crawlUrl)) {
            return $crawlUrl;
        }
        $this->crawler->addToCrawlQueue(CrawlUrl::create($resolvedUrl, $transformedUrl, $crawlUrl, \true, $crawlUrl->tags()));
        return $crawlUrl;
    }
    private function hasExceededMaxRedirects(CrawlUrl $crawlUrl) : bool
    {
        $maxRedirects = $this->crawler->maxRedirects();
        return $crawlUrl->redirectLevel() >= $maxRedirects;
    }
}
