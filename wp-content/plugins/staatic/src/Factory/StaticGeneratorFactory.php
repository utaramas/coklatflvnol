<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\GuzzleHttp\ClientInterface;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Crawler\CrawlOptions;
use Staatic\Crawler\CrawlQueue\CrawlQueueInterface;
use Staatic\Crawler\CrawlUrlProvider\AdditionalCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\EntryCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\PageNotFoundCrawlUrlProvider;
use Staatic\Crawler\Crawler;
use Staatic\Crawler\KnownUrlsContainer\KnownUrlsContainerInterface;
use Staatic\Crawler\UrlTransformer\UrlTransformerInterface;
use Staatic\Framework\BuildRepository\BuildRepositoryInterface;
use Staatic\Framework\PostProcessor\AdditionalRedirectsPostProcessor;
use Staatic\Framework\PostProcessor\DuplicatesRemoverPostProcessor;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\StaticGenerator;
use Staatic\Framework\Transformer\UnmatchedUrlTransformer;
use Staatic\Framework\Transformer\StaaticTransformer;
use Staatic\WordPress\Bridge\CrawlUrlProvider\AdditionalPathCrawlUrlProvider;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Setting\Build\AdditionalRedirectsSetting;

final class StaticGeneratorFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CrawlProfileFactory
     */
    private $crawlProfileFactory;

    /**
     * @var CrawlQueueInterface
     */
    private $crawlQueue;

    /**
     * @var KnownUrlsContainerInterface
     */
    private $knownUrlsContainer;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var BuildRepositoryInterface
     */
    private $buildRepository;

    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var UrlTransformerInterface
     */
    private $urlTransformer;

    public function __construct(
        LoggerInterface $logger,
        CrawlProfileFactory $crawlProfileFactory,
        CrawlQueueInterface $crawlQueue,
        KnownUrlsContainerInterface $knownUrlsContainer,
        ClientInterface $internalHttpClient,
        BuildRepositoryInterface $buildRepository,
        ResultRepositoryInterface $resultRepository,
        ResourceRepositoryInterface $resourceRepository,
        UrlTransformerInterface $urlTransformer
    )
    {
        $this->logger = $logger;
        $this->crawlProfileFactory = $crawlProfileFactory;
        $this->crawlQueue = $crawlQueue;
        $this->knownUrlsContainer = $knownUrlsContainer;
        $this->httpClient = $internalHttpClient;
        $this->buildRepository = $buildRepository;
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->urlTransformer = $urlTransformer;
    }

    /**
     * @param int|null $batchSize
     */
    public function __invoke(Publication $publication, $batchSize = null) : StaticGenerator
    {
        $build = $publication->build();
        $crawler = new Crawler($this->httpClient, ($this->crawlProfileFactory)($build->entryUrl(), $build->destinationUrl()), $this->crawlQueue, $this->knownUrlsContainer, $this->createCrawlUrlProviders(
            $publication
        ), new CrawlOptions([
            'concurrency' => get_option('staatic_http_concurrency'),
            'maxCrawls' => $batchSize,
            'maxDepth' => $build->parentId() ? 1 : null,
            'forceAssets' => $build->parentId() ? \true : \false
        ]));
        if ($crawler instanceof LoggerAwareInterface) {
            $crawler->setLogger($this->logger);
        }
        return new StaticGenerator(
            $crawler,
            $this->buildRepository,
            $this->resultRepository,
            $this->resourceRepository,
            $this->createTransformers(
            $publication
        ),
            $this->createPostProcessors(
            $publication
        ),
            $this->logger
        );
    }

    private function createTransformers(Publication $publication) : array
    {
        $transformers = [new UnmatchedUrlTransformer($publication->build()->entryUrl(), $this->urlTransformer)];
        $transformers = apply_filters('staatic_transformers', $transformers, $publication);
        $transformers[] = new StaaticTransformer();
        foreach ($transformers as $transformer) {
            if ($transformer instanceof LoggerAwareInterface) {
                $transformer->setLogger($this->logger);
            }
        }
        return $transformers;
    }

    private function createPostProcessors(Publication $publication) : array
    {
        $postProcessors = [];
        $additionalRedirects = AdditionalRedirectsSetting::resolvedValue(
            get_option('staatic_additional_redirects') ?: null,
            new Uri(get_option('staatic_destination_url'))
        );
        if (\count($additionalRedirects)) {
            $postProcessors[] = new AdditionalRedirectsPostProcessor(
                $this->resultRepository,
                $this->resourceRepository,
                $additionalRedirects,
                $this->urlTransformer->transform(
                new Uri(site_url('/'))
            )
            );
        }
        $postProcessors[] = new DuplicatesRemoverPostProcessor($this->resultRepository);
        $postProcessors = apply_filters('staatic_post_processors', $postProcessors, $publication);
        foreach ($postProcessors as $postProcessor) {
            if ($postProcessor instanceof LoggerAwareInterface) {
                $postProcessor->setLogger($this->logger);
            }
        }
        return $postProcessors;
    }

    private function createCrawlUrlProviders(Publication $publication) : array
    {
        $providers = [];
        $entryUrl = new Uri(site_url('/'));
        $providers[] = new EntryCrawlUrlProvider($entryUrl);
        if ($notFoundPath = get_option('staatic_page_not_found_path')) {
            $providers[] = new PageNotFoundCrawlUrlProvider($entryUrl->withPath($notFoundPath));
        }
        if (\count($resolvedAdditionalUrls = $this->getResolvedAdditionalUrls($entryUrl)) > 0) {
            $providers[] = new AdditionalCrawlUrlProvider($resolvedAdditionalUrls);
        }
        if (\count($additionalPaths = $this->getAdditionalPaths()) > 0) {
            $excludePaths = $this->getAdditionalPathExcludes();
            foreach ($additionalPaths as $additionalPath) {
                $providers[] = new AdditionalPathCrawlUrlProvider($additionalPath, $excludePaths);
            }
        }
        $providers = apply_filters('staatic_crawl_url_providers', $providers, $publication);
        foreach ($providers as $provider) {
            if ($provider instanceof LoggerAwareInterface) {
                $provider->setLogger($this->logger);
            }
        }
        return $providers;
    }

    private function getResolvedAdditionalUrls(UriInterface $baseUrl) : array
    {
        $resolvedAdditionalUrls = [];
        foreach (\explode("\n", get_option('staatic_additional_urls')) as $additionalUrl) {
            if (!$additionalUrl || \substr($additionalUrl, 0, 1) === '#') {
                continue;
            }
            $additionalUrl = new Uri($additionalUrl);
            if (!$additionalUrl->getAuthority()) {
                $additionalUrl = UriResolver::resolve($baseUrl, $additionalUrl);
            }
            $resolvedAdditionalUrls[] = $additionalUrl;
        }
        return $resolvedAdditionalUrls;
    }

    private function getAdditionalPaths() : array
    {
        $additionalPaths = [];
        foreach (\explode("\n", get_option('staatic_additional_paths')) as $additionalPath) {
            if (!$additionalPath || \substr($additionalPath, 0, 1) === '#') {
                continue;
            }
            if (!\is_readable($additionalPath)) {
                continue;
            }
            $additionalPaths[] = $additionalPath;
        }
        return $additionalPaths;
    }

    private function getAdditionalPathExcludes() : array
    {
        $excludePaths = [get_option('staatic_work_directory')];
        $excludePaths = apply_filters('staatic_additional_paths_exclude_paths', $excludePaths);
        return $excludePaths;
    }
}
