<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\WordPress\Setting\AbstractSetting;

final class SymlinkUploadsDirectorySetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_filesystem_symlink_uploads';
    }

    public function type() : string
    {
        return self::TYPE_BOOLEAN;
    }

    public function label() : string
    {
        return __('Symlink/Copy Uploads', 'staatic');
    }

    /**
     * @return string|null
     */
    public function extendedLabel()
    {
        return __('Symlink (on Linux) or copy (on Windows) the complete uploads directory.', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('This makes sure every upload is available and greatly improves build time as well.', 'staatic');
    }
}
