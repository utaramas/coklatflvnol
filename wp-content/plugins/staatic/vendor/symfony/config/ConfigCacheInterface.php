<?php

namespace Staatic\Vendor\Symfony\Component\Config;

use Staatic\Vendor\Symfony\Component\Config\Resource\ResourceInterface;
interface ConfigCacheInterface
{
    public function getPath();
    public function isFresh();
    /**
     * @param string $content
     * @param mixed[]|null $metadata
     */
    public function write($content, $metadata = null);
}
