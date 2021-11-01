<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Advanced;

use Staatic\WordPress\Setting\AbstractSetting;

final class UninstallDataSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_uninstall_data';
    }

    public function type() : string
    {
        return self::TYPE_BOOLEAN;
    }

    public function label() : string
    {
        return __('Delete Data', 'staatic');
    }

    /**
     * @return string|null
     */
    public function extendedLabel()
    {
        return __('Delete plugin data', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('This will delete the plugin\'s database tables when uninstalling the plugin.<br><em>Note: the work directory currently needs to be deleted manually.</em>', 'staatic');
    }
}
