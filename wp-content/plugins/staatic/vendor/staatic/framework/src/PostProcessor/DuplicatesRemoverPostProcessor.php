<?php

namespace Staatic\Framework\PostProcessor;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
final class DuplicatesRemoverPostProcessor implements PostProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;
    public function __construct(ResultRepositoryInterface $resultRepository)
    {
        $this->logger = new NullLogger();
        $this->resultRepository = $resultRepository;
    }
    public function createsOrRemovesResults() : bool
    {
        return \true;
    }
    /**
     * @param string $buildId
     * @return void
     */
    public function apply($buildId)
    {
        $this->logger->info(\sprintf('Applying duplicates remover post processor'), ['buildId' => $buildId]);
        $numDeleted = 0;
        foreach ($this->resultRepository->findByBuildIdWithRedirectUrl($buildId) as $result) {
            if ($result->url()->getAuthority() !== $result->redirectUrl()->getAuthority()) {
                continue;
            }
            $path = $result->url()->getPath();
            $comparePath = \substr($path, -1, 1) === '/' ? \substr($path, 0, -1) : \sprintf('%s/', $path);
            if ($result->redirectUrl()->getPath() !== $comparePath) {
                continue;
            }
            $this->logger->debug(\sprintf('Deleting unprocessable result with url "%s" (redirects to "%s")', $result->url(), $result->redirectUrl()), ['buildId' => $buildId, 'resultId' => $result->id()]);
            $this->resultRepository->delete($result);
            $numDeleted++;
        }
        $this->logger->info(\sprintf('Removed %d duplicates', $numDeleted), ['buildId' => $buildId]);
    }
}
