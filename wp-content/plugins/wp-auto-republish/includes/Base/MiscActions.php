<?php

/**
 * Action links.
 *
 * @since      1.2.0
 * @package    WP Auto Republish
 * @subpackage Wpar\Base
 * @author     Sayan Datta <hello@sayandatta.in>
 */
namespace Wpar\Base;

use  Wpar\Helpers\Hooker ;
use  Wpar\Helpers\HelperFunctions ;
defined( 'ABSPATH' ) || exit;
/**
 * Action links class.
 */
class MiscActions
{
    use  HelperFunctions, Hooker ;
    /**
     * Register functions.
     */
    public function register()
    {
        $this->action( 'wpar/plugin_updated', 'generate_schedules' );
        $this->action( 'wpar/after_plugin_uninstall', 'remove_cron_schedules' );
        $this->action( 'wpar/after_plugin_uninstall', 'meta_cleanup' );
        $this->action( 'wpar/remove_post_metadata', 'meta_cleanup' );
    }
    
    /**
     * Remove all cron scheduled on plugin uninstallation.
     */
    public function generate_schedules()
    {
        $args = [
            'post_type'   => 'any',
            'numberposts' => -1,
            'post_status' => 'any',
            'meta_query'  => [
            'relation' => 'AND',
            [
            'key'     => '_wpar_repost_schedule_datetime',
            'compare' => 'EXISTS',
        ],
            [
            'key'     => '_wpar_repost_done',
            'value'   => 'no',
            'compare' => '=',
        ],
        ],
        ];
        //error_log( print_r( $args, true ) );
        $posts = get_posts( $args );
        if ( !empty($posts) ) {
            foreach ( $posts as $post ) {
                $datetime = $this->get_meta( $post->ID, '_wpar_repost_schedule_datetime' );
                $this->do_action(
                    'upgrader_single_cron',
                    $post->ID,
                    $datetime,
                    true
                );
            }
        }
    }
    
    /**
     * Remove all cron scheduled on plugin uninstallation.
     */
    public function remove_cron_schedules()
    {
        $args = [
            'post_type'   => 'any',
            'numberposts' => -1,
            'post_status' => 'any',
            'meta_query'  => [
            'relation' => 'OR',
            [
            'key'     => 'wpar_global_republish_status',
            'compare' => 'EXISTS',
        ],
            [
            'key'     => 'wpar_single_republish_status',
            'compare' => 'EXISTS',
        ],
        ],
        ];
        //error_log( print_r( $args, true ) );
        $posts = get_posts( $args );
        if ( !empty($posts) ) {
            foreach ( $posts as $post ) {
                // schedule cron if not exists
                wp_clear_scheduled_hook( 'wpar/global_republish_single_post', [ $post->ID ] );
            }
        }
    }
    
    /**
     * Post meta cleanup.
     */
    public function meta_cleanup()
    {
        $args = [
            'numberposts' => -1,
            'post_type'   => 'any',
            'post_status' => 'any',
        ];
        $posts = get_posts( $args );
        if ( !empty($posts) ) {
            foreach ( $posts as $post ) {
                $metas = get_post_custom( $post->ID );
                foreach ( $metas as $key => $values ) {
                    if ( strpos( $key, 'wpar_' ) !== false ) {
                        $this->delete_meta( $post->ID, $key );
                    }
                }
            }
        }
    }

}