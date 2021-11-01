<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin;

use Staatic\WordPress\Module\ModuleInterface;

final class RegisterPluginActionLinks implements ModuleInterface
{
    /**
     * @return void
     */
    public function hooks()
    {
        if (!is_admin()) {
            return;
        }
        add_filter('plugin_action_links_' . plugin_basename(STAATIC_FILE), [$this, 'actionLinksSetup']);
    }

    /**
     * @param mixed[] $links
     */
    public function actionLinksSetup($links) : array
    {
        \array_unshift(
            $links,
            \sprintf('<a id="staatic-premium-link" href="%s" style="font-weight: bold; color: #38A169;" target="_blank">%s</a>', 'https://staatic.com/wordpress/premium/', __('Staatic Premium', 'staatic'))
        );
        \array_unshift(
            $links,
            \sprintf('<a id="staatic-settings-link" href="%s">%s</a>', admin_url(
                'admin.php?page=staatic-settings'
            ), __('Settings', 'staatic'))
        );
        return $links;
    }
}
