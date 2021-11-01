<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleHttp\Psr7;

final class Header
{
    public static function parse($header) : array
    {
        static $trimmed = "\"'  \n\t\r";
        $params = $matches = [];
        foreach (self::normalize($header) as $val) {
            $part = [];
            foreach (\preg_split('/;(?=([^"]*"[^"]*")*[^"]*$)/', $val) as $kvp) {
                if (\preg_match_all('/<[^>]+>|[^=]+/', $kvp, $matches)) {
                    $m = $matches[0];
                    if (isset($m[1])) {
                        $part[\trim($m[0], $trimmed)] = \trim($m[1], $trimmed);
                    } else {
                        $part[] = \trim($m[0], $trimmed);
                    }
                }
            }
            if ($part) {
                $params[] = $part;
            }
        }
        return $params;
    }
    public static function normalize($header) : array
    {
        if (!\is_array($header)) {
            return \array_map('trim', \explode(',', $header));
        }
        $result = [];
        foreach ($header as $value) {
            foreach ((array) $value as $v) {
                if (\strpos($v, ',') === \false) {
                    $result[] = $v;
                    continue;
                }
                foreach (\preg_split('/,(?=([^"]*"[^"]*")*[^"]*$)/', $v) as $vv) {
                    $result[] = \trim($vv);
                }
            }
        }
        return $result;
    }
}
