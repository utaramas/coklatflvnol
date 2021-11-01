<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
class XmlSitemapTaggerResponseHandler extends AbstractResponseHandler
{
    const SITEMAP_XML_TAG = CrawlerInterface::TAG_SITEMAP_XML;
    const SITEMAP_XML_PATH = '/sitemap.xml';
    public function __construct(CrawlerInterface $crawler)
    {
        parent::__construct($crawler);
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if ($crawlUrl->url()->getPath() === self::SITEMAP_XML_PATH) {
            $crawlUrl = $crawlUrl->withTags(\array_merge($crawlUrl->tags(), [self::SITEMAP_XML_TAG]));
        }
        return parent::handle($crawlUrl);
    }
}
