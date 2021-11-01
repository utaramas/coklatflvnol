<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;

final class ExcludePathsSetting extends AbstractSetting
{
    /**
     * @var PartialRenderer
     */
    protected $renderer;

    /**
     * @var TargetDirectorySetting
     */
    private $targetDirectory;

    public function __construct(PartialRenderer $renderer, TargetDirectorySetting $targetDirectory)
    {
        $this->renderer = $renderer;
        $this->targetDirectory = $targetDirectory;
    }

    public function name() : string
    {
        return 'staatic_filesystem_exclude_paths';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    protected function template() : string
    {
        return 'textarea';
    }

    public function label() : string
    {
        return __('Keep Files/Directories', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return \sprintf(
            /* translators: %s: Example paths. */
            __('Optionally add file or directory paths (absolute or relative to the target directory) that need to be left intact (one path per line).<br>Files existing in the target directory that are not part of the build and not in this list will be deleted upon deployment.<br>Examples: %s.', 'staatic'),
            \implode(
                ', ',
                ['<code>favicon.ico</code>',
                '<code>robots.txt</code>',
                __('a Bing/Google/Yahoo/etc. verification file', 'staatic')
            ])
        );
    }

    public function sanitizeValue($value)
    {
        $targetDirectory = \rtrim($this->targetDirectory->value(), '\\/');
        $excludePaths = [];
        foreach (\explode("\n", $value) as $excludePath) {
            $excludePath = \trim($excludePath);
            // Retain empty or commented lines
            if (!$excludePath || \substr($excludePath, 0, 1) === '#') {
                $excludePaths[] = $excludePath;
                continue;
            }
            $absolutePath = \substr($excludePath, 0, 1) === '/' ? $excludePath : \sprintf(
                '%s/%s',
                $targetDirectory,
                $excludePath
            );
            if (!\file_exists($absolutePath)) {
                add_settings_error('staatic-settings', 'invalid_exclude_path', \sprintf(
                    /* translators: %s: Supplied exclude path. */
                    __('The supplied exclude path "%s" does not exist', 'staatic'),
                    $absolutePath
                ), 'warning');
            }
            if (!\in_array($excludePath, $excludePaths)) {
                $excludePaths[] = $excludePath;
            }
        }
        return \implode("\n", $excludePaths);
    }

    /**
     * @param string|null $value
     * @param string $basePath
     */
    public static function resolvedValue($value, $basePath) : array
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        foreach (\explode("\n", $value) as $excludePath) {
            if (!$excludePath || \substr($excludePath, 0, 1) === '#') {
                continue;
            }
            $resolvedValue[] = \substr($excludePath, 0, 1) === '/' ? $excludePath : \sprintf(
                '%s/%s',
                untrailingslashit($basePath),
                $excludePath
            );
        }
        return $resolvedValue;
    }
}
