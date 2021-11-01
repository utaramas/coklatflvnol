<?php

namespace Staatic\Framework\ResultRepository;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Staatic\Framework\Result;
final class InMemoryResultRepository implements ResultRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var mixed[]
     */
    private $results = [];
    /**
     * @var mixed[]
     */
    private $deployableResults = [];
    public function __construct()
    {
        $this->logger = new NullLogger();
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
        $this->results[$result->id()] = $result;
    }
    /**
     * @param Result $result
     * @return void
     */
    public function update($result)
    {
        $this->logger->debug(\sprintf('Updating result #%s', $result->id()), ['resultId' => $result->id()]);
        $this->results[$result->id()] = $result;
    }
    /**
     * @param Result $result
     * @return void
     */
    public function delete($result)
    {
        $this->logger->debug(\sprintf('Deleting result #%s', $result->id()), ['resultId' => $result->id()]);
        unset($this->results[$result->id()]);
    }
    /**
     * @param string $sourceBuildId
     * @param string $targetBuildId
     * @return void
     */
    public function mergeBuildResults($sourceBuildId, $targetBuildId)
    {
        $this->logger->debug(\sprintf('Merging build results from build #%s into build #%s', $sourceBuildId, $targetBuildId), ['buildId' => $targetBuildId]);
        foreach ($this->results as $sourceResult) {
            if ($sourceResult->buildId() !== $sourceBuildId) {
                continue;
            }
            $targetResult = $this->findOneOrNull(function ($result) use($targetBuildId, $sourceResult) {
                return $result->buildId() === $targetBuildId && (string) $result->url() === (string) $sourceResult->url();
            });
            if ($targetResult) {
                continue;
            }
            $targetResult = Result::createFromResult($sourceResult, $this->nextId(), $targetBuildId);
            $this->results[$targetResult->id()] = $targetResult;
        }
    }
    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function scheduleForDeployment($buildId, $deploymentId) : int
    {
        $this->logger->debug(\sprintf('Scheduling results in build #%s for deployment #%s', $buildId, $deploymentId), ['buildId' => $buildId, 'deploymentId' => $deploymentId]);
        $numResults = 0;
        foreach ($this->results as $result) {
            if ($result->buildId() !== $buildId) {
                continue;
            }
            $this->deployableResults[$result->id()][$deploymentId] = ['dateCreated' => new \DateTimeImmutable(), 'dateDeployed' => null];
            $numResults++;
        }
        return $numResults;
    }
    /**
     * @param Result $result
     * @param string $deploymentId
     * @return void
     */
    public function markDeployed($result, $deploymentId)
    {
        $this->logger->debug(\sprintf('Marking result #%s deployed for deployment #%s', $result->id(), $deploymentId), ['resultId' => $result->id(), 'deploymentId' => $deploymentId]);
        if (!isset($this->deployableResults[$result->id()][$deploymentId])) {
            throw new \RuntimeException(\sprintf('Unable to mark result #%s deployed for deployment #%s: unknown result/deployment combination', $result->id(), $deploymentId));
        }
        $this->deployableResults[$result->id()][$deploymentId]['dateDeployed'] = new \DateTimeImmutable();
    }
    /**
     * @param string $resultId
     * @return Result|null
     */
    public function find($resultId)
    {
        return $this->results[$resultId] ?? null;
    }
    public function findAll() : \Generator
    {
        foreach ($this->results as $result) {
            (yield $result);
        }
    }
    /**
     * @param string $buildId
     */
    public function findByBuildId($buildId) : \Generator
    {
        foreach ($this->results as $result) {
            if ($result->buildId() === $buildId) {
                (yield $result);
            }
        }
    }
    /**
     * @param string $buildId
     */
    public function findByBuildIdWithRedirectUrl($buildId) : array
    {
        return \array_filter($this->results, function ($result) use($buildId) {
            return $result->buildId() === $buildId && $result->redirectUrl() !== null;
        });
    }
    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function findByBuildIdPendingDeployment($buildId, $deploymentId) : \Generator
    {
        foreach ($this->deployableResults as $resultId => $deployments) {
            $result = $this->results[$resultId];
            if ($result->buildId() !== $buildId) {
                continue;
            }
            $deployment = $deployments[$deploymentId] ?? null;
            if ($deployment === null || $deployment['dateDeployed'] !== null) {
                continue;
            }
            (yield $result);
        }
    }
    /**
     * @param string $buildId
     * @param UriInterface $url
     * @return Result|null
     */
    public function findOneByBuildIdAndUrl($buildId, $url)
    {
        return $this->findOneOrNull(function ($result) use($buildId, $url) {
            return $result->buildId() === $buildId && (string) $result->url() === (string) $url;
        });
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
        $generator = $this->findByBuildId($buildId);
        $results = \iterator_to_array($generator);
        return \count($results);
    }
    /**
     * @param string $buildId
     * @param string $deploymentId
     */
    public function countByBuildIdPendingDeployment($buildId, $deploymentId) : int
    {
        $count = 0;
        foreach ($this->deployableResults as $resultId => $deployments) {
            $result = $this->results[$resultId];
            if ($result->buildId() !== $buildId) {
                continue;
            }
            $deployment = $deployments[$deploymentId] ?? null;
            if ($deployment === null || $deployment['dateDeployed'] !== null) {
                continue;
            }
            $count++;
        }
        return $count;
    }
    /**
     * @return Result|null
     */
    private function findOneOrNull(callable $callback)
    {
        foreach ($this->results as $result) {
            if ($callback($result)) {
                return $result;
            }
        }
        return null;
    }
}
