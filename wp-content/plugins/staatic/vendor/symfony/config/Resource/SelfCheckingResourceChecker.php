<?php

namespace Staatic\Vendor\Symfony\Component\Config\Resource;

use Staatic\Vendor\Symfony\Component\Config\ResourceCheckerInterface;
class SelfCheckingResourceChecker implements ResourceCheckerInterface
{
    /**
     * @param ResourceInterface $metadata
     */
    public function supports($metadata)
    {
        return $metadata instanceof SelfCheckingResourceInterface;
    }
    /**
     * @param ResourceInterface $resource
     * @param int $timestamp
     */
    public function isFresh($resource, $timestamp)
    {
        return $resource->isFresh($timestamp);
    }
}
