<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://profile.wordpress.org/apsaraaruna
 * @since             2.0.0
 * @package           Mmh_Sitemap
 *
 * @wordpress-plugin
 * Plugin Name:       Main Menu HTML Site Map
 * Plugin URI:        https://wordpress.org/plugins/main-menu-html-sitemap
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           2.0.0
 * Author:            Apsara Aruna
 * Author URI:        http://profile.wordpress.org/apsaraaruna
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mmh-sitemap
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 2.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MMH_SITEMAP_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mmh-sitemap-activator.php
 */
function activate_mmh_sitemap() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mmh-sitemap-activator.php';
	Mmh_Sitemap_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mmh-sitemap-deactivator.php
 */
function deactivate_mmh_sitemap() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mmh-sitemap-deactivator.php';
	Mmh_Sitemap_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mmh_sitemap' );
register_deactivation_hook( __FILE__, 'deactivate_mmh_sitemap' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mmh-sitemap.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mmh_sitemap() {

	$plugin = new Mmh_Sitemap();
	$plugin->run();

}
run_mmh_sitemap();
