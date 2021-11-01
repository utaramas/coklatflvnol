<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;
use Staatic\WordPress\Setting\ComposedSettingInterface;

final class ConfigurationFilesSetting extends AbstractSetting implements ComposedSettingInterface
{
    /**
     * @var ServiceLocator
     */
    private $locator;

    public function __construct(PartialRenderer $renderer, ServiceLocator $settingLocator)
    {
        parent::__construct($renderer);
        $this->locator = $settingLocator;
    }

    public function name() : string
    {
        return 'staatic_filesystem_configuration_files';
    }

    public function type() : string
    {
        return self::TYPE_COMPOSED;
    }

    public function label() : string
    {
        return __('Configuration Files', 'staatic');
    }

    public function settings() : array
    {
        return [
            $this->locator->get(ApacheConfigurationFileSetting::class),
            $this->locator->get(NginxConfigurationFileSetting::class)
        ];
    }
}
