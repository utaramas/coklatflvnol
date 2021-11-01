<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\NetlifyDeployer;

use Staatic\Vendor\GuzzleHttp\ClientInterface;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\Framework\DeployStrategy\NetlifyDeployStrategy;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;

final class NetlifyDeployStrategyFactory
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
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct(
        ResultRepositoryInterface $resultRepository,
        ResourceRepositoryInterface $resourceRepository,
        ClientInterface $httpClient
    )
    {
        $this->resultRepository = $resultRepository;
        $this->resourceRepository = $resourceRepository;
        $this->httpClient = $httpClient;
    }

    public function create() : DeployStrategyInterface
    {
        return new NetlifyDeployStrategy(
            $this->resultRepository,
            $this->resourceRepository,
            $this->httpClient,
            $this->createOptions()
        );
    }

    private function createOptions() : array
    {
        return [
            'accessToken' => get_option('staatic_netlify_access_token'),
            'siteId' => get_option('staatic_netlify_site_id'),
            'concurrency' => get_option('staatic_http_concurrency')
        ];
    }
}
