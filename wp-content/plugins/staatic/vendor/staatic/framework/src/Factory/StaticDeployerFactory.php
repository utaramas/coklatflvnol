<?php

namespace Staatic\Framework\Factory;

use Staatic\Framework\BuildRepository\BuildRepositoryInterface;
use Staatic\Framework\BuildRepository\InMemoryBuildRepository;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\Framework\DeployStrategy\DummyDeployStrategy;
use Staatic\Framework\ResultRepository\InMemoryResultRepository;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\StaticDeployer;
class StaticDeployerFactory
{
    /**
     * @param int|null $maxResultsProcessable
     */
    public static function create($maxResultsProcessable = null)
    {
        $staticDeployer = new StaticDeployer(self::createBuildRepository(), self::createResultRepository(), self::createDeployStrategy(), $maxResultsProcessable);
        return $staticDeployer;
    }
    public static function createBuildRepository() : BuildRepositoryInterface
    {
        return new InMemoryBuildRepository();
    }
    public static function createResultRepository() : ResultRepositoryInterface
    {
        return new InMemoryResultRepository();
    }
    public static function createDeployStrategy() : DeployStrategyInterface
    {
        return new DummyDeployStrategy();
    }
}
