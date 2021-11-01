<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge;

use Staatic\Crawler\KnownUrlsContainer\KnownUrlsContainerInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;

final class KnownUrlsContainer implements KnownUrlsContainerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $tableName;

    public function __construct(\wpdb $wpdb, string $tableName = 'staatic_known_urls')
    {
        $this->logger = new NullLogger();
        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix . $tableName;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->logger->debug('Clearing container');
        $result = $this->wpdb->query("DELETE FROM {$this->tableName}");
        if ($result === \false) {
            throw new \RuntimeException(\sprintf('Unable to clear container: %s', $this->wpdb->last_error));
        }
    }

    /**
     * @param UriInterface $url
     * @return void
     */
    public function add($url)
    {
        $this->logger->debug(\sprintf('Adding url "%s" to container', $url));
        $result = $this->wpdb->insert($this->tableName, [
            'hash' => \md5((string) $url),
            'url' => (string) $url
        ]);
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to add url "%s" to container: %s',
                $url,
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @param UriInterface $url
     */
    public function isKnown($url) : bool
    {
        return (bool) $this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->tableName} WHERE hash = %s", \md5((string) $url))
        );
    }

    public function count()
    {
        return (int) $this->wpdb->get_var("\n            SELECT COUNT(*)\n            FROM {$this->tableName}");
    }
}
