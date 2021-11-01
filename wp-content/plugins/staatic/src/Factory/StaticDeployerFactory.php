<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\Framework\DeploymentRepository\DeploymentRepositoryInterface;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\StaticDeployer;
use Staatic\WordPress\Publication\Publication;

final class StaticDeployerFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeploymentRepositoryInterface
     */
    private $deploymentRepository;

    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    public function __construct(
        LoggerInterface $logger,
        DeploymentRepositoryInterface $deploymentRepository,
        ResultRepositoryInterface $resultRepository
    )
    {
        $this->logger = $logger;
        $this->deploymentRepository = $deploymentRepository;
        $this->resultRepository = $resultRepository;
    }

    public function __invoke(Publication $publication) : StaticDeployer
    {
        /** @var DeployStrategyInterface $deployStrategy */
        $deployStrategy = apply_filters('staatic_deployment_strategy', $publication);
        //!TODO: is apply_filters the right way to do this? guess it's okay...
        if (!$deployStrategy instanceof DeployStrategyInterface) {
            throw new \RuntimeException(\sprintf(
                'Expected to get a DeployStrategyInterface object, got %s instead',
                \get_class($deployStrategy)
            ));
        }
        if ($deployStrategy instanceof LoggerAwareInterface) {
            $deployStrategy->setLogger($this->logger);
        }
        return new StaticDeployer($this->deploymentRepository, $this->resultRepository, $deployStrategy, $this->logger);
    }
}
