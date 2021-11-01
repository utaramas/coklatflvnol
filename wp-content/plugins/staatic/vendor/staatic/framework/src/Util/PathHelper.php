<?php

namespace Staatic\Framework\Util;

final class PathHelper
{
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\\-\\.~';
    const CHAR_SUB_DELIMS = '!\\$&\'\\(\\)\\*\\+,;=';
    public static function determineFilePath(string $requestPath) : string
    {
        $filePath = \rawurldecode($requestPath);
        $fileName = \basename($filePath);
        if (($pos = \strrpos($fileName, '.')) !== \false && \substr($filePath, -1) !== '/') {
            $extension = \substr($fileName, $pos + 1);
            if (!\in_array($extension, ['htm', 'html'])) {
                return $filePath;
            }
        }
        if (\substr($filePath, -1) !== '/') {
            $filePath .= '/';
        }
        $filePath .= 'index.html';
        return $filePath;
    }
    public static function encodePath(string $path) : string
    {
        return \preg_replace_callback('/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\\/]++|%(?![A-Fa-f0-9]{2}))/', function ($match) {
            return \rawurlencode($match[0]);
        }, $path);
    }
}
