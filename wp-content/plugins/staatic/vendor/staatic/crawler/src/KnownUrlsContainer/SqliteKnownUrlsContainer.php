<?php

namespace Staatic\Crawler\KnownUrlsContainer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
final class SqliteKnownUrlsContainer implements KnownUrlsContainerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    const TABLE_DEFINITION = '
        CREATE TABLE IF NOT EXISTS %s (
            url TEXT NOT NULL,
            PRIMARY KEY (url)
        )';
    /**
     * @var \SQLite3
     */
    private $sqlite;
    /**
     * @var string
     */
    private $tableName;
    public function __construct(string $databasePath, string $tableName = 'staatic_known_urls')
    {
        $this->logger = new NullLogger();
        $this->sqlite = new \SQLite3($databasePath);
        $this->sqlite->enableExceptions(\true);
        $this->tableName = $tableName;
    }
    public function __destruct()
    {
        $this->sqlite->close();
    }
    /**
     * @return void
     */
    public function createTable()
    {
        try {
            $this->sqlite->exec(\sprintf(self::TABLE_DEFINITION, $this->tableName));
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to create known urls table: %s', $e->getMessage()));
        }
    }
    /**
     * @return void
     */
    public function clear()
    {
        $this->logger->debug('Clearing container');
        try {
            $this->sqlite->exec(\sprintf('DELETE FROM %s', $this->tableName));
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to clear container: %s', $e->getMessage()));
        }
    }
    /**
     * @param UriInterface $url
     * @return void
     */
    public function add($url)
    {
        $this->logger->debug(\sprintf('Adding url "%s" to container', $url));
        try {
            $statement = $this->sqlite->prepare(\sprintf('INSERT INTO %s (url) VALUES (:url)', $this->tableName));
            $statement->bindValue(':url', $url, \SQLITE3_TEXT);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to add url "%s" to container: %s', $url, $e->getMessage()));
        }
    }
    /**
     * @param UriInterface $url
     */
    public function isKnown($url) : bool
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT COUNT(*) FROM %s WHERE url = :url', $this->tableName));
            $statement->bindValue(':url', $url, \SQLITE3_TEXT);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to count container: %s', $e->getMessage()));
        }
        $row = $result->fetchArray(\SQLITE3_NUM);
        return (bool) $row[0];
    }
    public function count()
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT COUNT(*) FROM %s', $this->tableName));
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to count container: %s', $e->getMessage()));
        }
        $row = $result->fetchArray(\SQLITE3_NUM);
        return (int) $row[0];
    }
}
