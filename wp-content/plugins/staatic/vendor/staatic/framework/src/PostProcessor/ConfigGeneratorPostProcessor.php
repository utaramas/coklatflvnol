<?php

namespace Staatic\Framework\PostProcessor;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Http\Message\StreamInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Framework\ConfigGenerator\ConfigGeneratorInterface;
use Staatic\Framework\Resource;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\Result;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
final class ConfigGeneratorPostProcessor implements PostProcessorInterface, LoggerAwareInterface
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
     * @var ConfigGeneratorInterface
     */
    private $configGenerator;
    public function __construct(ResultRepositoryInterface $resultRepository, ResourceRepositoryInterface $resourceRepository, ConfigGeneratorInterface $configGenerator)
    {
        $this->logger = new NullLogger();
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->configGenerator = $configGenerator;
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
        $this->logger->info(\sprintf('Applying config generator post processor (using %s)', \get_class($this->configGenerator)), ['buildId' => $buildId]);
        foreach ($this->resultRepository->findByBuildId($buildId) as $result) {
            $this->configGenerator->processResult($result);
        }
        foreach ($this->configGenerator->getFiles() as $path => $content) {
            $url = new Uri($path);
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
    }
    private function createResource(UriInterface $url, StreamInterface $content) : Resource
    {
        $resource = Resource::create(\sha1($url), $content);
        $this->resourceRepository->write($resource);
        return $resource;
    }
    private function createResult(string $buildId, Resource $resource, UriInterface $url) : Result
    {
        $result = Result::create($this->resultRepository->nextId(), $buildId, $url, $resource);
        $this->resultRepository->add($result);
        return $result;
    }
}
