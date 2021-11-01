<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge;

use Staatic\Crawler\CrawlQueue\CrawlQueueInterface;
use Staatic\Crawler\CrawlUrl;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\UuidV4;

final class CrawlQueue implements CrawlQueueInterface, LoggerAwareInterface
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

    public function __construct(\wpdb $wpdb, string $tableName = 'staatic_crawl_queue')
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
        $this->logger->debug('Clearing crawl queue');
        $result = $this->wpdb->query("DELETE FROM {$this->tableName}");
        if ($result === \false) {
            throw new \RuntimeException(\sprintf('Unable to clear crawl queue: %s', $this->wpdb->last_error));
        }
    }

    /**
     * @param CrawlUrl $crawlUrl
     * @param int $priority
     * @return void
     */
    public function enqueue($crawlUrl, $priority)
    {
        $this->logger->debug(\sprintf('Enqueueing crawl url "%s" (priority %d)', $crawlUrl->url(), $priority), [
            'crawlUrlId' => $crawlUrl->id()
        ]);
        $result = $this->wpdb->insert($this->tableName, \array_merge($this->getCrawlUrlValues($crawlUrl), [
            'priority' => $priority
        ]));
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to enqueue crawl url "%s": %s',
                $crawlUrl->url(),
                $this->wpdb->last_error
            ));
        }
    }

    private function getCrawlUrlValues(CrawlUrl $crawlUrl) : array
    {
        return [
            'uuid' => $crawlUrl->id(),
            'url' => (string) $crawlUrl->url(),
            'origin_url' => (string) $crawlUrl->originUrl(),
            'transformed_url' => (string) $crawlUrl->transformedUrl(),
            'found_on_url' => $crawlUrl->foundOnUrl() ? (string) $crawlUrl->foundOnUrl() : null,
            'depth_level' => $crawlUrl->depthLevel(),
            'redirect_level' => $crawlUrl->redirectLevel(),
            'tags' => \implode(',', $crawlUrl->tags())
        ];
    }

    public function dequeue() : crawlUrl
    {
        $row = $this->wpdb->get_row("SELECT * FROM {$this->tableName} ORDER BY priority DESC, id ASC LIMIT 1", ARRAY_A);
        if ($row === null) {
            throw new \RuntimeException('Unable to dequeue; queue was empty');
        }
        $crawlUrl = $this->rowToCrawlUrl($row);
        $result = $this->wpdb->delete($this->tableName, [
            'uuid' => $crawlUrl->id()
        ]);
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to dequeue crawl url "%s": %s',
                $crawlUrl->url(),
                $this->wpdb->last_error
            ));
        }
        $this->logger->debug(\sprintf('Dequeued crawl url "%s"', $crawlUrl->url()), [
            'crawlUrlId' => $crawlUrl->id()
        ]);
        return $crawlUrl;
    }

    private function rowToCrawlUrl(array $row) : CrawlUrl
    {
        return new CrawlUrl((string) UuidV4::fromBytes($row['uuid']), new Uri($row['url']), new Uri(
            $row['origin_url']
        ), new Uri(
            $row['transformed_url']
        ), $row['found_on_url'] ? new Uri(
            $row['found_on_url']
        ) : null, (int) $row['depth_level'], (int) $row['redirect_level'], $row['tags'] ? \explode(
            ',',
            $row['tags']
        ) : []);
    }

    public function count()
    {
        return (int) $this->wpdb->get_var("\n            SELECT COUNT(*)\n            FROM {$this->tableName}");
    }
}
