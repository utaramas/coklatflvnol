<?php

/**
 * Fetch eligible posts.
 *
 * @since      1.2.0
 * @package    WP Auto Republish
 * @subpackage Wpar\Core
 * @author     Sayan Datta <hello@sayandatta.in>
 */
namespace Wpar\Core;

use  Wpar\Helpers\Hooker ;
use  Wpar\Helpers\HelperFunctions ;
defined( 'ABSPATH' ) || exit;
/**
 * Republication class.
 */
class FetchPosts extends \WP_Background_Process
{
    use  HelperFunctions, Hooker ;
    /**
     * @var string
     */
    protected  $action = 'wpar_get_old_posts' ;
    /**
     * Query Data
     *
     * (default value: array())
     *
     * @var array
     * @access protected
     */
    private  $query = array() ;
    /**
     * Register functions.
     */
    public function register()
    {
        $this->action( 'init', 'generate_cron' );
        $this->action( 'wpar/run_global_republish', 'run_republish_process' );
        $this->filter( 'cron_schedules', 'global_cron_schedules' );
    }
    
    /**
     * Create custom WP Cron intervals.
     */
    public function global_cron_schedules( $schedules )
    {
        $interval = $this->do_filter( 'global_cron_interval', 5 );
        $schedules['wpar_global_cron'] = [
            'interval' => MINUTE_IN_SECONDS * $interval,
            'display'  => sprintf( __( 'Every %s Minutes' ), $interval ),
        ];
        return $schedules;
    }
    
    /**
     * Generate Cron event if not already exists.
     */
    public function generate_cron()
    {
        if ( !wp_next_scheduled( 'wpar/run_global_republish' ) ) {
            wp_schedule_event( time(), 'wpar_global_cron', 'wpar/run_global_republish' );
        }
    }
    
    /**
     * Run auto republish process.
     */
    public function run_republish_process()
    {
        if ( $this->is_enabled( 'enable_plugin' ) && $this->valid_next_run() && $this->do_filter( 'global_republish_process', true ) ) {
            
            if ( get_transient( 'wpar_global_lock' ) === false ) {
                // run post republish query
                $this->get_old_posts();
                // lock republish query
                set_transient( 'wpar_global_lock', true, 10 );
                // update log reference
                update_option( 'wpar_global_last_run', current_time( 'timestamp', 0 ) );
            }
        
        }
    }
    
    /**
     * Get eligible posts.
     */
    private function get_old_posts()
    {
        $post_types = $this->get_data( 'wpar_post_types', [ 'post' ] );
        
        if ( !empty($post_types) ) {
            foreach ( $post_types as $post_type ) {
                $this->push_to_queue( $post_type );
            }
            $this->save()->dispatch();
        }
    
    }
    
    /**
     * Task
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $item )
    {
        $this->query_posts( $item );
        sleep( 2 );
        return false;
    }
    
    /**
     * Get eligible post ids for every available post types
     *
     * @param string $post_type WordPress post types
     */
    private function query_posts( $post_type )
    {
        $timestamp = current_time( 'timestamp', 0 );
        $overwrite = $this->get_data( 'wpar_exclude_by_type', 'none' );
        $taxonomies = $this->get_data( 'wpar_post_taxonomy', [] );
        $post_age = $this->get_data( 'wpar_republish_post_age', 120 );
        $cats = $tags = $terms = [];
        $args = [
            'post_status' => 'publish',
            'post_type'   => $post_type,
            'numberposts' => -1,
        ];
        if ( $post_age != 0 ) {
            $args['date_query'][]['before'] = $this->do_filter( 'post_before_date', date( 'Y-m-d', strtotime( "-{$post_age} days", $timestamp ) ), $timestamp );
        }
        
        if ( !in_array( $post_type, [ 'post', 'page', 'attachment' ] ) ) {
            $args['meta_query'] = [
                'relation' => 'AND',
                [
                'key'     => 'wpar_global_republish_status',
                'compare' => 'NOT EXISTS',
            ],
                [
                'relation' => 'OR',
                [
                'key'     => '_wpar_post_republish_occurrence',
                'compare' => 'NOT EXISTS',
            ],
                [
                'key'     => '_wpar_post_republish_occurrence',
                'value'   => '3',
                'compare' => '<=',
            ],
            ],
            ];
        } else {
            $args['meta_query'] = [ [
                'key'     => 'wpar_global_republish_status',
                'compare' => 'NOT EXISTS',
            ] ];
        }
        
        
        if ( $overwrite != 'none' && !empty($taxonomies) ) {
            foreach ( $taxonomies as $taxonomy ) {
                $get_item = explode( '|', $taxonomy );
                $type = $get_item[0];
                $term_name = $get_item[1];
                $term_id = $get_item[2];
                if ( $post_type === $type && is_object_in_taxonomy( $post_type, $term_name ) ) {
                    
                    if ( $term_name == 'category' ) {
                        $cats[] = $term_id;
                    } elseif ( $term_name == 'post_tag' ) {
                        $tags[] = $term_id;
                    } else {
                    }
                
                }
            }
            
            if ( $overwrite == 'include' ) {
                if ( !empty($cats) ) {
                    $args['category__in'] = $cats;
                }
                if ( !empty($tags) ) {
                    $args['tag__in'] = $tags;
                }
            } elseif ( $overwrite == 'exclude' ) {
                if ( !empty($cats) ) {
                    $args['category__not_in'] = $cats;
                }
                if ( !empty($tags) ) {
                    $args['tag__not_in'] = $tags;
                }
            }
        
        }
        
        $args = $this->do_filter( 'query_args', $args );
        //error_log( print_r( $args, true ) );
        // store post objects into an array
        $this->query[] = get_posts( $args );
    }
    
    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        parent::complete();
        // update future reference
        update_option( 'wpar_last_global_cron_run', current_time( 'timestamp', 0 ) );
        $query = $this->query;
        $timestamp = current_time( 'timestamp', 0 );
        $weekdays = $this->get_data( 'wpar_days' );
        $orderby = $this->get_data( 'wpar_republish_orderby' );
        $order = $this->get_data( 'wpar_republish_method', 'old_first' );
        $exclude_ids = $this->get_data( 'wpar_override_category_tag' );
        $exclude_ids = preg_replace( [
            '/[^\\d,]/',
            '/(?<=,),+/',
            '/^,+/',
            '/,+$/'
        ], '', $exclude_ids );
        $overwrite = $this->get_data( 'wpar_exclude_by_type', 'none' );
        // merge all existing arrays
        $posts_list = array_merge( ...$query );
        $post_ids = wp_list_pluck( $posts_list, 'ID' );
        //error_log( print_r( $posts_list, true ) );
        
        if ( !empty($post_ids) ) {
            $args = [
                'post_type'   => $this->get_data( 'wpar_post_types', [ 'post' ] ),
                'post_status' => 'publish',
                'post__in'    => $post_ids,
                'numberposts' => $this->do_filter( 'global_republish_count', 1 ),
                'orderby'     => 'date',
            ];
            
            if ( !empty($order) ) {
                $args['order'] = 'ASC';
                if ( $order == 'new_first' ) {
                    $args['order'] = 'DESC';
                }
            }
            
            if ( !empty($orderby) ) {
                $args['orderby'] = $orderby;
            }
            if ( !empty($exclude_ids) ) {
                
                if ( $overwrite == 'include' ) {
                    $args['post__in'] = array_diff( $post_ids, explode( ',', $exclude_ids ) );
                } elseif ( $overwrite == 'exclude' ) {
                    $args['post__in'] = array_unique( array_merge( $post_ids, explode( ',', $exclude_ids ) ) );
                }
            
            }
            $args = $this->do_filter( 'post_query_args', $args );
            //error_log( print_r( $args, true ) );
            $posts = get_posts( $args );
            if ( !empty($posts) ) {
                foreach ( $posts as $post ) {
                    if ( !empty($weekdays) && $this->do_filter(
                        'run_global_republish_cron',
                        true,
                        $post->ID,
                        $post
                    ) ) {
                        
                        if ( !wp_next_scheduled( 'wpar/global_republish_single_post', [ $post->ID ] ) ) {
                            // get required date time
                            $datetime = $this->next_schedule( $timestamp, $weekdays, 'local' );
                            // schedule single post republish event
                            wp_schedule_single_event( get_gmt_from_date( $datetime, 'U' ), 'wpar/global_republish_single_post', [ $post->ID ] );
                            // update required post metas
                            $this->update_meta( $post->ID, 'wpar_filter_republish_status', 'pending' );
                            $this->update_meta( $post->ID, '_wpar_global_republish_datetime', $datetime );
                        }
                    
                    }
                }
            }
        }
    
    }
    
    /**
     * Generate Single cron time.
     * 
     * @param int     $timestamp Local Timestamp
     * @param array   $weekdays  Available weekdays
     * @param string  $format    Datetime format
     * 
     * @return int|string  Generated UTC timestamp
     */
    private function next_schedule( $timestamp, $weekdays, $format = 'GMT' )
    {
        $cur_time = strtotime( date( 'H:i:s', $timestamp ) );
        $start_time = strtotime( $this->get_data( 'wpar_start_time', '05:00:00' ) );
        $end_time = strtotime( $this->get_data( 'wpar_end_time', '23:59:59' ) );
        $slop = $this->get_data( 'wpar_random_republish_interval', 14400 );
        if ( $start_time >= $end_time ) {
            $start_time = strtotime( '05:00:00' );
        }
        $i = 1;
        while ( $i <= 7 ) {
            $next_timestamp = strtotime( '+' . $i . ' days', $timestamp );
            $next_date = lcfirst( date( 'D', $next_timestamp ) );
            if ( in_array( $next_date, $weekdays ) ) {
                break;
            }
            $i++;
        }
        $gap = mt_rand( 0, $slop );
        $final_end_time = $start_time + $gap;
        if ( $final_end_time > $end_time ) {
            $final_end_time = $end_time;
        }
        $rand_time = mt_rand( $start_time, $final_end_time );
        $final_timestamp = $timestamp;
        if ( !in_array( lcfirst( date( 'D', $timestamp ) ), $weekdays ) ) {
            $final_timestamp = $next_timestamp;
        }
        $new_time = $cur_time + $gap;
        
        if ( $new_time >= $start_time && $new_time <= $end_time ) {
            $datetime = $final_timestamp + $gap;
        } else {
            
            if ( $new_time > $end_time ) {
                $datetime = strtotime( date( 'Y-m-d', $next_timestamp ) . ' ' . date( 'H:i:s', $rand_time ) );
            } elseif ( $new_time < $start_time ) {
                $datetime = strtotime( date( 'Y-m-d', $final_timestamp ) . ' ' . date( 'H:i:s', $rand_time ) );
            } else {
                $datetime = $final_timestamp + $gap;
            }
        
        }
        
        $formatted_date = date( 'Y-m-d H:i:s', $datetime );
        if ( $format == 'local' ) {
            return $formatted_date;
        }
        return get_gmt_from_date( $formatted_date, 'U' );
    }
    
    /**
     * Check if current run is actually eligible.
     */
    private function valid_next_run()
    {
        $last = get_option( 'wpar_last_global_cron_run' );
        $current_time = current_time( 'timestamp', 0 );
        $interval = $this->get_data( 'wpar_minimun_republish_interval', 43200 );
        $proceed = false;
        if ( $this->slot_available( $current_time ) ) {
            
            if ( false === $last ) {
                $proceed = true;
            } elseif ( is_numeric( $last ) ) {
                if ( $current_time - $last >= $interval ) {
                    $proceed = true;
                }
            }
        
        }
        return $proceed;
    }
    
    /**
     * Check if weekdays are available.
     * 
     * @param int $timestamp Local Timestamp
     * 
     * @return bool
     */
    private function slot_available( $timestamp )
    {
        $start_time = strtotime( $this->get_data( 'wpar_start_time', '05:00:00' ) );
        $end_time = strtotime( $this->get_data( 'wpar_end_time', '23:59:59' ) );
        $cur_time = strtotime( date( 'H:i:s', $timestamp ) );
        $weekdays = $this->get_data( 'wpar_days' );
        $next_date = lcfirst( date( 'D', $timestamp ) );
        $available = false;
        if ( $cur_time >= $start_time && $cur_time <= $end_time ) {
            if ( !empty($weekdays) && in_array( $next_date, $weekdays ) ) {
                $available = true;
            }
        }
        return $available;
    }

}