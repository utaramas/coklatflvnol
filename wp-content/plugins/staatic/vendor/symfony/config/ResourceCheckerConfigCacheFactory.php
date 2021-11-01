<?php

namespace Staatic\Vendor\Symfony\Component\Config;

class ResourceCheckerConfigCacheFactory implements ConfigCacheFactoryInterface
{
    private $resourceCheckers = [];
    /**
     * @param mixed[] $resourceCheckers
     */
    public function __construct($resourceCheckers = [])
    {
        $this->resourceCheckers = $resourceCheckers;
    }
    /**
     * @param string $file
     * @param callable $callable
     */
    public function cache($file, $callable)
    {
        $cache = new ResourceCheckerConfigCache($file, $this->resourceCheckers);
        if (!$cache->isFresh()) {
            $callable($cache);
        }
        return $cache;
    }
}
