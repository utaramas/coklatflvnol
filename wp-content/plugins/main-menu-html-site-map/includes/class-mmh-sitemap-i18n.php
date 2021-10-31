<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://profile.wordpress.org/apsaraaruna
 * @since      1.0.0
 *
 * @package    Mmh_Sitemap
 * @subpackage Mmh_Sitemap/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Mmh_Sitemap
 * @subpackage Mmh_Sitemap/includes
 * @author     Apsara Aruna <apsara@mail.com>
 */
class Mmh_Sitemap_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'mmh-sitemap',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
