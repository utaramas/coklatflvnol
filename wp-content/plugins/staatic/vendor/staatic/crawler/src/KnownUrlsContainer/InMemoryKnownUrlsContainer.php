<?php

namespace Staatic\Crawler\KnownUrlsContainer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
class InMemoryKnownUrlsContainer implements KnownUrlsContainerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var mixed[]
     */
    private $urls = [];
    public function __construct()
    {
        $this->logger = new NullLogger();
    }
    /**
     * @return void
     */
    public function clear()
    {
        $this->urls = [];
    }
    /**
     * @param UriInterface $url
     * @return void
     */
    public function add($url)
    {
        if ($this->isKnown($url)) {
            throw new \RuntimeException(\sprintf('Url "%s" is already known', $url));
        }
        $this->logger->debug(\sprintf('Adding url "%s" to container', $url));
        $this->urls[(string) $url] = \true;
    }
    /**
     * @param UriInterface $url
     */
    public function isKnown($url) : bool
    {
        return isset($this->urls[(string) $url]);
    }
    public function count()
    {
        return \count($this->urls);
    }
}
