<?php

namespace Staatic\Vendor\Composer;

use Staatic\Vendor\Composer\Autoload\ClassLoader;
use Staatic\Vendor\Composer\Semver\VersionParser;
class InstalledVersions
{
    private static $installed = array('root' => array('pretty_version' => 'v1.0.4', 'version' => '1.0.4.0', 'aliases' => array(), 'reference' => 'dccc2cbafe75d4b52da0a8a71e079a2a17b593a1', 'name' => 'staatic/staatic-wordpress'), 'versions' => array('async-aws/cloud-front' => array('pretty_version' => '0.1.2', 'version' => '0.1.2.0', 'aliases' => array(), 'reference' => '8b5cbc51947fb9062779715decfe5177e0b9c514'), 'async-aws/core' => array('pretty_version' => '1.11.0', 'version' => '1.11.0.0', 'aliases' => array(), 'reference' => '175aed2583ab00fb2093190edf4392710e073bb9'), 'async-aws/s3' => array('pretty_version' => '1.9.1', 'version' => '1.9.1.0', 'aliases' => array(), 'reference' => '55db6a725cc69de2d4a2181d781f139980c0586e'), 'brick/math' => array('pretty_version' => '0.9.3', 'version' => '0.9.3.0', 'aliases' => array(), 'reference' => 'ca57d18f028f84f777b2168cd1911b0dee2343ae'), 'caseyamcl/guzzle_retry_middleware' => array('pretty_version' => 'v2.6.1', 'version' => '2.6.1.0', 'aliases' => array(), 'reference' => '2d6c8e0bdc0c7102b3000ca157f535da48bd0bd0'), 'deliciousbrains/wp-background-processing' => array('pretty_version' => '1.0.2', 'version' => '1.0.2.0', 'aliases' => array(), 'reference' => '2cbee1abd1b49e1133cd8f611df4d4fc5a8b9800'), 'guzzlehttp/guzzle' => array('pretty_version' => '7.3.0', 'version' => '7.3.0.0', 'aliases' => array(), 'reference' => '7008573787b430c1c1f650e3722d9bba59967628'), 'guzzlehttp/promises' => array('pretty_version' => '1.4.1', 'version' => '1.4.1.0', 'aliases' => array(), 'reference' => '8e7d04f1f6450fef59366c399cfad4b9383aa30d'), 'guzzlehttp/psr7' => array('pretty_version' => '2.0.0', 'version' => '2.0.0.0', 'aliases' => array(), 'reference' => '1dc8d9cba3897165e16d12bb13d813afb1eb3fe7'), 'olvlvl/symfony-dependency-injection-proxy' => array('pretty_version' => 'v3.2.0', 'version' => '3.2.0.0', 'aliases' => array(), 'reference' => '2e0fc0d67debe84d5f6a085998cfd4bcf26b3ece'), 'php-http/async-client-implementation' => array('provided' => array(0 => '*')), 'php-http/client-implementation' => array('provided' => array(0 => '*')), 'psr/cache' => array('pretty_version' => '2.0.0', 'version' => '2.0.0.0', 'aliases' => array(), 'reference' => '213f9dbc5b9bfbc4f8db86d2838dc968752ce13b'), 'psr/container' => array('pretty_version' => '1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '8622567409010282b7aeebe4bb841fe98b58dcaf'), 'psr/container-implementation' => array('provided' => array(0 => '1.0')), 'psr/http-client' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => '2dfb5f6c5eff0e91e20e913f8c5452ed95b86621'), 'psr/http-client-implementation' => array('provided' => array(0 => '1.0')), 'psr/http-factory' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => '12ac7fcd07e5b077433f5f2bee95b3a771bf61be'), 'psr/http-factory-implementation' => array('provided' => array(0 => '1.0')), 'psr/http-message' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => 'f6561bf28d520154e4b0ec72be95418abe6d9363'), 'psr/http-message-implementation' => array('provided' => array(0 => '1.0')), 'psr/log' => array('pretty_version' => '1.1.4', 'version' => '1.1.4.0', 'aliases' => array(), 'reference' => 'd49695b909c3b7628b6289db5479a1c204601f11'), 'psr/simple-cache' => array('pretty_version' => '1.0.1', 'version' => '1.0.1.0', 'aliases' => array(), 'reference' => '408d5eafb83c57f6365a3ca330ff23aa4a5fa39b'), 'ralouphie/getallheaders' => array('pretty_version' => '3.0.3', 'version' => '3.0.3.0', 'aliases' => array(), 'reference' => '120b605dfeb996808c31b6477290a714d356e822'), 'ramsey/collection' => array('pretty_version' => '1.2.1', 'version' => '1.2.1.0', 'aliases' => array(), 'reference' => 'eaca1dc1054ddd10cbd83c1461907bee6fb528fa'), 'ramsey/uuid' => array('pretty_version' => '4.2.1', 'version' => '4.2.1.0', 'aliases' => array(), 'reference' => 'fe665a03df4f056aa65af552a96e1976df8c8dae'), 'rhumsaa/uuid' => array('replaced' => array(0 => '4.2.1')), 'staatic/crawler' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(0 => '0.0.x-dev'), 'reference' => '45d4b4d232a812662a17c4b1e43923bb4fe66bb8'), 'staatic/framework' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(0 => '0.0.x-dev'), 'reference' => '4590cd6eb0450aa2c3b3764cabe168ee55415d8f'), 'staatic/staatic-wordpress' => array('pretty_version' => 'v1.0.4', 'version' => '1.0.4.0', 'aliases' => array(), 'reference' => 'dccc2cbafe75d4b52da0a8a71e079a2a17b593a1'), 'symfony/config' => array('pretty_version' => 'v5.2.10', 'version' => '5.2.10.0', 'aliases' => array(), 'reference' => '1156feb067e6962b3c4444d172fd0d4d8473cd5b'), 'symfony/css-selector' => array('pretty_version' => 'v5.3.4', 'version' => '5.3.4.0', 'aliases' => array(), 'reference' => '7fb120adc7f600a59027775b224c13a33530dd90'), 'symfony/dependency-injection' => array('pretty_version' => 'v5.2.10', 'version' => '5.2.10.0', 'aliases' => array(), 'reference' => '22b1ed3e5d080d69ec913e04eac3699eafb6b5b4'), 'symfony/deprecation-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => '5f38c8804a9e97d23e0c8d63341088cd8a22d627'), 'symfony/filesystem' => array('pretty_version' => 'v5.2.10', 'version' => '5.2.10.0', 'aliases' => array(), 'reference' => '9aa15870b021a34de200a15cff38844db4a930fa'), 'symfony/http-client' => array('pretty_version' => 'v5.2.10', 'version' => '5.2.10.0', 'aliases' => array(), 'reference' => 'a1fef5661ae36f39c1d48cb0672c4591e9452323'), 'symfony/http-client-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => '7e82f6084d7cae521a75ef2cb5c9457bbda785f4'), 'symfony/http-client-implementation' => array('provided' => array(0 => '2.2')), 'symfony/polyfill-ctype' => array('pretty_version' => 'v1.23.0', 'version' => '1.23.0.0', 'aliases' => array(), 'reference' => '46cd95797e9df938fdd2b03693b5fca5e64b01ce'), 'symfony/polyfill-php70' => array('replaced' => array(0 => '*')), 'symfony/polyfill-php71' => array('pretty_version' => 'v1.20.0', 'version' => '1.20.0.0', 'aliases' => array(), 'reference' => '2d6cdeca7ea470e50db9e544c9ec4b1955036c22'), 'symfony/polyfill-php72' => array('pretty_version' => 'v1.23.0', 'version' => '1.23.0.0', 'aliases' => array(), 'reference' => '9a142215a36a3888e30d0a9eeea9766764e96976'), 'symfony/polyfill-php73' => array('pretty_version' => 'v1.23.0', 'version' => '1.23.0.0', 'aliases' => array(), 'reference' => 'fba8933c384d6476ab14fb7b8526e5287ca7e010'), 'symfony/polyfill-php74' => array('pretty_version' => 'v1.23.0', 'version' => '1.23.0.0', 'aliases' => array(), 'reference' => 'a5d80cdf049bd3b0af6da91184a2cd37533c0fd8'), 'symfony/polyfill-php80' => array('pretty_version' => 'v1.23.1', 'version' => '1.23.1.0', 'aliases' => array(), 'reference' => '1100343ed1a92e3a38f9ae122fc0eb21602547be'), 'symfony/polyfill-php81' => array('pretty_version' => 'v1.23.0', 'version' => '1.23.0.0', 'aliases' => array(), 'reference' => 'e66119f3de95efc359483f810c4c3e6436279436'), 'symfony/service-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => 'f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb'), 'symfony/service-implementation' => array('provided' => array(0 => '1.0|2.0')), 'voku/simple_html_dom' => array('pretty_version' => '4.7.29', 'version' => '4.7.29.0', 'aliases' => array(), 'reference' => '079067c704b714b7c2813971297bb340307813e7')));
    private static $canGetVendors;
    private static $installedByVendor = array();
    public static function getInstalledPackages()
    {
        $packages = array();
        foreach (self::getInstalled() as $installed) {
            $packages[] = \array_keys($installed['versions']);
        }
        if (1 === \count($packages)) {
            return $packages[0];
        }
        return \array_keys(\array_flip(\call_user_func_array('array_merge', $packages)));
    }
    public static function isInstalled($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (isset($installed['versions'][$packageName])) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \Staatic\Vendor\Composer\Semver\VersionParser $parser
     */
    public static function satisfies($parser, $packageName, $constraint)
    {
        $constraint = $parser->parseConstraints($constraint);
        $provided = $parser->parseConstraints(self::getVersionRanges($packageName));
        return $provided->matches($constraint);
    }
    public static function getVersionRanges($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            $ranges = array();
            if (isset($installed['versions'][$packageName]['pretty_version'])) {
                $ranges[] = $installed['versions'][$packageName]['pretty_version'];
            }
            if (\array_key_exists('aliases', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['aliases']);
            }
            if (\array_key_exists('replaced', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['replaced']);
            }
            if (\array_key_exists('provided', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['provided']);
            }
            return \implode(' || ', $ranges);
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getPrettyVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['pretty_version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['pretty_version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getReference($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['reference'])) {
                return null;
            }
            return $installed['versions'][$packageName]['reference'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getRootPackage()
    {
        $installed = self::getInstalled();
        return $installed[0]['root'];
    }
    public static function getRawData()
    {
        @\trigger_error('getRawData only returns the first dataset loaded, which may not be what you expect. Use getAllRawData() instead which returns all datasets for all autoloaders present in the process.', \E_USER_DEPRECATED);
        return self::$installed;
    }
    public static function getAllRawData()
    {
        return self::getInstalled();
    }
    public static function reload($data)
    {
        self::$installed = $data;
        self::$installedByVendor = array();
    }
    private static function getInstalled()
    {
        if (null === self::$canGetVendors) {
            self::$canGetVendors = \method_exists('Staatic\\Vendor\\Composer\\Autoload\\ClassLoader', 'getRegisteredLoaders');
        }
        $installed = array();
        if (self::$canGetVendors) {
            foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
                if (isset(self::$installedByVendor[$vendorDir])) {
                    $installed[] = self::$installedByVendor[$vendorDir];
                } elseif (\is_file($vendorDir . '/composer/installed.php')) {
                    $installed[] = self::$installedByVendor[$vendorDir] = (require $vendorDir . '/composer/installed.php');
                }
            }
        }
        $installed[] = self::$installed;
        return $installed;
    }
}
