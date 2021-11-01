<?php

namespace Staatic\Framework\DeployStrategy;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Framework\Deployment;
final class DummyDeployStrategy implements DeployStrategyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    public function __construct()
    {
        $this->logger = new NullLogger();
    }
    /**
     * @param Deployment $deployment
     */
    public function initiate($deployment) : array
    {
        $this->logger->info(\sprintf('Initiating dummy deployment'));
        return [];
    }
    /**
     * @param Deployment $deployment
     * @param mixed[] $results
     * @return void
     */
    public function processResults($deployment, $results)
    {
        $this->logger->info(\sprintf('Processing nothing :-)'));
    }
    /**
     * @param Deployment $deployment
     * @return void
     */
    public function finish($deployment)
    {
        $this->logger->info(\sprintf('Finishing dummy deployment'));
    }
}
