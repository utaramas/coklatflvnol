<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin;

use Staatic\WordPress\Module\ModuleInterface;

final class RegisterAssets implements ModuleInterface
{
    /**
     * @var string
     */
    private $pluginPath;

    /**
     * @var string
     */
    private $pluginUrl;

    /**
     * @var string
     */
    private $pluginVersion;

    public function __construct(string $pluginVersion)
    {
        $this->pluginPath = STAATIC_PATH;
        $this->pluginUrl = STAATIC_URL;
        $this->pluginVersion = $pluginVersion;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (!is_admin()) {
            return;
        }
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        // add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
    }

    /**
     * @return void
     */
    public function enqueueStyles()
    {
        wp_enqueue_style(
            'staatic-admin',
            \sprintf('%s/assets/admin.css', $this->pluginUrl),
            [],
            $this->pluginVersion,
            'all'
        );
    }

    /**
     * @return void
     */
    public function enqueueScripts()
    {
        $scriptAsset = (require \sprintf('%s/assets/admin.asset.php', $this->pluginPath));
        wp_enqueue_script(
            'staatic-admin',
            \sprintf('%s/assets/admin.js', $this->pluginUrl),
            $scriptAsset['dependencies'],
            $scriptAsset['version']
        );
    }

    /**
     * @return void
     */
    public function enqueueBlockEditorAssets()
    {
        wp_enqueue_style(
            'staatic-block-editor',
            \sprintf('%s/assets/block-editor.css', $this->pluginUrl),
            [],
            $this->pluginVersion,
            'all'
        );
        $scriptAsset = (require \sprintf('%s/assets/block-editor.asset.php', $this->pluginPath));
        wp_enqueue_script(
            'staatic-block-editor',
            \sprintf('%s/assets/block-editor.js', $this->pluginUrl),
            $scriptAsset['dependencies'],
            $scriptAsset['version']
        );
    }

    public static function getDefaultPriority() : int
    {
        return 20;
    }
}
