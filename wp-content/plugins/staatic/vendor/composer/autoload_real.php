<?php

namespace Staatic\Vendor;

use Staatic\Vendor\Composer\Autoload\ClassLoader;
use Staatic\Vendor\Composer\Autoload\ComposerStaticInit2f16933e1205fd04d0e3119522fa91db;
// autoload_real.php @generated by Composer
class ComposerAutoloaderInit2f16933e1205fd04d0e3119522fa91db
{
    private static $loader;
    public static function loadClassLoader($class)
    {
        if ('Staatic\\Vendor\\Composer\\Autoload\\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }
    /**
     * @return ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }
        require __DIR__ . '/platform_check.php';
        \spl_autoload_register(array('Staatic\\Vendor\\ComposerAutoloaderInit2f16933e1205fd04d0e3119522fa91db', 'loadClassLoader'), \true, \true);
        self::$loader = $loader = new ClassLoader(\dirname(\dirname(__FILE__)));
        \spl_autoload_unregister(array('Staatic\\Vendor\\ComposerAutoloaderInit2f16933e1205fd04d0e3119522fa91db', 'loadClassLoader'));
        $useStaticLoader = \PHP_VERSION_ID >= 50600 && !\defined('Staatic\\Vendor\\HHVM_VERSION') && (!\function_exists('zend_loader_file_encoded') || !\zend_loader_file_encoded());
        if ($useStaticLoader) {
            require __DIR__ . '/autoload_static.php';
            \call_user_func(ComposerStaticInit2f16933e1205fd04d0e3119522fa91db::getInitializer($loader));
        } else {
            $classMap = (require __DIR__ . '/autoload_classmap.php');
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }
        $loader->setClassMapAuthoritative(\true);
        $loader->register(\true);
        if ($useStaticLoader) {
            $includeFiles = ComposerStaticInit2f16933e1205fd04d0e3119522fa91db::$files;
        } else {
            $includeFiles = (require __DIR__ . '/autoload_files.php');
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequire2f16933e1205fd04d0e3119522fa91db($fileIdentifier, $file);
        }
        return $loader;
    }
}
function composerRequire2f16933e1205fd04d0e3119522fa91db($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = \true;
    }
}