<?php

namespace Staatic\Framework\DeploymentRepository;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Framework\Deployment;
final class SqliteDeploymentRepository implements DeploymentRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    const TABLE_DEFINITION = '
        CREATE TABLE IF NOT EXISTS %s (
            id TEXT NOT NULL,
            build_id TEXT NOT NULL,
            date_created TEXT NOT NULL,
            date_started TEXT,
            date_finished TEXT,
            num_results_total INTEGER,
            num_results_deployable INTEGER,
            num_results_deployed INTEGER,
            metadata TEXT,
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
    public function __construct(string $databasePath, string $tableName = 'staatic_deployments')
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
            throw new \RuntimeException(\sprintf('Unable to create deployment repositoy table: %s', $e->getMessage()));
        }
    }
    public function nextId() : string
    {
        return (string) Uuid::uuid4();
    }
    /**
     * @param Deployment $deployment
     * @return void
     */
    public function add($deployment)
    {
        $this->logger->debug(\sprintf('Adding deployment #%s', $deployment->id()), ['deploymentId' => $deployment->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    INSERT INTO %s (
                        id, build_id, date_created,
                        date_started, date_finished,
                        num_results_total, num_results_deployable, num_results_deployed,
                        metadata
                    ) VALUES (
                        :id, :buildId, :dateCreated,
                        :dateStarted, :dateFinished,
                        :numResultsTotal, :numResultsDeployable, :numResultsDeployed,
                        :metadata
                    )
                ', $this->tableName));
            $this->bindDeploymentValues($deployment, $statement);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to add deployment #%s: %s', $deployment->id(), $e->getMessage()));
        }
    }
    /**
     * @param Deployment $deployment
     * @return void
     */
    public function update($deployment)
    {
        $this->logger->debug(\sprintf('Updating deployment #%s', $deployment->id()), ['deploymentId' => $deployment->id()]);
        try {
            $statement = $this->sqlite->prepare(\sprintf('
                    UPDATE %s
                    SET build_id = :buildId,
                        date_created = :dateCreated,
                        date_started = :dateStarted,
                        date_finished = :dateFinished,
                        num_results_total = :numResultsTotal,
                        num_results_deployable = :numResultsDeployable,
                        num_results_deployed = :numResultsDeployed,
                        metadata = :metadata
                    WHERE id = :id
                ', $this->tableName));
            $this->bindDeploymentValues($deployment, $statement);
            $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to update deployment #%s: %s', $deployment->id(), $e->getMessage()));
        }
    }
    /**
     * @param string $deploymentId
     * @return Deployment|null
     */
    public function find($deploymentId)
    {
        try {
            $statement = $this->sqlite->prepare(\sprintf('SELECT * FROM %s WHERE id = :id', $this->tableName));
            $statement->bindValue(':id', $deploymentId, \SQLITE3_TEXT);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to find deployment #%s: %s', $deploymentId, $e->getMessage()));
        }
        $row = $result->fetchArray(\SQLITE3_ASSOC);
        return \is_array($row) ? $this->rowToDeployment($row) : null;
    }
    /**
     * @return void
     */
    private function bindDeploymentValues(Deployment $deployment, \SQLite3Stmt $statement)
    {
        $statement->bindValue(':id', $deployment->id(), \SQLITE3_TEXT);
        $statement->bindValue(':buildId', $deployment->buildId(), \SQLITE3_TEXT);
        $statement->bindValue(':dateCreated', $deployment->dateCreated()->format('c'), \SQLITE3_TEXT);
        $statement->bindValue(':dateStarted', $deployment->dateStarted() ? $deployment->dateStarted()->format('c') : null, \SQLITE3_TEXT);
        $statement->bindValue(':dateFinished', $deployment->dateFinished() ? $deployment->dateFinished()->format('c') : null, \SQLITE3_TEXT);
        $statement->bindValue(':numResultsTotal', $deployment->numResultsTotal(), \SQLITE3_NUM);
        $statement->bindValue(':numResultsDeployable', $deployment->numResultsDeployable(), \SQLITE3_NUM);
        $statement->bindValue(':numResultsDeployed', $deployment->numResultsDeployed(), \SQLITE3_NUM);
        $statement->bindValue(':metadata', $deployment->metadata() ? \json_encode($deployment->metadata()) : null, \SQLITE3_TEXT);
    }
    private function rowToDeployment(array $row) : Deployment
    {
        return new Deployment($row['id'], $row['build_id'], new \DateTimeImmutable($row['date_created']), $row['date_started'] ? new \DateTimeImmutable($row['date_started']) : null, $row['date_finished'] ? new \DateTimeImmutable($row['date_finished']) : null, (int) $row['num_results_total'], (int) $row['num_results_deployable'], (int) $row['num_results_deployed'], $row['metadata'] ? \json_decode($row['metadata'], \true) : null);
    }
}
