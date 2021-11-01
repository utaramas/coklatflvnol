<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\AwsDeployer;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Service\Settings;

final class AwsDeployerModule implements ModuleInterface
{
    const DEPLOYMENT_METHOD_NAME = 'aws';

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ServiceLocator
     */
    private $settingLocator;

    /**
     * @var AwsDeployStrategyFactory
     */
    private $deployStrategyFactory;

    public function __construct(
        Settings $settings,
        ServiceLocator $settingLocator,
        AwsDeployStrategyFactory $deployStrategyFactory
    )
    {
        $this->settings = $settings;
        $this->settingLocator = $settingLocator;
        $this->deployStrategyFactory = $deployStrategyFactory;
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
            $this->settingLocator->get(RegionSetting::class),
            $this->settingLocator->get(AuthSetting::class),
            $this->settingLocator->get(S3Setting::class),
            $this->settingLocator->get(CloudFrontSetting::class)
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
        add_filter('staatic_deployment_strategy', [$this, 'createDeploymentStrategy']);
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
        $deploymentMethods[self::DEPLOYMENT_METHOD_NAME] = __('Amazon Web Services', 'staatic');
        return $deploymentMethods;
    }

    /**
     * @param Publication $publication
     */
    public function createDeploymentStrategy($publication) : DeployStrategyInterface
    {
        return $this->deployStrategyFactory->create();
    }
}
