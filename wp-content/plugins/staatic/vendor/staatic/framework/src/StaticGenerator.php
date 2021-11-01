<?php

namespace Staatic\Framework;

use Staatic\Vendor\GuzzleHttp\Exception\TransferException;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\Observer\CallbackObserver;
use Staatic\Framework\BuildRepository\BuildRepositoryInterface;
use Staatic\Framework\CrawlResultHandler\CrawlResultHandler;
use Staatic\Framework\CrawlResultHandler\CrawlResultHandlerInterface;
use Staatic\Framework\PostProcessor\PostProcessorInterface;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\Transformer\TransformerInterface;
class StaticGenerator implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var CrawlerInterface
     */
    private $crawler;
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
     * @var mixed[]
     */
    private $transformers;
    /**
     * @var mixed[]
     */
    private $postProcessors;
    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(CrawlerInterface $crawler, BuildRepositoryInterface $buildRepository, ResultRepositoryInterface $resultRepository, ResourceRepositoryInterface $resourceRepository, array $transformers = [], array $postProcessors = [], $logger = null)
    {
        $this->crawler = $crawler;
        $this->buildRepository = $buildRepository;
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->transformers = $transformers;
        $this->postProcessors = $postProcessors;
        $this->logger = $logger ?: new NullLogger();
        $this->crawlResultHandler = $this->createCrawlResultHandler();
    }
    /**
     * @param Build $build
     */
    public function initializeCrawler($build) : int
    {
        $this->logger->notice('Initializing crawler', ['buildId' => $build->id()]);
        $numEnqueued = $this->crawler->initialize();
        $this->updateInitializeCrawlerStats($build, $numEnqueued);
        $this->logger->notice(\sprintf('Finished initializing crawler (%d crawl urls enqueued)', $numEnqueued), ['buildId' => $build->id()]);
        return $numEnqueued;
    }
    /**
     * @return void
     */
    private function updateInitializeCrawlerStats(Build $build, int $numCrawled)
    {
        $build->queuedUrls($numCrawled);
        $this->buildRepository->update($build);
    }
    /**
     * @param Build $build
     */
    public function crawl($build) : bool
    {
        $observer = $this->createCrawlerObserver($build);
        $this->crawler->attach($observer);
        $numCrawled = $this->crawler->crawl();
        $this->crawler->detach($observer);
        $this->updateCrawlStats($build, $numCrawled);
        return $build->isFinishedCrawling();
    }
    /**
     * @return void
     */
    private function updateCrawlStats(Build $build, int $numCrawled)
    {
        $numUrlsCrawlable = $this->crawler->numUrlsCrawlable();
        $build->crawledUrls($numUrlsCrawlable, $numCrawled);
        $this->buildRepository->update($build);
    }
    /**
     * @param Build $build
     * @return void
     */
    public function finish($build)
    {
        $this->logger->notice('Finishing build', ['buildId' => $build->id()]);
        if ($build->parentId()) {
            $this->logger->info(\sprintf('Merging build results (parent build id: #%s)', $build->parentId()), ['buildId' => $build->id()]);
            $this->resultRepository->mergeBuildResults($build->parentId(), $build->id());
        }
        $this->logger->notice(\sprintf('Finished build'), ['buildId' => $build->id()]);
    }
    private function createCrawlResultHandler() : CrawlResultHandlerInterface
    {
        return new CrawlResultHandler($this->resultRepository, $this->resourceRepository, $this->transformers);
    }
    private function createCrawlerObserver(Build $build) : \SplObserver
    {
        return new CallbackObserver(function (UriInterface $url, UriInterface $transformedUrl, ResponseInterface $response, $foundOnUrl, array $tags) use($build) {
            $this->logger->log(\in_array(CrawlerInterface::TAG_PAGE_NOT_FOUND, $tags) ? 'warning' : 'info', \sprintf('Crawl "%s" fulfilled', $url), ['buildId' => $build->id()]);
            $this->handleCrawlResult($build, CrawlResult::fromFulfilledCrawlRequest($url, $transformedUrl, $response, $foundOnUrl));
        }, function (UriInterface $url, UriInterface $transformedUrl, TransferException $transferException, $foundOnUrl, array $tags) use($build) {
            $this->logger->log(\in_array(CrawlerInterface::TAG_PAGE_NOT_FOUND, $tags) ? 'info' : 'warning', \sprintf('Crawl "%s" rejected (%s) (found on %s)', $url, $transferException->getMessage(), $foundOnUrl), ['buildId' => $build->id()]);
            $this->handleCrawlResult($build, CrawlResult::fromRejectedCrawlRequest($url, $transformedUrl, $transferException, $foundOnUrl));
        }, function () use($build) {
            if (!$build->dateCrawlStarted()) {
                $this->logger->notice(\sprintf('Crawling started for "%s"', $build->entryUrl()), ['buildId' => $build->id()]);
                $build->crawlStarted();
                $this->buildRepository->update($build);
            }
        }, function () use($build) {
            $this->logger->notice(\sprintf('Crawling finished for "%s"', $build->entryUrl()), ['buildId' => $build->id()]);
            $build->crawlFinished();
            $this->buildRepository->update($build);
        });
    }
    /**
     * @return void
     */
    private function handleCrawlResult(Build $build, CrawlResult $crawlResult)
    {
        $this->crawlResultHandler->handle($build->id(), $crawlResult);
    }
    /**
     * @param Build $build
     * @return void
     */
    public function postProcess($build)
    {
        $this->logger->notice('Starting post processing', ['buildId' => $build->id()]);
        $this->applyPostProcessors($build);
        $this->logger->notice('Finished post processing', ['buildId' => $build->id()]);
    }
    /**
     * @return void
     */
    private function applyPostProcessors(Build $build)
    {
        foreach ($this->postProcessors as $postProcessor) {
            $postProcessor->apply($build->id());
        }
    }
    public function getResultRepository() : ResultRepositoryInterface
    {
        return $this->resultRepository;
    }
    public function getResourceRepository() : ResourceRepositoryInterface
    {
        return $this->resourceRepository;
    }
}
