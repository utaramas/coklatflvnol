<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\WordPress\Setting\AbstractSetting;

final class NginxConfigurationFileSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_filesystem_nginx_configs';
    }

    public function type() : string
    {
        return self::TYPE_BOOLEAN;
    }

    public function label() : string
    {
        return __('Generate Nginx Configuration', 'staatic');
    }

    /**
     * @return string|null
     */
    public function extendedLabel()
    {
        return __('Generate nginx_rules.conf file providing actual HTTP redirects, HTTP status overrides, etc.', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('Enable this option if you\'re on a Nginx webserver which imports this configuration file.', 'staatic');
    }
}
