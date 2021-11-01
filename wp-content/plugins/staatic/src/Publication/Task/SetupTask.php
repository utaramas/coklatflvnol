<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication\Task;

use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Setting\Advanced\WorkDirectorySetting;

final class SetupTask implements TaskInterface
{
    /**
     * @var WorkDirectorySetting
     */
    private $workDirectory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(WorkDirectorySetting $workDirectory, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->workDirectory = $workDirectory;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    public function name() : string
    {
        return 'setup';
    }

    public function description() : string
    {
        return __('Setting up', 'staatic');
    }

    /**
     * @param Publication $publication
     */
    public function execute($publication) : bool
    {
        $workDirectory = trailingslashit($this->workDirectory->value());
        $this->logger->info(\sprintf('Ensuring work directory exists in %s', $workDirectory));
        $this->filesystem->ensureDirectoryExists($workDirectory);
        $buildDirectory = $workDirectory . 'build/';
        $this->logger->info(\sprintf('Ensuring build directory exists in %s', $buildDirectory));
        $this->filesystem->ensureDirectoryExists($buildDirectory);
        if (!$publication->build()->parentId()) {
            $this->logger->info(\sprintf('Clearing build directory in %s', $buildDirectory));
            $this->filesystem->clearDirectory($buildDirectory);
        }
        $this->validateDeploymentMethod($publication);
        return \true;
    }

    /**
     * @return void
     */
    private function validateDeploymentMethod(Publication $publication)
    {
        if (!get_option('staatic_deployment_method')) {
            $this->invalidDeploymentMethod(__('No deployment method has been selected yet', 'staatic'));
        }
        $errors = apply_filters('staatic_deployment_strategy_validate', [], $publication);
        if (\count($errors) !== 0) {
            $this->invalidDeploymentMethod(\implode(', ', $errors));
        }
        $deployStrategy = apply_filters('staatic_deployment_strategy', $publication);
        if (!$deployStrategy instanceof DeployStrategyInterface) {
            $this->invalidDeploymentMethod(
                __('Deployment method did not register "staatic_deployment_strategy" hook', 'staatic')
            );
        }
    }

    /**
     * @return void
     */
    private function invalidDeploymentMethod(string $message)
    {
        throw new \RuntimeException(\sprintf(
            /* translators: %s: Error message. */
            __('Deployment has not been configured correctly: %s', 'staatic'),
            $message
        ));
    }
}
