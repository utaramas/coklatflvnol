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
final class AdditionalRedirectsPostProcessor implements PostProcessorInterface, LoggerAwareInterface
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
     * @var mixed[]
     */
    private $additionalRedirects;
    /**
     * @var UriInterface
     */
    private $baseUrl;
    public function __construct(ResultRepositoryInterface $resultRepository, ResourceRepositoryInterface $resourceRepository, array $additionalRedirects, UriInterface $baseUrl)
    {
        $this->logger = new NullLogger();
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->additionalRedirects = $additionalRedirects;
        $this->baseUrl = $baseUrl;
    }
    public function createsOrRemovesResults() : bool
    {
        return \true;
    }
    /**
     * @param string $buildId
     * @return void
     */
    public function apply($buildId)
    {
        $this->logger->info(\sprintf('Applying additional redirects post processor (%d redirects)', \count($this->additionalRedirects)), ['buildId' => $buildId]);
        foreach ($this->additionalRedirects as $path => $detail) {
            $url = $this->baseUrl->withPath($path);
            $existingResult = $this->resultRepository->findOneByBuildIdAndUrl($buildId, $url);
            if ($existingResult) {
                $this->logger->warning(\sprintf('Skipping additional redirect with URL "%s"; a result with the same URL already exists', (string) $url), ['buildId' => $buildId]);
                continue;
            }
            $this->logger->debug(\sprintf('Adding result for redirect with URL "%s", redirecting to "%s"', (string) $url, $detail['redirectUrl']), ['buildId' => $buildId]);
            $resource = $this->createResource($url, Utils::streamFor());
            $this->createResult($buildId, $resource, $url, new Uri($detail['redirectUrl']), $detail['statusCode']);
        }
    }
    private function createResource(UriInterface $url, StreamInterface $content) : Resource
    {
        $resource = Resource::create(\sha1($url), $content);
        $this->resourceRepository->write($resource);
        return $resource;
    }
    private function createResult(string $buildId, Resource $resource, UriInterface $url, UriInterface $redirectUrl, int $statusCode) : Result
    {
        $result = Result::create($this->resultRepository->nextId(), $buildId, $url, $resource, ['statusCode' => $statusCode, 'redirectUrl' => $redirectUrl]);
        $this->resultRepository->add($result);
        return $result;
    }
}
