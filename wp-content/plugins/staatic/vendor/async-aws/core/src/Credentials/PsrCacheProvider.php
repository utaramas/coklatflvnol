<?php

declare (strict_types=1);
namespace Staatic\Vendor\AsyncAws\Core\Credentials;

use Staatic\Vendor\AsyncAws\Core\Configuration;
use Staatic\Vendor\Psr\Cache\CacheException;
use Staatic\Vendor\Psr\Cache\CacheItemPoolInterface;
use Staatic\Vendor\Psr\Log\LoggerInterface;
final class PsrCacheProvider implements CredentialProvider
{
    private $cache;
    private $decorated;
    private $logger;
    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(CredentialProvider $decorated, CacheItemPoolInterface $cache, $logger = null)
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
        $this->logger = $logger;
    }
    /**
     * @param Configuration $configuration
     * @return Credentials|null
     */
    public function getCredentials($configuration)
    {
        try {
            return $this->getFromCache($configuration);
        } catch (CacheException $e) {
            if (null !== $this->logger) {
                $this->logger->error('Failed to get AWS credentials from cache.', ['exception' => $e]);
            }
            return $this->decorated->getCredentials($configuration);
        }
    }
    /**
     * @return Credentials|null
     */
    private function getFromCache(Configuration $configuration)
    {
        $item = $this->cache->getItem('AsyncAws.Credentials.' . \sha1(\serialize([$configuration, \get_class($this->decorated)])));
        if (!$item->isHit()) {
            $item->set($credential = $this->decorated->getCredentials($configuration));
            if (null !== $credential && null !== ($exp = $credential->getExpireDate())) {
                $item->expiresAt($exp);
                $this->cache->save($item);
            }
        }
        return $item->get();
    }
}
