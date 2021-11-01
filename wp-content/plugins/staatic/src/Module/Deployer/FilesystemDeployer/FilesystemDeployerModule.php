<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\Framework\ConfigGenerator\ApacheConfigGenerator;
use Staatic\Framework\ConfigGenerator\NginxConfigGenerator;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\Framework\PostProcessor\ConfigGeneratorPostProcessor;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\Transformer\MetaRedirectTransformer;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Service\Settings;

final class FilesystemDeployerModule implements ModuleInterface
{
    const DEPLOYMENT_METHOD_NAME = 'filesystem';

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ServiceLocator
     */
    private $settingLocator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FilesystemDeployStrategyFactory
     */
    private $deployStrategyFactory;

    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    public function __construct(
        Settings $settings,
        ServiceLocator $settingLocator,
        Filesystem $filesystem,
        FilesystemDeployStrategyFactory $deployStrategyFactory,
        ResultRepositoryInterface $resultRepository,
        ResourceRepositoryInterface $resourceRepository
    )
    {
        $this->settings = $settings;
        $this->settingLocator = $settingLocator;
        $this->filesystem = $filesystem;
        $this->deployStrategyFactory = $deployStrategyFactory;
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        add_action('init', [$this, 'registerSettings']);
        add_action('wp_loaded', [$this, 'enableDeploymentMethod'], 20);
        if (!is_admin()) {
            return;
        }
        add_filter('staatic_deployment_methods', [$this, 'registerDeploymentMethod']);
    }

    /**
     * @return void
     */
    public function registerSettings()
    {
        $deployerSettings = [
            $this->settingLocator->get(TargetDirectorySetting::class),
            $this->settingLocator->get(ConfigurationFilesSetting::class),
            $this->settingLocator->get(ExcludePathsSetting::class),
            $this->settingLocator->get(SymlinkUploadsDirectorySetting::class)
        ];
        foreach ($deployerSettings as $setting) {
            $this->settings->addSetting('staatic-deployment', $setting);
        }
    }

    /**
     * @return void
     */
    public function enableDeploymentMethod()
    {
        if (!$this->isSelectedDeploymentMethod()) {
            return;
        }
        add_filter('staatic_additional_paths_exclude_paths', [$this, 'overrideAdditionalPathsExcludePaths']);
        add_filter('staatic_exclude_urls', [$this, 'overrideExcludeUrls']);
        add_filter('staatic_transformers', [$this, 'overrideTransformers']);
        add_filter('staatic_post_processors', [$this, 'overridePostProcessors']);
        add_filter('staatic_deployment_strategy', [$this, 'createDeploymentStrategy']);
        add_filter('staatic_deployment_strategy_validate', [$this, 'validateDeploymentStrategy']);
    }

    private function isSelectedDeploymentMethod() : bool
    {
        return get_option('staatic_deployment_method') === self::DEPLOYMENT_METHOD_NAME;
    }

    /**
     * @param mixed[] $deploymentMethods
     */
    public function registerDeploymentMethod($deploymentMethods) : array
    {
        $deploymentMethods[self::DEPLOYMENT_METHOD_NAME] = __('Local Directory', 'staatic');
        return $deploymentMethods;
    }

    /**
     * @param mixed[] $excludePaths
     */
    public function overrideAdditionalPathsExcludePaths($excludePaths) : array
    {
        $excludePaths[] = get_option('staatic_filesystem_target_directory');
        return $excludePaths;
    }

    /**
     * @param mixed[] $excludeUrls
     */
    public function overrideExcludeUrls($excludeUrls) : array
    {
        if (get_option('staatic_filesystem_symlink_uploads')) {
            $excludeRule = $this->filesystem->getUploadsPath() . '*';
            if (!\in_array($excludeRule, $excludeUrls)) {
                $excludeUrls[] = $excludeRule;
            }
        }
        return $excludeUrls;
    }

    /**
     * @param mixed[] $transformers
     */
    public function overrideTransformers($transformers) : array
    {
        \array_unshift($transformers, new MetaRedirectTransformer());
        return $transformers;
    }

    /**
     * @param mixed[] $postProcessors
     */
    public function overridePostProcessors($postProcessors) : array
    {
        $notFoundUrl = null;
        if ($notFoundPath = get_option('staatic_page_not_found_path')) {
            $notFoundUrl = (new Uri(site_url('/')))->withPath($notFoundPath);
        }
        if (get_option('staatic_filesystem_apache_configs')) {
            $postProcessors[] = new ConfigGeneratorPostProcessor(
                $this->resultRepository,
                $this->resourceRepository,
                new ApacheConfigGenerator(
                $notFoundUrl
            )
            );
        }
        if (get_option('staatic_filesystem_nginx_configs')) {
            $postProcessors[] = new ConfigGeneratorPostProcessor(
                $this->resultRepository,
                $this->resourceRepository,
                new NginxConfigGenerator(
                $notFoundUrl
            )
            );
        }
        return $postProcessors;
    }

    /**
     * @param mixed[] $errors
     */
    public function validateDeploymentStrategy($errors) : array
    {
        $targetDirectory = get_option('staatic_filesystem_target_directory');
        if (!\is_dir($targetDirectory)) {
            if (!\mkdir($targetDirectory, 0777, \true)) {
                $errors[] = \sprintf(
                    /* translators: %s: Target directory. */
                    __('Target directory could not be created: %s', 'staatic'),
                    $targetDirectory
                );
            }
        } elseif (!\is_writable($targetDirectory)) {
            $errors[] = \sprintf(
                /* translators: %s: Target directory. */
                __('Target directory is not writable: %s', 'staatic'),
                $targetDirectory
            );
        }
        $stagingDirectory = trailingslashit(get_option('staatic_work_directory')) . 'staging/';
        if ($stagingDirectory) {
            if (!\is_dir($stagingDirectory)) {
                if (!\mkdir($stagingDirectory, 0777, \true)) {
                    $errors[] = \sprintf(
                        /* translators: %s: Staging directory. */
                        __('Staging directory could not be created: %s', 'staatic'),
                        $stagingDirectory
                    );
                }
            } elseif (!\is_writable($stagingDirectory)) {
                $errors[] = \sprintf(
                    /* translators: %s: Staging directory. */
                    __('Staging directory is not writable: %s', 'staatic'),
                    $stagingDirectory
                );
            }
        }
        return $errors;
    }

    /**
     * @param Publication $publication
     */
    public function createDeploymentStrategy($publication) : DeployStrategyInterface
    {
        return $this->deployStrategyFactory->create();
    }
}
