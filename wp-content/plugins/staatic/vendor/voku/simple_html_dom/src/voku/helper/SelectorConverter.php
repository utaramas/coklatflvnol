<?php

declare (strict_types=1);
namespace Staatic\Vendor\voku\helper;

use Staatic\Vendor\Symfony\Component\CssSelector\CssSelectorConverter;
class SelectorConverter
{
    protected static $compiled = [];
    /**
     * @param string $selector
     * @param bool $ignoreCssSelectorErrors
     */
    public static function toXPath($selector, $ignoreCssSelectorErrors = \false)
    {
        if (isset(self::$compiled[$selector])) {
            return self::$compiled[$selector];
        }
        if ($selector === 'text') {
            return '//text()';
        }
        if ($selector === 'comment') {
            return '//comment()';
        }
        if (\strpos($selector, '//') === 0) {
            return $selector;
        }
        if (!\class_exists(CssSelectorConverter::class)) {
            throw new \RuntimeException('Unable to filter with a CSS selector as the Symfony CssSelector 2.8+ is not installed (you can use filterXPath instead).');
        }
        $converter = new CssSelectorConverter(\true);
        if ($ignoreCssSelectorErrors) {
            try {
                $xPathQuery = $converter->toXPath($selector);
            } catch (\Exception $e) {
                $xPathQuery = $selector;
            }
        } else {
            $xPathQuery = $converter->toXPath($selector);
        }
        self::$compiled[$selector] = $xPathQuery;
        return $xPathQuery;
    }
}
