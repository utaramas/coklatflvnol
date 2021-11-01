<?php

namespace Staatic\Framework\CrawlResultHandler;

use Staatic\Crawler\ResponseUtil;
use Staatic\Framework\CrawlResult;
use Staatic\Framework\Resource;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\Result;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Framework\Transformer\TransformerInterface;
final class CrawlResultHandler implements CrawlResultHandlerInterface
{
    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;
    /**
     * @var ResourceRepositoryInterface
     */
    protected $resourceRepository;
    /**
     * @var mixed[]
     */
    protected $transformers;
    public function __construct(ResultRepositoryInterface $resultRepository, ResourceRepositoryInterface $resourceRepository, array $transformers)
    {
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->transformers = $transformers;
    }
    /**
     * @param string $buildId
     * @param CrawlResult $crawlResult
     * @return void
     */
    public function handle($buildId, $crawlResult)
    {
        if (!$crawlResult->response()) {
            return;
        }
        $resource = Resource::create(\sha1($crawlResult->transformedUrl()), $crawlResult->response()->getBody());
        $result = $this->createResult($buildId, $this->resultRepository->nextId(), $crawlResult, $resource);
        $this->applyTransformers($result, $resource);
        $this->resourceRepository->write($resource);
        $result->setMd5($resource->md5());
        $result->setSha1($resource->sha1());
        $result->setSize($resource->size());
        $this->resultRepository->add($result);
    }
    private function createResult(string $buildId, string $resultId, CrawlResult $crawlResult, Resource $resource) : Result
    {
        $response = $crawlResult->response();
        $mimeType = null;
        $charset = null;
        if ($response->hasHeader('Content-Type')) {
            list($mimeType, $charset) = ResponseUtil::parseContentTypeHeader($response->getHeaderLine('Content-Type'));
        }
        $redirectUrl = null;
        if (ResponseUtil::isRedirectResponse($response)) {
            $redirectUrl = ResponseUtil::getRedirectUrl($response);
        }
        return new Result($resultId, $buildId, $crawlResult->transformedUrl(), $response->getStatusCode(), $resource->id(), $resource->md5(), $resource->sha1(), $resource->size(), $mimeType, $charset, $redirectUrl, $crawlResult->url(), $crawlResult->foundOnUrl());
    }
    /**
     * @return void
     */
    private function applyTransformers(Result $result, Resource $resource)
    {
        foreach ($this->transformers as $transformer) {
            if (!$transformer->supports($result, $resource)) {
                continue;
            }
            $transformer->transform($result, $resource);
            if ($resource->content()->tell() !== 0) {
                throw new \RuntimeException(\sprintf('Resource content stream was not left in a valid state since "%s"', \get_class($transformer)));
            }
        }
    }
}
