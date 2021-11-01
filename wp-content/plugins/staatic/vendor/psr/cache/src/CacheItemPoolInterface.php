<?php

namespace Staatic\Vendor\Psr\Cache;

interface CacheItemPoolInterface
{
    /**
     * @param string $key
     */
    public function getItem($key);
    /**
     * @param mixed[] $keys
     */
    public function getItems($keys = []);
    /**
     * @param string $key
     */
    public function hasItem($key);
    public function clear();
    /**
     * @param string $key
     */
    public function deleteItem($key);
    /**
     * @param mixed[] $keys
     */
    public function deleteItems($keys);
    /**
     * @param CacheItemInterface $item
     */
    public function save($item);
    /**
     * @param CacheItemInterface $item
     */
    public function saveDeferred($item);
    public function commit();
}
