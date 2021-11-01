<?php

namespace Staatic\Framework\PostProcessor;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\StreamInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Framework\Resource;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\Result;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
final class XmlSitemapGeneratorPostProcessor implements PostProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;
    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;
    /**
     * @var UriInterface
     */
    private $baseUrl;
    public function __construct(ResultRepositoryInterface $resultRepository, ResourceRepositoryInterface $resourceRepository, UriInterface $baseUrl = null)
    {
        $this->logger = new NullLogger();
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->baseUrl = $baseUrl ?: new Uri();
    }
    public function createsOrRemovesResults() : bool
    {
        return \false;
    }
    /**
     * @param string $buildId
     * @return void
     */
    public function apply($buildId)
    {
        $this->logger->info('Applying xml sitemap generator post processor', ['buildId' => $buildId]);
        $content = Utils::streamFor();
        $content->write(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

XML
);
        foreach ($this->getSitemapResults($buildId) as $result) {
            $escapedUrl = \htmlspecialchars($result->url());
            $content->write(<<<XML
    <url>
        <loc>{$escapedUrl}</loc>
    </url>

XML
);
        }
        $content->write(<<<XML
</urlset>
XML
);
        $content->rewind();
        $url = $this->baseUrl->withPath('/sitemap.xml');
        $result = $this->resultRepository->findOneByBuildIdAndUrl($buildId, $url);
        if ($result) {
            $resource = $this->resourceRepository->find($result->resourceId());
            $resource->replace($content);
            $this->resourceRepository->write($resource);
            $result->setMd5($resource->md5());
            $result->setSha1($resource->sha1());
            $result->setSize($resource->size());
            $this->resultRepository->update($result);
        } else {
            $resource = $this->createResource($url, $content);
            $result = $this->createResult($buildId, $resource, $url);
        }
    }
    private function getSitemapResults(string $buildId) : \Generator
    {
        $results = $this->resultRepository->findByBuildId($buildId);
        foreach ($results as $result) {
            if ($result->statusCode() !== 200) {
                continue;
            }
            if ($result->mimeType() !== 'text/html') {
                continue;
            }
            (yield $result);
        }
    }
    private function createResource(UriInterface $url, StreamInterface $content) : Resource
    {
        $resource = Resource::create(\sha1($url), $content);
        $this->resourceRepository->write($resource);
        return $resource;
    }
    private function createResult(string $buildId, Resource $resource, UriInterface $url) : Result
    {
        $result = Result::create($this->resultRepository->nextId(), $buildId, $url, $resource, ['mimeType' => 'text/xml', 'charset' => 'UTF-8']);
        $this->resultRepository->add($result);
        return $result;
    }
}
