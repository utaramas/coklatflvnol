<?php

namespace Staatic\Vendor\Symfony\Component\Config\Loader;

use Staatic\Vendor\Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Staatic\Vendor\Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Staatic\Vendor\Symfony\Component\Config\Exception\LoaderLoadException;
use Staatic\Vendor\Symfony\Component\Config\FileLocatorInterface;
use Staatic\Vendor\Symfony\Component\Config\Resource\FileExistenceResource;
use Staatic\Vendor\Symfony\Component\Config\Resource\GlobResource;
abstract class FileLoader extends Loader
{
    protected static $loading = [];
    protected $locator;
    private $currentDir;
    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }
    /**
     * @param string $dir
     */
    public function setCurrentDir($dir)
    {
        $this->currentDir = $dir;
    }
    public function getLocator()
    {
        return $this->locator;
    }
    /**
     * @param string|null $type
     * @param bool $ignoreErrors
     * @param string|null $sourceResource
     */
    public function import($resource, $type = null, $ignoreErrors = \false, $sourceResource = null, $exclude = null)
    {
        if (\is_string($resource) && \strlen($resource) !== ($i = \strcspn($resource, '*?{[')) && \false === \strpos($resource, "\n")) {
            $excluded = [];
            foreach ((array) $exclude as $pattern) {
                foreach ($this->glob($pattern, \true, $_, \false, \true) as $path => $info) {
                    $excluded[\rtrim(\str_replace('\\', '/', $path), '/')] = \true;
                }
            }
            $ret = [];
            $isSubpath = 0 !== $i && \false !== \strpos(\substr($resource, 0, $i), '/');
            foreach ($this->glob($resource, \false, $_, $ignoreErrors || !$isSubpath, \false, $excluded) as $path => $info) {
                if (null !== ($res = $this->doImport($path, 'glob' === $type ? null : $type, $ignoreErrors, $sourceResource))) {
                    $ret[] = $res;
                }
                $isSubpath = \true;
            }
            if ($isSubpath) {
                return isset($ret[1]) ? $ret : $ret[0] ?? null;
            }
        }
        return $this->doImport($resource, $type, $ignoreErrors, $sourceResource);
    }
    /**
     * @param string $pattern
     * @param bool $recursive
     * @param bool $ignoreErrors
     * @param bool $forExclusion
     * @param mixed[] $excluded
     */
    protected function glob($pattern, $recursive, &$resource = null, $ignoreErrors = \false, $forExclusion = \false, $excluded = [])
    {
        if (\strlen($pattern) === ($i = \strcspn($pattern, '*?{['))) {
            $prefix = $pattern;
            $pattern = '';
        } elseif (0 === $i || \false === \strpos(\substr($pattern, 0, $i), '/')) {
            $prefix = '.';
            $pattern = '/' . $pattern;
        } else {
            $prefix = \dirname(\substr($pattern, 0, 1 + $i));
            $pattern = \substr($pattern, \strlen($prefix));
        }
        try {
            $prefix = $this->locator->locate($prefix, $this->currentDir, \true);
        } catch (FileLocatorFileNotFoundException $e) {
            if (!$ignoreErrors) {
                throw $e;
            }
            $resource = [];
            foreach ($e->getPaths() as $path) {
                $resource[] = new FileExistenceResource($path);
            }
            return;
        }
        $resource = new GlobResource($prefix, $pattern, $recursive, $forExclusion, $excluded);
        yield from $resource;
    }
    private function doImport($resource, string $type = null, bool $ignoreErrors = \false, string $sourceResource = null)
    {
        try {
            $loader = $this->resolve($resource, $type);
            if ($loader instanceof self && null !== $this->currentDir) {
                $resource = $loader->getLocator()->locate($resource, $this->currentDir, \false);
            }
            $resources = \is_array($resource) ? $resource : [$resource];
            for ($i = 0; $i < ($resourcesCount = \count($resources)); ++$i) {
                if (isset(self::$loading[$resources[$i]])) {
                    if ($i == $resourcesCount - 1) {
                        throw new FileLoaderImportCircularReferenceException(\array_keys(self::$loading));
                    }
                } else {
                    $resource = $resources[$i];
                    break;
                }
            }
            self::$loading[$resource] = \true;
            try {
                $ret = $loader->load($resource, $type);
            } finally {
                unset(self::$loading[$resource]);
            }
            return $ret;
        } catch (FileLoaderImportCircularReferenceException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (!$ignoreErrors) {
                if ($e instanceof LoaderLoadException) {
                    throw $e;
                }
                throw new LoaderLoadException($resource, $sourceResource, 0, $e, $type);
            }
        }
        return null;
    }
}
