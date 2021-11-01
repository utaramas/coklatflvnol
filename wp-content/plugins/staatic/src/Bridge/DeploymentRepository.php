<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\UuidV4;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Framework\Deployment;
use Staatic\Framework\DeploymentRepository\DeploymentRepositoryInterface;

final class DeploymentRepository implements DeploymentRepositoryInterface, LoggerAwareInterface
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

    /**
     * @var string
     */
    private $deployTableName;

    public function __construct(
        \wpdb $wpdb,
        string $tableName = 'staatic_deployments',
        string $deployTableName = 'staatic_results_deployment'
    )
    {
        $this->logger = new NullLogger();
        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix . $tableName;
        $this->deployTableName = $wpdb->prefix . $deployTableName;
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
        $this->logger->debug(\sprintf('Adding deployment #%s', $deployment->id()), [
            'deploymentId' => $deployment->id()
        ]);
        $result = $this->wpdb->insert($this->tableName, $this->getDeploymentValues($deployment));
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to add deployment #%s: %s',
                $deployment->id(),
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @param Deployment $deployment
     * @return void
     */
    public function update($deployment)
    {
        $this->logger->debug(\sprintf('Updating deployment #%s', $deployment->id()), [
            'deploymentId' => $deployment->id()
        ]);
        $result = $this->wpdb->update($this->tableName, $this->getDeploymentValues($deployment), [
            'uuid' => $deployment->id()
        ]);
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to update deployment #%s: %s',
                $deployment->id(),
                $this->wpdb->last_error
            ));
        }
    }

    private function getDeploymentValues(Deployment $deployment) : array
    {
        return [
            'uuid' => $deployment->id(),
            'build_uuid' => $deployment->buildId(),
            'date_created' => $deployment->dateCreated()->format('Y-m-d H:i:s'),
            'date_started' => $deployment->dateStarted() ? $deployment->dateStarted()->format('Y-m-d H:i:s') : null,
            'date_finished' => $deployment->dateFinished() ? $deployment->dateFinished()->format('Y-m-d H:i:s') : null,
            'num_results_total' => $deployment->numResultsTotal(),
            'num_results_deployable' => $deployment->numResultsDeployable(),
            'num_results_deployed' => $deployment->numResultsDeployed(),
            'metadata' => $deployment->metadata() ? \json_encode($deployment->metadata()) : null
        ];
    }

    /**
     * @param string $deploymentId
     * @return Deployment|null
     */
    public function find($deploymentId)
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE uuid = UNHEX(REPLACE(%s, '-', ''))",
                $deploymentId
            ),
            ARRAY_A
        );
        return \is_array($row) ? $this->rowToDeployment($row) : null;
    }

    private function rowToDeployment(array $row) : Deployment
    {
        return new Deployment((string) UuidV4::fromBytes($row['uuid']), (string) UuidV4::fromBytes(
            $row['build_uuid']
        ), new \DateTimeImmutable(
            $row['date_created']
        ), $row['date_started'] ? new \DateTimeImmutable(
            $row['date_started']
        ) : null, $row['date_finished'] ? new \DateTimeImmutable(
            $row['date_finished']
        ) : null, (int) $row['num_results_total'], (int) $row['num_results_deployable'], (int) $row['num_results_deployed'], $row['metadata'] ? \json_decode(
            $row['metadata'],
            \true
        ) : null);
    }

    // Plugin specific methods

    /**
     * @param Deployment $deployment
     * @return void
     */
    public function delete($deployment)
    {
        $this->logger->debug(\sprintf('Deleting deployment #%s', $deployment->id()), [
            'deploymentId' => $deployment->id()
        ]);
        $result = $this->wpdb->delete($this->deployTableName, [
            'deployment_uuid' => $deployment->id()
        ]);
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to delete deployment results #%s: %s',
                $deployment->id(),
                $this->wpdb->last_error
            ));
        }
        $result = $this->wpdb->delete($this->tableName, [
            'uuid' => $deployment->id()
        ]);
        if ($result === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to delete deployment #%s: %s',
                $deployment->id(),
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @return Deployment[]
     * @param mixed[] $deploymentIds
     */
    public function findByIds($deploymentIds) : array
    {
        $deploymentIds = \array_map(function ($deploymentId) {
            return \sprintf("UNHEX(REPLACE('%s', '-', ''))", esc_sql($deploymentId));
        }, $deploymentIds);
        $results = $this->wpdb->get_results(
            \sprintf("SELECT * FROM {$this->tableName} WHERE uuid IN (%s)", \implode(', ', $deploymentIds)),
            ARRAY_A
        );
        return \array_map(function ($row) {
            return $this->rowToDeployment($row);
        }, $results);
    }
}
