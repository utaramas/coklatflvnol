<?php

namespace Staatic\Framework\ResultRepository;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Framework\Result;
final class SqliteResultRepository implements ResultRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    const TABLE_DEFINITION = '
        CREATE TABLE IF NOT EXISTS %s (
            id TEXT NOT NULL,
            build_id INTEGER NOT NULL,
            url TEXT NOT NULL,
            status_code INTEGER NOT NULL,
            resource_id INTEGER NOT NULL,
            md5 TEXT,
            sha1 TEXT,
            size INTEGER,
            mime_type TEXT,
            charset TEXT,
            redirect_url TEXT,
            original_url TEXT,
            original_found_on_url TEXT,
            date_created TEXT NOT NULL,
            PRIMARY KEY (id)
        )';
    const DEPLOY_TABLE_DEFINITION = '
        CREATE TABLE IF NOT EXISTS %s (
            result_id TEXT NOT NULL,
            deployment_id INTEGER NOT NULL,
            date_created TEXT NOT NULL,
            date_deployed TEXT,
            PRIMARY KEY (result_id, deployment_id)
        )';
    /**
     * @var \SQLite3
     */
    private $sqlite;
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var string
     */
    private $deployTableName;
    public function __construct(string $databasePath, string $tableName = 'staatic_results', string $deployTableName = 'staatic_results_deployment')
    {
        $this->logger = new NullLogger();
        $this->sqlite = new \SQLite3($databasePath);
        $this->sqlite->enableExceptions(\true);
        $this->tableName = $tableName;
        $this->deployTableName = $deployTableName;
    }
    public function __destruct()
    {
        $this->sqlite->close();
    }
    public function createTables()
    {
        try {
            $this->sqlite->exec(\sprintf(self::TABLE_DEFINITION, $this->tableName));
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to create result repository table: %s', $e->getMessage()));
        }
        try {
            $this->sqlite->exec(\sprintf(self::DEPLOY_TABLE_DEFINITION, $this->deployTableName));
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to create result deploy table: %s', $e->getMessage()));
        }
    }
    public function nextId() : string
    {
        return (string) Uuid::uuid4();
    }
    /**
     * @param Result $result
     * @return void
     */
    public function add($result)
    {
        $this->logger->debug(\sprintf('Adding result #%s', $result->id()), ['resultId' => $result->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    INSERT INTO %s (
                        id, build_id, url, status_code, resource_id, md5, sha1, size, mime_type, charset,
                        redirect_url, original_url, original_found_on_url, date_created
                    ) VALUES (
                        :id, :buildId, :url, :statusCode, :resourceId, :md5, :sha1, :size, :mimeType, :charset,
                        :redirectUrl, :originalUrl, :originalFoundOnUrl, :dateCreated
                    )
                ', $this->tableName));
            $this->bindResultValues($result, $statement);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to add result "%s" (#%s): %s', $result->url(), $result->id(), $e->getMessage()));
        }
    }
    /**
     * @param Result $result
     * @return void
     */
    public function update($result)
    {
        $this->logger->debug(\sprintf('Updating result #%s', $result->id()), ['resultId' => $result->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    UPDATE %s
                    SET build_id = :buildId,
                        url = :url,
                        status_code = :statusCode,
                        resource_id = :resourceId,
                        md5 = :md5,
                        sha1 = :sha1,
                        size = :size,
                        mime_type = :mimeType,
                        charset = :charset,
                        redirect_url = :redirectUrl,
                        original_url = :originalUrl,
                        original_found_on_url = :originalFoundOnUrl,
                        date_created = :dateCreated
                    WHERE id = :id
                ', $this->tableName));
            $this->bindResultValues($result, $statement);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to update result #%s: %s', $result->id(), $e->getMessage()));
        }
    }
    /**
     * @param Result $result
     * @return void
     */
    public function delete($result)
    {
        $this->logger->debug(\sprintf('Deleting result #%s', $result->id()), ['resultId' => $result->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('DELETE FROM %s WHERE id = :id', $this->tableName));
            $statement->bindValue(':id', $result->id(), \SQLITE3_TEXT);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to delete result #%s: %s', $result->id(), $e->getMessage()));
        }
    }
    /**
     * @param string $sourceBuildId
     * @param string $targetBuildId
     * @return void
     */
    public function mergeBuildResults($sourceBuildId, $targetBuildId)
    {
        $this->logger->debug(\sprintf('Merging build results from build #%s into build #%s', $sourceBuildId, $targetBuildId), ['buildId' => $targetBuildId]);
        try {
            $this->doMergeBuildResults($sourceBuildId, $targetBuildId);
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to merge build results from build #%s into build #%s: %s', $sourceBuildId, $targetBuildId, $e->getMessage()));
        }
    }
    /**
     * @return void
     */
    private function doMergeBuildResults(string $sourceBuildId, string $targetBuildId)
    {
        $statement = $this->sqlite->prepare(\sprintf('
            SELECT
                s.url, s.status_code, s.resource_id, s.md5, s.sha1, s.size, s.mime_type, s.charset,
                s.redirect_url, s.original_url, s.original_found_on_url, s.date_created
            FROM %1$s s
                LEFT JOIN %1$s t ON t.build_id = :targetBuildId AND t.url = s.url
            WHERE s.build_id = :sourceBuildId
                AND t.id IS NULL', $this->tableName));
        $statement->bindValue(':sourceBuildId', $sourceBuildId, \SQLITE3_TEXT);
        $statement->bindValue(':targetBuildId', $targetBuildId, \SQLITE3_TEXT);
        $result = $statement->execute();
        $insertValues = [];
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            $insertValues[] = ['id' => $this->nextId(), 'build_id' => $targetBuildId] + $row;
            if (\count($insertValues) >= 50) {
                $this->massInsert($this->tableName, $insertValues);
                $insertValues = [];
            }
        }
        if (\count($insertValues)) {
            $this->massInsert($this->tableName, $insertValues);
        }
    }
    /**
     * @return void
     */
    private function massInsert(string $tableName, array $insertValues)
    {
        $columnNames = \array_keys($insertValues[0]);
        $columnCount = \count($insertValues[0]);
        $rowPlaceholders = \array_map(function ($row) use($columnCount) {
            return '(' . \implode(', ', \array_fill(0, $columnCount, '?')) . ')';
        }, $insertValues);
        $statement = $this->sqlite->prepare(\sprintf('
            INSERT INTO %s (' . \implode(', ', $columnNames) . ')
            VALUES ' . \implode(', ', $rowPlaceholders), $tableName));
        $i = 1;
        foreach ($insertValues as $row) {
            foreach ($row as $value) {
                $statement->bindValue($i++, $value, \is_string($value) ? \SQLITE3_TEXT : \SQLITE3_INTEGER);
            }
        }
        $statement->execute();
    }
    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function scheduleForDeployment($buildId, $deploymentId) : int
    {
        $this->logger->debug(\sprintf('Scheduling results in build #%s for deployment #%s', $buildId, $deploymentId), ['buildId' => $buildId, 'deploymentId' => $deploymentId]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                INSERT INTO %2$s (result_id, deployment_id, date_created)
                SELECT r.id, :deploymentId, :dateCreated
                FROM %1$s r
                WHERE r.build_id = :buildId', $this->tableName, $this->deployTableName));
            $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
            $statement->bindValue(':deploymentId', $deploymentId, \SQLITE3_TEXT);
            $statement->bindValue(':dateCreated', (new \DateTimeImmutable())->format('c'), \SQLITE3_TEXT);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to schedule results in build #%s for deployment #%s: %s', $buildId, $deploymentId, $e->getMessage()));
        }
        return $this->sqlite->changes();
    }
    /**
     * @param Result $result
     * @param string $deploymentId
     * @param bool $force
     */
    public function markDeployable($result, $deploymentId, $force = \false) : bool
    {
        $this->logger->debug(\sprintf('Marking result #%s deployable for #%s', $result->id(), $deploymentId), ['resultId' => $result->id(), 'deploymentId' => $deploymentId]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                INSERT INTO %s (result_id, deployment_id, date_created)
                VALUES (:resultId, :deploymentId, :dateCreated)
                ON CONFLICT(result_id, deployment_id) DO ' . ($force ? 'UPDATE SET
                    date_deployed = NULL' : 'NOTHING'), $this->deployTableName));
            $statement->bindValue(':resultId', $result->id(), \SQLITE3_TEXT);
            $statement->bindValue(':deploymentId', $deploymentId, \SQLITE3_TEXT);
            $statement->bindValue(':dateCreated', (new \DateTimeImmutable())->format('c'), \SQLITE3_TEXT);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to mark result #%s deployable for #%s: %s', $result->id(), $deploymentId, $e->getMessage()));
        }
        return $this->sqlite->changes() === 1;
    }
    /**
     * @param Result $result
     * @param string $deploymentId
     * @return void
     */
    public function markDeployed($result, $deploymentId)
    {
        $this->logger->debug(\sprintf('Marking result #%s deployed for deployment #%s', $result->id(), $deploymentId), ['resultId' => $result->id(), 'deploymentId' => $deploymentId]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                UPDATE %s
                SET date_deployed = :dateDeployed
                WHERE result_id = :resultId
                    AND deployment_id = :deploymentId', $this->deployTableName));
            $statement->bindValue(':resultId', $result->id(), \SQLITE3_TEXT);
            $statement->bindValue(':deploymentId', $deploymentId, \SQLITE3_TEXT);
            $statement->bindValue(':dateDeployed', (new \DateTimeImmutable())->format('c'), \SQLITE3_TEXT);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to mark result #%s deployed for deployment #%s: %s', $result->id(), $deploymentId, $e->getMessage()));
        }
    }
    /**
     * @return void
     */
    private function bindResultValues(Result $result, \SQLite3Stmt $statement)
    {
        $statement->bindValue(':id', $result->id(), \SQLITE3_TEXT);
        $statement->bindValue(':buildId', $result->buildId(), \SQLITE3_TEXT);
        $statement->bindValue(':url', $result->url(), \SQLITE3_TEXT);
        $statement->bindValue(':statusCode', $result->statusCode(), \SQLITE3_NUM);
        $statement->bindValue(':resourceId', $result->resourceId(), \SQLITE3_TEXT);
        $statement->bindValue(':md5', $result->md5(), \SQLITE3_TEXT);
        $statement->bindValue(':sha1', $result->sha1(), \SQLITE3_TEXT);
        $statement->bindValue(':size', $result->size(), \SQLITE3_NUM);
        $statement->bindValue(':mimeType', $result->mimeType(), \SQLITE3_TEXT);
        $statement->bindValue(':charset', $result->charset(), \SQLITE3_TEXT);
        $statement->bindValue(':redirectUrl', $result->redirectUrl(), \SQLITE3_TEXT);
        $statement->bindValue(':originalUrl', $result->originalUrl(), \SQLITE3_TEXT);
        $statement->bindValue(':originalFoundOnUrl', $result->originalFoundOnUrl(), \SQLITE3_TEXT);
        $statement->bindValue(':dateCreated', $result->dateCreated()->format('c'), \SQLITE3_TEXT);
    }
    /**
     * @param string $resultId
     * @return Result|null
     */
    public function find($resultId)
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s WHERE id = :id', $this->tableName));
            $statement->bindValue(':id', $resultId, \SQLITE3_TEXT);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to find result #%s: %s', $resultId, $e->getMessage()));
        }
        return $this->fetchOneOrNull($result);
    }
    public function findAll() : \Generator
    {
        $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s', $this->tableName));
        $result = $statement->execute();
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            (yield $this->rowToResult($row));
        }
    }
    /**
     * @param string $buildId
     */
    public function findByBuildId($buildId) : \Generator
    {
        $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s WHERE build_id = :buildId', $this->tableName));
        $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
        $result = $statement->execute();
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            (yield $this->rowToResult($row));
        }
    }
    /**
     * @param string $buildId
     */
    public function findByBuildIdWithRedirectUrl($buildId) : array
    {
        $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s WHERE build_id = :buildId AND redirect_url IS NOT NULL', $this->tableName));
        $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
        $result = $statement->execute();
        return $this->fetch($result);
    }
    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function findByBuildIdPendingDeployment($buildId, $deploymentId) : \Generator
    {
        $statement = $this->sqlite->prepare(\sprintf('
                SELECT r.*
                FROM %1$s r
                    LEFT JOIN %2$s d ON d.result_id = r.id AND d.deployment_id = :deploymentId
                WHERE r.build_id = :buildId
                    AND d.date_deployed IS NULL', $this->tableName, $this->deployTableName));
        $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
        $statement->bindValue(':deploymentId', $deploymentId, \SQLITE3_TEXT);
        $result = $statement->execute();
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            (yield $this->rowToResult($row));
        }
    }
    /**
     * @param string $buildId
     * @param UriInterface $url
     * @return Result|null
     */
    public function findOneByBuildIdAndUrl($buildId, $url)
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s WHERE build_id = :buildId AND url = :url', $this->tableName));
            $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
            $statement->bindValue(':url', (string) $url, \SQLITE3_TEXT);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to find by build #%s and url %s: %s', $buildId, $url, $e->getMessage()));
        }
        return $this->fetchOneOrNull($result);
    }
    /**
     * @param string $buildId
     * @param UriInterface $url
     * @return Result|null
     */
    public function findOneByBuildIdAndUrlResolved($buildId, $url)
    {
        $result = $this->findOneByBuildIdAndUrl($buildId, $url);
        if (!$result) {
            return null;
        } elseif ($result->statusCodeCategory() === 3) {
            return $this->findOneByBuildIdAndUrlResolved($buildId, $result->redirectUrl());
        } else {
            return $result;
        }
    }
    /**
     * @param string $buildId
     */
    public function countByBuildId($buildId) : int
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT COUNT(*) FROM %s WHERE build_id = :buildId', $this->tableName));
            $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
            $result = $statement->execute();
            $row = $result->fetchArray(\SQLITE3_NUM);
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to count by build #%s: %s', $buildId, $e->getMessage()));
        }
        return (int) $row[0];
    }
    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function countByBuildIdPendingDeployment($buildId, $deploymentId) : int
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    SELECT COUNT(*)
                    FROM %1$s r
                        LEFT JOIN %2$s d ON d.result_id = r.id and d.deployment_id = :deploymentId
                    WHERE r.build_id = :buildId
                        AND d.date_deployed IS NULL', $this->tableName, $this->deployTableName));
            $statement->bindValue(':buildId', $buildId, \SQLITE3_TEXT);
            $statement->bindValue(':deploymentId', $deploymentId, \SQLITE3_TEXT);
            $result = $statement->execute();
            $row = $result->fetchArray(\SQLITE3_NUM);
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to count pending deployment by build #%s: %s', $buildId, $e->getMessage()));
        }
        return (int) $row[0];
    }
    private function fetch(\SQLite3Result $result) : array
    {
        $results = [];
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            $results[] = $this->rowToResult($row);
        }
        return $results;
    }
    /**
     * @return Result|null
     */
    private function fetchOneOrNull(\SQLite3Result $result)
    {
        $row = $result->fetchArray(\SQLITE3_ASSOC);
        return \is_array($row) ? $this->rowToResult($row) : null;
    }
    private function rowToResult(array $row) : Result
    {
        return new Result($row['id'], $row['build_id'], new Uri($row['url']), $row['status_code'], $row['resource_id'], $row['md5'], $row['sha1'], $row['size'], $row['mime_type'], $row['charset'], $row['redirect_url'] ? new Uri($row['redirect_url']) : null, $row['original_url'] ? new Uri($row['original_url']) : null, $row['original_found_on_url'] ? new Uri($row['original_found_on_url']) : null, new \DateTimeImmutable($row['date_created']));
    }
}
