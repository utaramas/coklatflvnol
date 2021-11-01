<?php

namespace Staatic\Framework\BuildRepository;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Framework\Build;
final class SqliteBuildRepository implements BuildRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    const TABLE_DEFINITION = '
        CREATE TABLE IF NOT EXISTS %s (
            id TEXT NOT NULL,
            entry_url TEXT NOT NULL,
            destination_url TEXT NOT NULL,
            parent_id TEXT,
            date_created TEXT NOT NULL,
            date_crawl_started TEXT,
            date_crawl_finished TEXT,
            num_urls_crawlable INTEGER,
            num_urls_crawled INTEGER,
            PRIMARY KEY (id)
        )';
    /**
     * @var \SQLite3
     */
    private $sqlite;
    /**
     * @var string
     */
    private $tableName;
    public function __construct(string $databasePath, string $tableName = 'staatic_builds')
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
    public function createTable()
    {
        try {
            $this->sqlite->exec(\sprintf(self::TABLE_DEFINITION, $this->tableName));
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to create build repository table: %s', $e->getMessage()));
        }
    }
    public function nextId() : string
    {
        return (string) Uuid::uuid4();
    }
    /**
     * @param Build $build
     * @return void
     */
    public function add($build)
    {
        $this->logger->debug(\sprintf('Adding build #%s', $build->id()), ['buildId' => $build->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    INSERT INTO %s (
                        id, entry_url, destination_url, parent_id, date_created,
                        date_crawl_started, date_crawl_finished, num_urls_crawlable, num_urls_crawled
                    ) VALUES (
                        :id, :entryUrl, :destinationUrl, :parentId, :dateCreated,
                        :dateCrawlStarted, :dateCrawlFinished, :numUrlsCrawlable, :numUrlsCrawled
                    )
                ', $this->tableName));
            $this->bindBuildValues($build, $statement);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to add build #%s: %s', $build->id(), $e->getMessage()));
        }
    }
    /**
     * @param Build $build
     * @return void
     */
    public function update($build)
    {
        $this->logger->debug(\sprintf('Updating build #%s', $build->id()), ['buildId' => $build->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    UPDATE %s
                    SET entry_url = :entryUrl,
                        destination_url = :destinationUrl,
                        parent_id = :parentId,
                        date_created = :dateCreated,
                        date_crawl_started = :dateCrawlStarted,
                        date_crawl_finished = :dateCrawlFinished,
                        num_urls_crawlable = :numUrlsCrawlable,
                        num_urls_crawled = :numUrlsCrawled
                    WHERE id = :id
                ', $this->tableName));
            $this->bindBuildValues($build, $statement);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to update build #%s: %s', $build->id(), $e->getMessage()));
        }
    }
    /**
     * @param string $buildId
     * @return Build|null
     */
    public function find($buildId)
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s WHERE id = :id', $this->tableName));
            $statement->bindValue(':id', $buildId, \SQLITE3_TEXT);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to find build #%s: %s', $buildId, $e->getMessage()));
        }
        $row = $result->fetchArray(\SQLITE3_ASSOC);
        return \is_array($row) ? $this->rowToBuild($row) : null;
    }
    /**
     * @return void
     */
    private function bindBuildValues(Build $build, \SQLite3Stmt $statement)
    {
        $statement->bindValue(':id', $build->id(), \SQLITE3_TEXT);
        $statement->bindValue(':entryUrl', $build->entryUrl(), \SQLITE3_TEXT);
        $statement->bindValue(':destinationUrl', $build->destinationUrl(), \SQLITE3_TEXT);
        $statement->bindValue(':parentId', $build->parentId(), \SQLITE3_TEXT);
        $statement->bindValue(':dateCreated', $build->dateCreated()->format('c'), \SQLITE3_TEXT);
        $statement->bindValue(':dateCrawlStarted', $build->dateCrawlStarted() ? $build->dateCrawlStarted()->format('c') : null, \SQLITE3_TEXT);
        $statement->bindValue(':dateCrawlFinished', $build->dateCrawlFinished() ? $build->dateCrawlFinished()->format('c') : null, \SQLITE3_TEXT);
        $statement->bindValue(':numUrlsCrawlable', $build->numUrlsCrawlable(), \SQLITE3_NUM);
        $statement->bindValue(':numUrlsCrawled', $build->numUrlsCrawled(), \SQLITE3_NUM);
    }
    private function rowToBuild(array $row) : Build
    {
        return new Build($row['id'], new Uri($row['entry_url']), new Uri($row['destination_url']), $row['parent_id'] ? $row['parent_id'] : null, new \DateTimeImmutable($row['date_created']), $row['date_crawl_started'] ? new \DateTimeImmutable($row['date_started']) : null, $row['date_crawl_finished'] ? new \DateTimeImmutable($row['date_finished']) : null, (int) $row['num_urls_crawlable'], (int) $row['num_urls_crawled']);
    }
}
