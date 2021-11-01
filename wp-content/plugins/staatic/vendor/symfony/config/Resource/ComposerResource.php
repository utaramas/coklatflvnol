<?php

namespace Staatic\Vendor\Symfony\Component\Config\Resource;

class ComposerResource implements SelfCheckingResourceInterface
{
    private $vendors;
    private static $runtimeVendors;
    public function __construct()
    {
        self::refresh();
        $this->vendors = self::$runtimeVendors;
    }
    public function getVendors() : array
    {
        return \array_keys($this->vendors);
    }
    public function __toString() : string
    {
        return __CLASS__;
    }
    /**
     * @param int $timestamp
     */
    public function isFresh($timestamp) : bool
    {
        self::refresh();
        return \array_values(self::$runtimeVendors) === \array_values($this->vendors);
    }
    private static function refresh()
    {
        self::$runtimeVendors = [];
        foreach (\get_declared_classes() as $class) {
            if ('C' === $class[0] && 0 === \strpos($class, 'ComposerAutoloaderInit')) {
                $r = new \ReflectionClass($class);
                $v = \dirname($r->getFileName(), 2);
                if (\is_file($v . '/composer/installed.json')) {
                    self::$runtimeVendors[$v] = @\filemtime($v . '/composer/installed.json');
                }
            }
        }
    }
}
