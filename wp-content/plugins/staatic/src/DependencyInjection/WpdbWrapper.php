<?php

declare(strict_types=1);

namespace Staatic\WordPress\DependencyInjection;

final class WpdbWrapper
{
    public static function get() : \wpdb
    {
        global $wpdb;
        return $wpdb;
    }
}
