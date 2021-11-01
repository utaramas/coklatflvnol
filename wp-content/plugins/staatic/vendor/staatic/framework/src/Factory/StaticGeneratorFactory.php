<?php

namespace Staatic\Framework\Factory;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Framework\BuildRepository\BuildRepositoryInterface;
use Staatic\Framework\BuildRepository\InMemoryBuildRepository;
use Staatic\Framework\ResourceRepository\InMemoryResourceRepository;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\ResultRepository\InMemoryResultRepository;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\StaticGenerator;
class StaticGeneratorFactory
{
    /**
     * @param CrawlerInterface $crawler
     * @param UriInterface $entryUrl
     */
    public static function create($crawler, $entryUrl)
    {
        $staticGenerator = new StaticGenerator($crawler, self::createBuildRepository(), self::createResultRepository(), self::createResourceRepository(), self::createTransformers(), self::createPostProcessors());
        return $staticGenerator;
    }
    public static function createBuildRepository() : BuildRepositoryInterface
    {
        return new InMemoryBuildRepository();
    }
    public static function createResultRepository() : ResultRepositoryInterface
    {
        return new InMemoryResultRepository();
    }
    public static function createResourceRepository() : ResourceRepositoryInterface
    {
        return new InMemoryResourceRepository();
    }
    public static function createTransformers() : array
    {
        return [];
    }
    public static function createPostProcessors() : array
    {
        return [];
    }
}
