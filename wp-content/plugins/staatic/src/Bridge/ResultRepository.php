<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\UuidV4;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Framework\Result;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;

final class ResultRepository implements ResultRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    //!TODO: Specified key was too long; max key length is 767 bytes
    // UNIQUE KEY build_uuid_url (build_uuid, url)
    // Use build_uuid, resource_id instead? Since resource_id is based on the url?

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
        string $tableName = 'staatic_results',
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
     * @param Result $result
     * @return void
     */
    public function add($result)
    {
        $this->logger->debug(\sprintf('Adding result #%s', $result->id()), [
            'resultId' => $result->id()
        ]);
        $queryResult = $this->wpdb->insert($this->tableName, $this->getResultValues($result));
        if ($queryResult === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to add result "%s" (#%s): %s',
                $result->url(),
                $result->id(),
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @param Result $result
     * @return void
     */
    public function update($result)
    {
        $this->logger->debug(\sprintf('Updating result #%s', $result->id()), [
            'resultId' => $result->id()
        ]);
        $queryResult = $this->wpdb->update($this->tableName, $this->getResultValues($result), [
            'uuid' => $result->id()
        ]);
        if ($queryResult === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to update result #%s: %s',
                $result->id(),
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @param Result $result
     * @return void
     */
    public function delete($result)
    {
        $this->logger->debug(\sprintf('Deleting result #%s', $result->id()), [
            'resultId' => $result->id()
        ]);
        $queryResult = $this->wpdb->delete($this->tableName, [
            'uuid' => $result->id()
        ]);
        if ($queryResult === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to delete result #%s: %s',
                $result->id(),
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @param string $sourceBuildId
     * @param string $targetBuildId
     * @return void
     */
    public function mergeBuildResults($sourceBuildId, $targetBuildId)
    {
        $this->logger->debug(
            \sprintf('Merging build results from build #%s into build #%s', $sourceBuildId, $targetBuildId),
            [
            'buildId' => $targetBuildId
        ]
        );
        $statement = $this->wpdb->prepare(
            "\n            SELECT\n                s.url, s.status_code, s.resource_id, s.md5, s.sha1, s.size, s.mime_type, s.charset,\n                s.redirect_url, s.original_url, s.original_found_on_url, s.date_created\n            FROM {$this->tableName} s\n                LEFT JOIN {$this->tableName} t ON t.build_uuid = UNHEX(REPLACE(%s, '-', '')) AND t.url = s.url\n            WHERE s.build_uuid = UNHEX(REPLACE(%s, '-', ''))\n                AND t.uuid IS NULL",
            $targetBuildId,
            $sourceBuildId
        );
        $rows = $this->wpdb->get_results($statement, ARRAY_A);
        $insertValues = [];
        foreach ($rows as $row) {
            $insertValues[] = [
                'uuid' => $this->nextId(),
                'build_uuid' => $targetBuildId
            ] + $row;
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
        $rowPlaceholders = \array_map(function ($row) {
            return "(UNHEX(REPLACE(%s, '-', '')), UNHEX(REPLACE(%s, '-', '')), %s, %d, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s)";
        }, $insertValues);
        //!TODO: not really flexible, yet...
        $values = [];
        foreach ($insertValues as $row) {
            foreach ($row as $value) {
                $values[] = $value;
            }
        }
        $statement = $this->wpdb->prepare(
            "\n            INSERT INTO {$tableName} (" . \implode(
                ', ',
                $columnNames
            ) . ")\n            VALUES " . \implode(
                ', ',
                $rowPlaceholders
            ),
            $values
        );
        $queryResult = $this->wpdb->query($statement);
        if ($queryResult === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to mass insert into %s: %s',
                $tableName,
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function scheduleForDeployment($buildId, $deploymentId) : int
    {
        $this->logger->debug(\sprintf('Scheduling results in build #%s for deployment #%s', $buildId, $deploymentId), [
            'buildId' => $buildId,
            'deploymentId' => $deploymentId
        ]);
        $statement = $this->wpdb->prepare(
            "\n            INSERT INTO {$this->deployTableName} (result_uuid, deployment_uuid, date_created)\n            SELECT r.uuid, UNHEX(REPLACE(%s, '-', '')), %s\n            FROM {$this->tableName} r\n            WHERE r.build_uuid = UNHEX(REPLACE(%s, '-', ''))",
            $deploymentId,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            $buildId
        );
        $queryResult = $this->wpdb->query($statement);
        if ($queryResult === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to schedule results in build #%s for deployment #%s: %s',
                $buildId,
                $deploymentId,
                $this->wpdb->last_error
            ));
        }
        return \is_int($queryResult) ? $queryResult : 0;
    }

    /**
     * @param Result $result
     * @param string $deploymentId
     * @return void
     */
    public function markDeployed($result, $deploymentId)
    {
        $this->logger->debug(\sprintf('Marking result #%s deployed for deployment #%s', $result->id(), $deploymentId), [
            'resultId' => $result->id(),
            'deploymentId' => $deploymentId
        ]);
        $statement = $this->wpdb->prepare(
            "\n            UPDATE {$this->deployTableName}\n            SET date_deployed = %s\n            WHERE result_uuid = UNHEX(REPLACE(%s, '-', ''))\n                AND deployment_uuid = UNHEX(REPLACE(%s, '-', ''))",
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            $result->id(),
            $deploymentId
        );
        $rowsAffected = $this->wpdb->query($statement);
        if ($rowsAffected !== 1) {
            throw new \RuntimeException(\sprintf(
                'Unable to mark result #%s deployed for deployment #%s',
                $result->id(),
                $deploymentId
            ));
        }
    }

    private function getResultValues(Result $result) : array
    {
        return [
            'uuid' => $result->id(),
            'build_uuid' => $result->buildId(),
            'url' => (string) $result->url(),
            'status_code' => $result->statusCode(),
            'resource_id' => $result->resourceId(),
            'md5' => $result->md5(),
            'sha1' => $result->sha1(),
            'size' => $result->size(),
            'mime_type' => $result->mimeType(),
            'charset' => $result->charset(),
            'redirect_url' => $result->redirectUrl() ? (string) $result->redirectUrl() : null,
            'original_url' => $result->originalUrl() ? (string) $result->originalUrl() : null,
            'original_found_on_url' => $result->originalFoundOnUrl() ? (string) $result->originalFoundOnUrl() : null,
            'date_created' => $result->dateCreated()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @param string $resultId
     * @return Result|null
     */
    public function find($resultId)
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE uuid = UNHEX(REPLACE(%s, '-', ''))",
                $resultId
            ),
            ARRAY_A
        );
        if (!\is_array($row)) {
            throw new \RuntimeException(\sprintf('Unable to find result "%s"', $resultId));
        }
        return $this->rowToResult($row);
    }

    /**
     * @return Result[]|\Generator
     */
    public function findAll() : \Generator
    {
        $rows = $this->wpdb->get_results("SELECT * FROM {$this->tableName}", ARRAY_A);
        foreach ($rows as $row) {
            (yield $this->rowToResult($row));
        }
    }

    /**
     * @return Result[]|\Generator
     * @param string $buildId
     */
    public function findByBuildId($buildId) : \Generator
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE build_uuid = UNHEX(REPLACE(%s, '-', ''))",
                $buildId
            ),
            ARRAY_A
        );
        foreach ($rows as $row) {
            (yield $this->rowToResult($row));
        }
    }

    /**
     * @return Result[]
     * @param string $buildId
     */
    public function findByBuildIdWithRedirectUrl($buildId) : array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE build_uuid = UNHEX(REPLACE(%s, '-', '')) AND redirect_url IS NOT NULL",
                $buildId
            ),
            ARRAY_A
        );
        return \array_map(function ($row) {
            return $this->rowToResult($row);
        }, $rows);
    }

    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function findByBuildIdPendingDeployment($buildId, $deploymentId) : \Generator
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "\n                SELECT r.*\n                FROM {$this->tableName} r\n                    LEFT JOIN {$this->deployTableName} d ON d.result_uuid = r.uuid AND d.deployment_uuid = UNHEX(REPLACE(%s, '-', ''))\n                WHERE r.build_uuid = UNHEX(REPLACE(%s, '-', ''))\n                    AND d.date_deployed IS NULL",
                $deploymentId,
                $buildId
            ),
            ARRAY_A
        );
        foreach ($rows as $row) {
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
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE build_uuid = UNHEX(REPLACE(%s, '-', '')) AND url = %s",
                $buildId,
                (string) $url
            ),
            ARRAY_A
        );
        return \is_array($row) ? $this->rowToResult($row) : null;
    }

    /**
     * @param string $buildId
     * @param UriInterface $url
     * @return Result|null
     */
    public function findOneByBuildIdAndUrlResolved($buildId, $url)
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} WHERE build_uuid = UNHEX(REPLACE(%s, '-', '')) AND url = %s",
                $buildId,
                (string) $url
            ),
            ARRAY_A
        );
        if (!\is_array($row)) {
            return null;
        }
        $result = $this->rowToResult($row);
        if ($result->statusCodeCategory() === 3) {
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
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->tableName} WHERE build_uuid = UNHEX(REPLACE(%s, '-', ''))", $buildId)
        );
    }

    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function countByBuildIdPendingDeployment($buildId, $deploymentId) : int
    {
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare("\n                SELECT COUNT(*)\n                FROM {$this->tableName} r\n                    LEFT JOIN {$this->deployTableName} d ON d.result_uuid = r.uuid and d.deployment_uuid = UNHEX(REPLACE(%s, '-', ''))\n                WHERE r.build_uuid = UNHEX(REPLACE(%s, '-', ''))\n                    AND d.date_deployed IS NULL", $deploymentId, $buildId)
        );
    }

    private function rowToResult(array $row) : Result
    {
        return new Result((string) UuidV4::fromBytes($row['uuid']), (string) UuidV4::fromBytes(
            $row['build_uuid']
        ), new Uri(
            $row['url']
        ), (int) $row['status_code'], $row['resource_id'], $row['md5'], $row['sha1'], (int) $row['size'], $row['mime_type'], $row['charset'], $row['redirect_url'] ? new Uri(
            $row['redirect_url']
        ) : null, $row['original_url'] ? new Uri(
            $row['original_url']
        ) : null, $row['original_found_on_url'] ? new Uri(
            $row['original_found_on_url']
        ) : null, new \DateTimeImmutable(
            $row['date_created']
        ));
    }

    // Plugin specific methods

    /**
     * @param string $buildId
     * @return void
     */
    public function deleteByBuildId($buildId)
    {
        $this->logger->debug(\sprintf('Deleting results for build #%s', $buildId), [
            'buildId' => $buildId
        ]);
        $queryResult = $this->wpdb->delete($this->tableName, [
            'build_uuid' => $buildId
        ]);
        if ($queryResult === \false) {
            throw new \RuntimeException(\sprintf(
                'Unable to delete results for build #%s: %s',
                $buildId,
                $this->wpdb->last_error
            ));
        }
    }

    /**
     * @return Result[]
     * @param string $buildId
     * @param string|null $query
     * @param int $limit
     * @param int $offset
     * @param string|null $orderBy
     * @param string|null $direction
     */
    public function findWhereMatching(
        $buildId,
        $statusCategories,
        $query,
        $limit,
        $offset,
        $orderBy,
        $direction
    ) : array
    {
        $statusCategories = \is_int($statusCategories) ? [$statusCategories] : $statusCategories;
        $orderBy = $orderBy ?: null;
        $direction = $direction ?: 'DESC';
        $results = $this->wpdb->get_results(
            "\n            SELECT *\n            FROM {$this->tableName}\n            WHERE build_uuid = UNHEX(REPLACE('" . esc_sql(
                $buildId
            ) . "', '-', ''))" . (!empty($statusCategories) ? "\n            AND SUBSTR(status_code, 1, 1) IN (" . \implode(
                ', ',
                \array_map(function ($category) {
            return \intval($category);
        }, $statusCategories)
            ) . ")" : "") . ($query ? "\n                AND url LIKE '%" . esc_sql(
            $this->wpdb->esc_like($query)
        ) . "%'" : "") . "\n            ORDER BY " . ($orderBy ? "{$orderBy} {$direction}, " : "") . "id {$direction}\n            LIMIT {$limit} OFFSET {$offset}",
            ARRAY_A
        );
        return \array_map(function ($row) {
            return $this->rowToResult($row);
        }, $results);
    }

    /**
     * @param string $buildId
     * @param int|null $category
     * @param string|null $query
     */
    public function countWhereMatching($buildId, $category, $query) : int
    {
        return (int) $this->wpdb->get_var(
            "\n            SELECT COUNT(*)\n            FROM {$this->tableName}\n            WHERE build_uuid = UNHEX(REPLACE('" . esc_sql(
                $buildId
            ) . "', '-', ''))" . ($category ? "\n                AND SUBSTR(status_code, 1, 1) = " . \intval(
                $category
            ) : "") . ($query ? "\n                AND url LIKE '%" . esc_sql(
                $this->wpdb->esc_like($query)
            ) . "%'" : "")
        );
    }

    /**
     * @param string $buildId
     */
    public function getResultsPerStatusCategory($buildId) : array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "\n                SELECT SUBSTR(status_code, 1, 1) AS category, COUNT(*) AS total\n                FROM {$this->tableName}\n                WHERE build_uuid = UNHEX(REPLACE(%s, '-', ''))\n                GROUP BY category\n                ORDER BY category",
                $buildId
            ),
            ARRAY_A
        );
        $resultsPerStatusCategory = [];
        foreach ($rows as $row) {
            $resultsPerStatusCategory[(int) $row['category']] = (int) $row['total'];
        }
        foreach (\range(1, 5) as $statusCategory) {
            $resultsPerStatusCategory[$statusCategory] = $resultsPerStatusCategory[$statusCategory] ?? 0;
        }
        return $resultsPerStatusCategory;
    }
}
