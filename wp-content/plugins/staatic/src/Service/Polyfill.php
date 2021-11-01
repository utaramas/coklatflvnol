<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

final class Polyfill
{
    // https://developer.wordpress.org/reference/functions/wp_get_scheduled_event/
    public static function wp_get_scheduled_event($hook, $args = [], $timestamp = null)
    {
        global $wp_version;
        if (\version_compare($wp_version, '5.1.0', '>=')) {
            return wp_get_scheduled_event($hook, $args, $timestamp);
        }
        if (null !== $timestamp && !\is_numeric($timestamp)) {
            return \false;
        }
        $crons = _get_cron_array();
        if (empty($crons)) {
            return \false;
        }
        $key = \md5(\serialize($args));
        if (!$timestamp) {
            // Get next event.
            $next = \false;
            foreach ($crons as $timestamp => $cron) {
                if (isset($cron[$hook][$key])) {
                    $next = $timestamp;
                    break;
                }
            }
            if (!$next) {
                return \false;
            }
            $timestamp = $next;
        } elseif (!isset($crons[$timestamp][$hook][$key])) {
            return \false;
        }
        $event = (object) array(
            'hook' => $hook,
            'timestamp' => $timestamp,
            'schedule' => $crons[$timestamp][$hook][$key]['schedule'],
            'args' => $args
        );
        if (isset($crons[$timestamp][$hook][$key]['interval'])) {
            $event->interval = $crons[$timestamp][$hook][$key]['interval'];
        }
        return $event;
    }

    // https://developer.wordpress.org/reference/functions/wp_unschedule_event/
    public static function wp_unschedule_event($timestamp, $hook, $args = []) : bool
    {
        global $wp_version;
        $result = wp_unschedule_event($timestamp, $hook, $args);
        if ($result !== \true && \version_compare($wp_version, '5.1.0', '>=')) {
            return $result;
        } else {
            return \true;
        }
    }

    // https://developer.wordpress.org/reference/functions/wp_schedule_event/
    public static function wp_schedule_event($timestamp, $recurrence, $hook, $args = []) : bool
    {
        global $wp_version;
        $result = wp_schedule_event($timestamp, $recurrence, $hook, $args);
        if ($result !== \true && \version_compare($wp_version, '5.1.0', '>=')) {
            return $result;
        } else {
            return \true;
        }
    }
}
