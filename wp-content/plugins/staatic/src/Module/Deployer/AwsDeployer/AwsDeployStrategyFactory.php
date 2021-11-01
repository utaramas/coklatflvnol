<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\AwsDeployer;

use Staatic\Framework\DeployStrategy\AwsDeployStrategy;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
// use Staatic\Framework\DeployStrategy\PathHelper;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;

final class AwsDeployStrategyFactory
{
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    public function __construct(
        ResultRepositoryInterface $resultRepository,
        ResourceRepositoryInterface $resourceRepository,
        HttpClientInterface $httpClient
    )
    {
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->httpClient = $httpClient;
    }

    public function create() : DeployStrategyInterface
    {
        return new AwsDeployStrategy(
            $this->resultRepository,
            $this->resourceRepository,
            $this->httpClient,
            $this->createOptions()
        );
    }

    private function createOptions() : array
    {
        // $errorDocument = null;
        // if ($notFoundPath = get_option('staatic_page_not_found_path')) {
        //     $errorDocument = PathHelper::determineFilePath($notFoundPath);
        // }
        return [
            'region' => get_option('staatic_aws_region'),
            'concurrency' => get_option('staatic_http_concurrency'),
            'profile' => get_option('staatic_aws_auth_profile') ?: null,
            'accessKeyId' => get_option('staatic_aws_auth_access_key_id') ?: null,
            'secretAccessKey' => get_option('staatic_aws_auth_secret_access_key') ?: null,
            'bucket' => get_option('staatic_aws_s3_bucket'),
            'prefix' => get_option('staatic_aws_s3_prefix') ?: null,
            // 'errorDocument' => $errorDocument,
            'distributionId' => get_option('staatic_aws_cloudfront_distribution_id') ?: null,
        ];
    }
}
