<?php

declare(strict_types=1);

namespace Staatic\WordPress\Bridge\CrawlUrlProvider;

use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Http\Message\UriInterface;

final class AdditionalPathCrawlUrlProvider implements CrawlUrlProviderInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var mixed[]
     */
    private $excludePaths;

    public function __construct(string $path, array $excludePaths = [])
    {
        $this->path = $path;
        $this->excludePaths = \array_map(function ($path) {
            return untrailingslashit($path);
        }, $excludePaths);
    }

    // CrawlerInterface => UrlTransformer?

    /**
     * @param CrawlerInterface $crawler
     */
    public function provide($crawler) : \Generator
    {
        if (\is_file($this->path)) {
            $urls = [$this->convertPathToUrl($this->path)];
        } elseif (\is_dir($this->path)) {
            $paths = $this->scanDirectoryForFiles($this->path, $this->excludePaths);
            $urls = \array_map(function (string $path) {
                return $this->convertPathToUrl($path);
            }, $paths);
        }
        foreach ($urls as $url) {
            (yield CrawlUrl::create($url, $crawler->transformUrl($url)));
        }
    }

    private static function convertPathToUrl(string $path) : UriInterface
    {
        $homePath = untrailingslashit(ABSPATH);
        $relativePath = \str_replace($homePath, '', $path);
        return new Uri(site_url($relativePath));
    }

    private static function scanDirectoryForFiles(string $directory, array $excludePaths = []) : array
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator(new \RecursiveDirectoryIterator(
            $directory,
            \RecursiveDirectoryIterator::SKIP_DOTS
        ), function (
            $fileInfo,
            $path,
            $iterator
        ) use (
            $excludePaths
        ) {
            if (\preg_match('~\\.htaccess$~', $path) === 1) {
                //!TODO: extend
                return \false;
            }
            return !\in_array($path, $excludePaths);
        }));
        $files = [];
        foreach ($iterator as $fileName => $fileInfo) {
            $files[] = $fileName;
        }
        return $files;
    }
}
