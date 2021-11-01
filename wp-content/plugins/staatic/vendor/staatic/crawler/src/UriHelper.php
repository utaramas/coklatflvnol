<?php

namespace Staatic\Crawler;

final class UriHelper
{
    public static function isValidUrl(string $url) : bool
    {
        if (empty($url)) {
            return \false;
        }
        if (\preg_match('~^data:~', $url) === 1) {
            return \false;
        }
        if (\preg_match('~^javascript:~', $url) === 1) {
            return \false;
        }
        return \true;
    }
}
