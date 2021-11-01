<?php

namespace Staatic\Framework\ResourceRepository;

use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Framework\Resource;
final class InMemoryResourceRepository implements ResourceRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var mixed[]
     */
    private $resources = [];
    public function __construct()
    {
        $this->logger = new NullLogger();
    }
    /**
     * @param Resource $resource
     * @return void
     */
    public function write($resource)
    {
        $this->logger->debug(\sprintf('Adding resource #%s', $resource->id()), ['resourceId' => $resource->id()]);
        $this->resources[$resource->id()] = $resource;
    }
    /**
     * @param string $resourceId
     * @return Resource|null
     */
    public function find($resourceId)
    {
        return $this->resources[$resourceId] ?? null;
    }
}
