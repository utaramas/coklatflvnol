<?php
/**
 * Action links.
 *
 * @since      1.1.0
 * @package    WP Auto Republish
 * @subpackage Wpar\Base
 * @author     Sayan Datta <hello@sayandatta.in>
 */

namespace Wpar\Base;

use Wpar\Helpers\Hooker;
use Wpar\Base\BaseController;

defined( 'ABSPATH' ) || exit;

/**
 * Action links class.
 */
class Actions extends BaseController
{
	use Hooker;

	/**
	 * Register functions.
	 */
	public function register() 
	{
		$this->action( "plugin_action_links_{$this->plugin}", 'settings_link', 10, 1 );
		$this->action( 'plugin_row_meta', 'meta_links', 10, 2 );
		$this->action( 'upgrader_process_complete', 'run_upgrade_action', 10, 2 );
	}

	/**
	 * Register settings link.
	 */
	public function settings_link( $links ) 
	{
		$wparlinks = [
			'<a href="' . admin_url( 'admin.php?page=wp-auto-republish' ) . '">' . __( 'Settings', 'wp-auto-republish' ) . '</a>',
		];
		return array_merge( $wparlinks, $links );
	}

	/**
	 * Register meta links.
	 */
	public function meta_links( $links, $file )
	{
		if ( $file === $this->plugin ) { // only for this plugin
			if ( wpar_load_fs_sdk()->is_not_paying() && ! wpar_load_fs_sdk()->is_trial() ) {
				if ( ! wpar_load_fs_sdk()->is_trial_utilized() ) {
				    $links[] = '<a href="' . wpar_load_fs_sdk()->get_trial_url() . '" target="_blank" style="font-weight: 700;">' . __( 'Try Premium', 'wp-auto-republish' ) . '</a>';
				}
				$links[] = '<a href="https://wordpress.org/support/plugin/wp-auto-republish" target="_blank">' . __( 'Support', 'wp-auto-republish' ) . '</a>';
				$links[] = '<a href="https://www.paypal.me/iamsayan/" target="_blank">' . __( 'Donate', 'wp-auto-republish' ) . '</a>';
			}
		}
		
		return $links;
	}

	/**
	 * Run process after plugin update.
	 */
	public function run_upgrade_action( $upgrader_object, $options )
	{
		// If an update has taken place and the updated type is plugins and the plugins element exists
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
	        // Iterate through the plugins being updated and check if ours is there
		    foreach( $options['plugins'] as $plugin ) {
		        if ( $plugin === $this->plugin ) {
					$this->do_action( 'plugin_updated', $options, $this->version, $upgrader_object, $options['translations']['version'] );
				}
		    }
		}
	}
}