<?php

namespace Staatic\Vendor\Psr\Cache;

interface CacheItemInterface
{
    public function getKey();
    public function get();
    public function isHit();
    /**
     * @param mixed $value
     */
    public function set($value);
    /**
     * @param \DateTimeInterface|null $expiration
     */
    public function expiresAt($expiration);
    /**
     * @param int|\DateInterval|null $time
     */
    public function expiresAfter($time);
}
