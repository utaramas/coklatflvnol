<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\Framework\DeployStrategy\FilesystemDeployStrategy;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\WordPress\Service\Filesystem;

final class FilesystemDeployStrategyFactory
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    public function __construct(Filesystem $filesystem, ResourceRepositoryInterface $resourceRepository)
    {
        $this->filesystem = $filesystem;
        $this->resourceRepository = $resourceRepository;
    }

    public function create() : DeployStrategyInterface
    {
        return new FilesystemDeployStrategy($this->resourceRepository, $this->createOptions());
    }

    private function createOptions() : array
    {
        $targetDirectory = trailingslashit(get_option('staatic_filesystem_target_directory'));
        $stagingDirectory = trailingslashit(get_option('staatic_work_directory')) . 'staging/';
        $excludePaths = ExcludePathsSetting::resolvedValue(
            get_option('staatic_filesystem_exclude_paths') ?: null,
            $targetDirectory
        );
        $excludePaths = apply_filters('staatic_filesystem_exclude_paths', $excludePaths);
        $options = [
            'targetDirectory' => $targetDirectory,
            'stagingDirectory' => $stagingDirectory,
            'createApacheConfigs' => (bool) get_option('staatic_filesystem_apache_configs'),
            'createNginxConfigs' => (bool) get_option('staatic_filesystem_nginx_configs'),
            'excludePaths' => $excludePaths,
            'copyOnWindows' => \true
        ];
        if (get_option('staatic_filesystem_symlink_uploads')) {
            $sourceUploadsDirectory = $this->filesystem->getUploadsDirectory();
            $targetUploadsDirectory = untrailingslashit($this->filesystem->getUploadsPath());
            $options['symlinks'] = [
                $sourceUploadsDirectory => $targetUploadsDirectory
            ];
        }
        return $options;
    }
}
