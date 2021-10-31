<?php

/**
 * The Main file.
 *
 * @since      1.1.0
 * @package    WP Auto Republish
 * @subpackage Wpar\Core
 * @author     Sayan Datta <hello@sayandatta.in>
 */
namespace Wpar\Core;

use  Wpar\Helpers\Ajax ;
use  Wpar\Helpers\Hooker ;
use  Wpar\Helpers\HelperFunctions ;
defined( 'ABSPATH' ) || exit;
/**
 * Republication class.
 */
class PostRepublish extends \WP_Async_Request
{
    use  Ajax, HelperFunctions, Hooker ;
    /**
     * @var string
     */
    protected  $action = 'wpar_republish_pending_post' ;
    /**
     * Register functions.
     */
    public function register()
    {
        $this->action( 'wpar/global_republish_single_post', 'trigger_republish' );
    }
    
    /**
     * Trigger post update process.
     * 
     * @since v1.1.7
     * @param int   $post_id   Post ID
     */
    public function trigger_republish( $post_id )
    {
        
        if ( get_transient( 'wpar_global_pending_lock' ) === false ) {
            // get single republish event status
            $single_pending = $this->get_meta( $post_id, 'wpar_single_republish_status' );
            // run if post republish is actually enabled
            if ( $this->is_enabled( 'enable_plugin' ) && !$single_pending ) {
                $this->data( [
                    'post_id'          => $post_id,
                    'republish_action' => 'repost',
                ] )->dispatch();
            }
            // delete metas
            $this->delete_meta( $post_id, 'wpar_global_republish_status' );
            $this->delete_meta( $post_id, '_wpar_global_republish_datetime' );
            $this->delete_meta( $post_id, 'wpar_filter_republish_status' );
            // lock republish query
            set_transient( 'wpar_global_pending_lock', true, 10 );
        }
    
    }
    
    /**
     * Handle Trigger post update process.
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    protected function handle()
    {
        $post_id = sanitize_text_field( $_POST['post_id'] );
        $action = sanitize_text_field( $_POST['republish_action'] );
        if ( $action == 'repost' ) {
            $this->update_old_post( $post_id );
        }
    }
    
    /**
     * Run post update process.
     * 
     * @param int   $post_id  Post ID
     * @param bool  $single   Check if it is a single republish event
     * @param bool  $instant  Check if it is one click republish event
     * 
     * @return int $post_id
     */
    protected function update_old_post( $post_id, $single = false, $instant = false )
    {
        $post = get_post( $post_id );
        $timestamp = current_time( 'timestamp', 0 );
        $pub_date = $this->get_meta( $post->ID, '_wpar_original_pub_date' );
        if ( !$pub_date || $post->post_status !== 'future' ) {
            $this->update_meta( $post->ID, '_wpar_original_pub_date', $post->post_date );
        }
        $new_time = $this->get_publish_time( $post->ID, $single, $instant );
        // remove kses filters
        kses_remove_filters();
        $args = [
            'ID'            => $post->ID,
            'post_date'     => $new_time,
            'post_date_gmt' => get_gmt_from_date( $new_time ),
        ];
        $args = $this->do_filter(
            'update_process_args',
            $args,
            $post->ID,
            $post
        );
        //error_log( print_r( $args, true ) );
        wp_update_post( $args );
        //error_log( print_r( $args, true ) );
        $this->set_occurence( $post );
        $this->do_action( 'clear_site_cache' );
        // reinit kses filters
        kses_init_filters();
        return $post_id;
    }
    
    /**
     * Get new post published time.
     * 
     * @since v1.1.7
     * @param int   $post_id   Post ID
     * @param bool  $single    Check if a single republish event
     * @param bool  $instant   Check if one click republish event
     * @param bool  $scheduled Check if scheduled republish event
     * 
     * @return string
     */
    private function get_publish_time(
        $post_id,
        $single,
        $instant = false,
        $scheduled = false
    )
    {
        $post = get_post( $post_id );
        $timestamp = current_time( 'timestamp', 0 );
        
        if ( $this->get_data( 'wpar_republish_post_position', 'one' ) == 'one' ) {
            $new_time = current_time( 'mysql' );
        } else {
            $lastposts = get_posts( [
                'post_type'   => $post->post_type,
                'numberposts' => 1,
                'offset'      => 1,
                'post_status' => 'publish',
                'order'       => 'DESC',
            ] );
            
            if ( !empty($lastposts) ) {
                foreach ( $lastposts as $lastpost ) {
                    $post_date = strtotime( $lastpost->post_date );
                    $new_time = date( 'Y-m-d H:i:s', mktime(
                        date( 'H', $post_date ),
                        date( 'i', $post_date ) + 5,
                        date( 's', $post_date ),
                        date( 'm', $post_date ),
                        date( 'd', $post_date ),
                        date( 'Y', $post_date )
                    ) );
                }
            } else {
                $new_time = current_time( 'mysql' );
            }
        
        }
        
        return $new_time;
    }
    
    /**
     * Custom post type support.
     *
     * @param object $post WP Post object.
     */
    private function set_occurence( $post )
    {
        $repeat = $this->get_meta( $post->ID, '_wpar_post_republish_occurrence' );
        
        if ( !empty($repeat) && is_numeric( $repeat ) ) {
            $repeat++;
        } else {
            $repeat = 1;
        }
        
        $this->update_meta( $post->ID, '_wpar_post_republish_occurrence', $repeat );
    }

}