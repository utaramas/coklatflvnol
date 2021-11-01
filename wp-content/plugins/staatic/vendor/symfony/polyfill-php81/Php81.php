<?php

namespace Staatic\Vendor\Symfony\Polyfill\Php81;

final class Php81
{
    public static function array_is_list(array $array) : bool
    {
        if ([] === $array) {
            return \true;
        }
        $nextKey = -1;
        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return \false;
            }
        }
        return \true;
    }
}