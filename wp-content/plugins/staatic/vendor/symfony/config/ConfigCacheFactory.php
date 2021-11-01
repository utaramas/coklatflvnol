<?php

namespace Staatic\Vendor\Symfony\Component\Config;

class ConfigCacheFactory implements ConfigCacheFactoryInterface
{
    private $debug;
    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }
    /**
     * @param string $file
     * @param callable $callback
     */
    public function cache($file, $callback)
    {
        $cache = new ConfigCache($file, $this->debug);
        if (!$cache->isFresh()) {
            $callback($cache);
        }
        return $cache;
    }
}
