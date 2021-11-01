<?php

declare(strict_types=1);

namespace Staatic\WordPress\Util;

final class HttpUtil
{
    public static function userAgent() : string
    {
        global $wp_version;
        return \sprintf('StaaticWordPress/%s (WordPress %s; %s)', STAATIC_VERSION, $wp_version, home_url('/'));
    }
}
