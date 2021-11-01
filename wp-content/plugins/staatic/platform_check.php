<?php

function staatic_platform_check_php_failure() {
    if (!is_admin()) {
        return;
    }

    add_action('admin_notices', 'staatic_platform_check_php_notice');
    staatic_platform_check_deactivate();
}

function staatic_platform_check_wordpress_failure() {
    if (!is_admin()) {
        return;
    }

    add_action('admin_notices', 'staatic_platform_check_wordpress_notice');
    staatic_platform_check_deactivate();
}

function staatic_platform_check_php_notice() {
    return staatic_platform_check_notice(
        __(
            sprintf(
                'Staatic for WordPress requires at least PHP version %1$s.; detected PHP version %2$s.',
                STAATIC_MINIMUM_PHP_VERSION,
                PHP_VERSION
            ),
            'staatic'
        )
    );
}

function staatic_platform_check_wordpress_notice() {
    global $wp_version;

    return staatic_platform_check_notice(
        __(
            sprintf(
                'Staatic for WordPress requires at least WordPress version %1$s.; detected WordPress version %2$s.',
                STAATIC_MINIMUM_WORDPRESS_VERSION,
                $wp_version
            ),
            'staatic'
        )
    );
}

function staatic_platform_check_notice($message) {
    echo '<div class="error"><p>';
    echo esc_html__('Activation failed:', 'staatic') . ' ' . esc_html($message);
    echo '</p></div>';
}

function staatic_platform_check_deactivate() {
    static $isDeactivated = false;

    if ($isDeactivated) {
        return;
    }

    $isDeactivated = true;

    deactivate_plugins(plugin_basename(STAATIC_FILE));

    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
}

$platformCheckSuccessful = true;

if (version_compare(PHP_VERSION, STAATIC_MINIMUM_PHP_VERSION, '<')) {
    add_action('admin_init', 'staatic_platform_check_php_failure', 1);
    $platformCheckSuccessful = false;
}

global $wp_version;
if (version_compare($wp_version, STAATIC_MINIMUM_WORDPRESS_VERSION, '<')) {
    add_action('admin_init', 'staatic_platform_check_wordpress_failure', 1);
    $platformCheckSuccessful = false;
}

return $platformCheckSuccessful;
