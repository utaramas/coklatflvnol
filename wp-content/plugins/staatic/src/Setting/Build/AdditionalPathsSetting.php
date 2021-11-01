<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;

final class AdditionalPathsSetting extends AbstractSetting
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(PartialRenderer $renderer, Filesystem $filesystem)
    {
        parent::__construct($renderer);
        $this->filesystem = $filesystem;
    }

    public function name() : string
    {
        return 'staatic_additional_paths';
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
        return __('Additional Paths', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return \sprintf(
            /* translators: %s: Example additional paths. */
            __('Optionally add (filesystem) paths that need to be included in the build (one path per line).<br>Example: <code>%s</code>.', 'staatic'),
            $this->filesystem->getUploadsDirectory()
        );
    }

    public function defaultValue()
    {
        return $this->filesystem->getUploadsDirectory();
    }

    public function sanitizeValue($value)
    {
        $homePath = untrailingslashit(ABSPATH);
        $additionalPaths = [];
        foreach (\explode("\n", $value) as $additionalPath) {
            $additionalPath = \trim($additionalPath);
            if (!$additionalPath || \substr($additionalPath, 0, 1) === '#') {
                $additionalPaths[] = $additionalPath;
                continue;
            }
            if (\realpath($additionalPath) === \false) {
                add_settings_error('staatic-settings', 'invalid_additional_path', \sprintf(
                    /* translators: %s: Supplied additional path. */
                    __('The supplied additional path "%s" is not readable and therefore skipped', 'staatic'),
                    $additionalPath
                ));
                $additionalPaths[] = \sprintf('#%s', $additionalPath);
                continue;
            }
            if (\preg_match('~^' . \preg_quote($homePath, '~') . '~i', $additionalPath) === 0) {
                add_settings_error('staatic-settings', 'invalid_additional_path', \sprintf(
                    /* translators: %s: Supplied additional path. */
                    __('The supplied additional path "%s" is not web accessible and therefore skipped', 'staatic'),
                    $additionalPath
                ));
                $additionalPaths[] = \sprintf('#%s', $additionalPath);
                continue;
            }
            if (!\in_array($additionalPath, $additionalPaths)) {
                $additionalPaths[] = $additionalPath;
            }
        }
        return \implode("\n", $additionalPaths);
    }
}
