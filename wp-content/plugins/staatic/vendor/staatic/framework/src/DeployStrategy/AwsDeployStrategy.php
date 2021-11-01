<?php

namespace Staatic\Framework\DeployStrategy;

use Staatic\Vendor\AsyncAws\Core\Result as AwsResult;
use Staatic\Vendor\AsyncAws\CloudFront\CloudFrontClient;
use Staatic\Vendor\AsyncAws\Core\Exception\Http\HttpException;
use Staatic\Vendor\AsyncAws\S3\Result\PutObjectOutput;
use Staatic\Vendor\AsyncAws\S3\S3Client;
use Staatic\Vendor\GuzzleHttp\Psr7\StreamWrapper;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Framework\Deployment;
use Staatic\Framework\Resource;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\Result;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
final class AwsDeployStrategy implements DeployStrategyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;
    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;
    /**
     * @var S3Client
     */
    private $s3Client;
    /**
     * @var CloudFrontClient
     */
    private $cloudFrontClient;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var string
     */
    private $region;
    /**
     * @var string|null
     */
    private $profile;
    /**
     * @var string|null
     */
    private $accessKeyId;
    /**
     * @var string|null
     */
    private $secretAccessKey;
    /**
     * @var float
     */
    private $timeout = 30;
    /**
     * @var string
     */
    private $bucket;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string|null
     */
    private $distributionId;
    /**
     * @var mixed[]
     */
    private $loggerContext = [];
    public function __construct(ResultRepositoryInterface $resultRepository, ResourceRepositoryInterface $resourceRepository, HttpClientInterface $httpClient, array $options = [])
    {
        $this->logger = new NullLogger();
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        if (empty($options['region'])) {
            throw new \InvalidArgumentException('Missing required option "region"');
        }
        if (empty($options['bucket'])) {
            throw new \InvalidArgumentException('Missing required option "bucket"');
        }
        if (!empty($options['profile']) && !empty($options['accessKeyId']) && !empty($options['secretAccessKey'])) {
            throw new \InvalidArgumentException('Option "profile" cannot be used together with option "accessKeyId"');
        }
        $this->region = $options['region'];
        $this->profile = $options['profile'] ?? null;
        $this->accessKeyId = $options['accessKeyId'] ?? null;
        $this->secretAccessKey = $options['secretAccessKey'] ?? null;
        $this->bucket = $options['bucket'];
        $this->prefix = empty($options['prefix']) ? '' : \trim($options['prefix'], '/') . '/';
        $this->distributionId = $options['distributionId'] ?? null;
        $this->httpClient = $httpClient;
        $this->s3Client = $this->createS3Client();
        $this->cloudFrontClient = $this->createCloudFrontClient();
    }
    private function createS3Client() : S3Client
    {
        $arguments = ['region' => $this->region];
        $arguments = $this->applyCredentials($arguments);
        return new S3Client($arguments, null, $this->httpClient);
    }
    private function createCloudFrontClient() : CloudFrontClient
    {
        $arguments = ['region' => $this->region];
        $arguments = $this->applyCredentials($arguments);
        return new CloudFrontClient($arguments, null, $this->httpClient);
    }
    private function applyCredentials(array $arguments) : array
    {
        if ($this->accessKeyId && $this->secretAccessKey) {
            $arguments['accessKeyId'] = $this->accessKeyId;
            $arguments['accessKeySecret'] = $this->secretAccessKey;
        } elseif ($this->profile) {
            $arguments['profile'] = $this->profile;
        }
        return $arguments;
    }
    /**
     * @param Deployment $deployment
     */
    public function initiate($deployment) : array
    {
        $this->loggerContext = ['deploymentId' => $deployment->id()];
        $localFileHashes = [];
        $localFileResults = [];
        $results = $this->resultRepository->findByBuildIdPendingDeployment($deployment->buildId(), $deployment->id());
        foreach ($results as $result) {
            $key = $this->pathToKey($result->url()->getPath());
            $localFileHashes[$key] = $result->md5();
            $localFileResults[$key] = $result;
        }
        $objects = $this->s3Client->listObjectsV2(['Bucket' => $this->bucket, 'Prefix' => $this->prefix]);
        $remoteFileHashes = [];
        foreach ($objects as $object) {
            $remoteFileHashes[$object->getKey()] = \trim($object->getETag(), '"');
        }
        $diff = $this->diffDeploymentFiles($localFileHashes, $remoteFileHashes);
        foreach ($diff['keep'] as $key => $hash) {
            $result = $localFileResults[$key];
            $this->resultRepository->markDeployed($result, $deployment->id());
        }
        $this->logger->info(\sprintf('Deployment initiated (unmodified files: %d, modified files: "%s", removed files: "%s")', \count($diff['keep']), \implode('", "', \array_keys($diff['upload'])), \implode('", "', \array_keys($diff['delete']))), $this->loggerContext);
        return ['uploadFiles' => \array_keys($diff['upload']), 'deleteFiles' => \array_keys($diff['delete'])];
    }
    /**
     * @param Deployment $deployment
     * @param mixed[] $results
     * @return void
     */
    public function processResults($deployment, $results)
    {
        $this->loggerContext = ['deploymentId' => $deployment->id()];
        $this->logger->info('Deploying results', $this->loggerContext);
        $awsResults = [];
        $pendingResults = [];
        foreach ($results as $result) {
            $resource = $this->resourceRepository->find($result->resourceId());
            \assert($resource !== null);
            $awsResults[] = $this->putResultObject($result, $resource);
            $pendingResults[] = $result;
        }
        foreach (AwsResult::wait($awsResults, $this->timeout, \true) as $index => $awsResult) {
            $result = $pendingResults[$index];
            try {
                $awsResult->getETag();
                $this->logger->info(\sprintf('Deployment of result #%s was successful', $result->id()), \array_merge($this->loggerContext, ['resultId' => $result->id()]));
            } catch (HttpException $e) {
                $this->logger->error(\sprintf('Deployment of result #%s failed: %s', $result->id(), $e->getMessage()), \array_merge($this->loggerContext, ['resultId' => $result->id()]));
            }
        }
    }
    private function putResultObject(Result $result, Resource $resource) : PutObjectOutput
    {
        $resource->content()->rewind();
        $arguments = ['Bucket' => $this->bucket, 'Key' => $this->pathToKey($result->url()->getPath()), 'Body' => StreamWrapper::getResource($resource->content()), 'ContentLength' => $resource->size(), 'ContentMD5' => \base64_encode(\hex2bin($resource->md5()))];
        if ($result->mimeType()) {
            $contentType = $result->mimeType();
            if ($result->charset()) {
                $contentType = \sprintf('%s; %s', $contentType, $result->charset());
            }
            $arguments['ContentType'] = $contentType;
        }
        if ($result->redirectUrl()) {
            $arguments['WebsiteRedirectLocation'] = (string) $result->redirectUrl();
        }
        return $this->s3Client->putObject($arguments);
    }
    /**
     * @param Deployment $deployment
     * @return void
     */
    public function finish($deployment)
    {
        $this->loggerContext = ['deploymentId' => $deployment->id()];
        $this->logger->info('Finishing deployment', $this->loggerContext);
        $this->deleteStaleFiles($deployment->metadata());
        $this->invalidateCache($deployment->metadata(), $deployment->id());
    }
    /**
     * @return void
     */
    private function deleteStaleFiles(array $deploymentMetadata)
    {
        $awsResults = [];
        $pendingFiles = [];
        foreach ($deploymentMetadata['deleteFiles'] as $key) {
            $awsResults[] = $this->s3Client->deleteObject(['Bucket' => $this->bucket, 'Key' => $key]);
            $pendingFiles[] = $key;
        }
        foreach (AwsResult::wait($awsResults, $this->timeout, \true) as $index => $awsResult) {
            $key = $pendingFiles[$index];
            try {
                $awsResult->getDeleteMarker();
                $this->logger->info(\sprintf('Deletion of stale file %s was successful', $key), $this->loggerContext);
            } catch (HttpException $e) {
                $this->logger->error(\sprintf('Deletion of stale file %s failed: %s', $key, $e->getMessage()), $this->loggerContext);
            }
        }
    }
    /**
     * @return void
     */
    private function invalidateCache(array $deploymentMetadata, string $deploymentId)
    {
        if (!$this->distributionId) {
            return;
        }
        $keys = \array_merge($deploymentMetadata['uploadFiles'], $deploymentMetadata['deleteFiles']);
        $numKeys = \count($keys);
        if ($numKeys === 0) {
            $this->logger->info('No paths to be invalidated in Amazon CloudFront', $this->loggerContext);
            return;
        }
        if ($numKeys > 3000) {
            $this->logger->notice(\sprintf('Too many paths (%d) to be invalidated in Amazon CloudFront, invalidating everything', $numKeys), $this->loggerContext);
            $paths = ['/*'];
        } else {
            $this->logger->info(\sprintf('Invalidating %d paths in Amazon CloudFront', $numKeys), $this->loggerContext);
            $paths = \array_map(function ($key) {
                return $this->keyToPath($key);
            }, $keys);
        }
        try {
            $this->cloudFrontClient->createInvalidation(['DistributionId' => $this->distributionId, 'InvalidationBatch' => ['CallerReference' => \sprintf('staatic/%s', $deploymentId), 'Paths' => ['Items' => $paths, 'Quantity' => \count($paths)]]])->resolve($this->timeout);
        } catch (HttpException $e) {
            $this->logger->warning(\sprintf('Unable to invalidate CloudFront cache: %s', $e->getMessage()), $this->loggerContext);
        }
    }
    private function diffDeploymentFiles($localFiles, $remoteFiles) : array
    {
        return ['keep' => \array_intersect_assoc($localFiles, $remoteFiles), 'upload' => \array_diff_assoc($localFiles, $remoteFiles), 'delete' => \array_diff_key($remoteFiles, $localFiles)];
    }
    private function pathToKey(string $path) : string
    {
        if (\substr($path, -1, 1) === '/') {
            $path .= 'index.html';
        }
        return $this->prefix . \ltrim($path, '/');
    }
    private function keyToPath(string $key) : string
    {
        $path = '/' . \substr($key, \strlen($this->prefix));
        if ($path !== '/index.html' && \substr($path, -11) === '/index.html') {
            $path = \substr($path, 0, -10);
        }
        return $path;
    }
}
