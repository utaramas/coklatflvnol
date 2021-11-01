<?php

declare (strict_types=1);
namespace Staatic\Vendor\AsyncAws\Core;

use Staatic\Vendor\AsyncAws\Core\AwsError\AwsErrorFactoryInterface;
use Staatic\Vendor\AsyncAws\Core\AwsError\ChainAwsErrorFactory;
use Staatic\Vendor\AsyncAws\Core\Credentials\CacheProvider;
use Staatic\Vendor\AsyncAws\Core\Credentials\ChainProvider;
use Staatic\Vendor\AsyncAws\Core\Credentials\CredentialProvider;
use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Exception\LogicException;
use Staatic\Vendor\AsyncAws\Core\HttpClient\AwsRetryStrategy;
use Staatic\Vendor\AsyncAws\Core\Signer\Signer;
use Staatic\Vendor\AsyncAws\Core\Signer\SignerV4;
use Staatic\Vendor\AsyncAws\Core\Stream\StringStream;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Symfony\Component\HttpClient\HttpClient;
use Staatic\Vendor\Symfony\Component\HttpClient\RetryableHttpClient;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
abstract class AbstractApi
{
    private $httpClient;
    private $configuration;
    private $credentialProvider;
    private $signers;
    private $logger;
    private $awsErrorFactory;
    /**
     * @param CredentialProvider|null $credentialProvider
     * @param HttpClientInterface|null $httpClient
     * @param LoggerInterface|null $logger
     */
    public function __construct($configuration = [], $credentialProvider = null, $httpClient = null, $logger = null)
    {
        if (\is_array($configuration)) {
            $configuration = Configuration::create($configuration);
        } elseif (!$configuration instanceof Configuration) {
            throw new InvalidArgument(\sprintf('First argument to "%s::__construct()" must be an array or an instance of "%s"', static::class, Configuration::class));
        }
        $this->logger = $logger ?? new NullLogger();
        $this->awsErrorFactory = $this->getAwsErrorFactory();
        if (!isset($httpClient)) {
            $httpClient = HttpClient::create();
            if (\class_exists(RetryableHttpClient::class)) {
                $httpClient = new RetryableHttpClient($httpClient, new AwsRetryStrategy(AwsRetryStrategy::DEFAULT_RETRY_STATUS_CODES, 1000, 2.0, 0, 0.1, $this->awsErrorFactory), 3, $this->logger);
            }
        }
        $this->httpClient = $httpClient;
        $this->configuration = $configuration;
        $this->credentialProvider = $credentialProvider ?? new CacheProvider(ChainProvider::createDefaultChain($this->httpClient, $this->logger));
    }
    public final function getConfiguration() : Configuration
    {
        return $this->configuration;
    }
    /**
     * @param Input $input
     * @param \DateTimeImmutable|null $expires
     */
    public final function presign($input, $expires = null) : string
    {
        $request = $input->request();
        $request->setEndpoint($this->getEndpoint($request->getUri(), $request->getQuery(), $input->getRegion()));
        if (null !== ($credentials = $this->credentialProvider->getCredentials($this->configuration))) {
            $this->getSigner($input->getRegion())->presign($request, $credentials, new RequestContext(['expirationDate' => $expires]));
        }
        return $request->getEndpoint();
    }
    protected function getServiceCode() : string
    {
        throw new LogicException(\sprintf('The method "%s" should not be called. The Client "%s" must implement the "%s" method.', __FUNCTION__, \get_class($this), 'getEndpointMetadata'));
    }
    protected function getSignatureVersion() : string
    {
        throw new LogicException(\sprintf('The method "%s" should not be called. The Client "%s" must implement the "%s" method.', __FUNCTION__, \get_class($this), 'getEndpointMetadata'));
    }
    protected function getSignatureScopeName() : string
    {
        throw new LogicException(\sprintf('The method "%s" should not be called. The Client "%s" must implement the "%s" method.', __FUNCTION__, \get_class($this), 'getEndpointMetadata'));
    }
    /**
     * @param Request $request
     * @param RequestContext|null $context
     */
    protected final function getResponse($request, $context = null) : Response
    {
        $request->setEndpoint($this->getEndpoint($request->getUri(), $request->getQuery(), $context ? $context->getRegion() : null));
        if (null !== ($credentials = $this->credentialProvider->getCredentials($this->configuration))) {
            $this->getSigner($context ? $context->getRegion() : null)->sign($request, $credentials, $context ?? new RequestContext());
        }
        $length = $request->getBody()->length();
        if (null !== $length && !$request->hasHeader('content-length')) {
            $request->setHeader('content-length', (string) $length);
        }
        if (($requestBody = $request->getBody()) instanceof StringStream) {
            $requestBody = $requestBody->stringify();
        }
        $response = $this->httpClient->request($request->getMethod(), $request->getEndpoint(), ['headers' => $request->getHeaders(), 'body' => 0 === $length ? null : $requestBody]);
        if ($debug = \filter_var($this->configuration->get('debug'), \FILTER_VALIDATE_BOOLEAN)) {
            $this->logger->debug('AsyncAws HTTP request sent: {method} {endpoint}', ['method' => $request->getMethod(), 'endpoint' => $request->getEndpoint(), 'headers' => \json_encode($request->getHeaders()), 'body' => 0 === $length ? null : $requestBody]);
        }
        return new Response($response, $this->httpClient, $this->logger, $this->awsErrorFactory, $debug, $context ? $context->getExceptionMapping() : []);
    }
    protected function getSignerFactories() : array
    {
        return ['v4' => static function (string $service, string $region) {
            return new SignerV4($service, $region);
        }];
    }
    protected function getAwsErrorFactory() : AwsErrorFactoryInterface
    {
        return new ChainAwsErrorFactory();
    }
    /**
     * @param string|null $region
     */
    protected function getEndpointMetadata($region) : array
    {
        trigger_deprecation('async-aws/core', '1.2', 'Extending "%s"" without overriding "%s" is deprecated. This method will be abstract in version 2.0.', __CLASS__, __FUNCTION__);
        $endpoint = $this->configuration->get('endpoint');
        $region = $region ?? $this->configuration->get('region');
        return ['endpoint' => \strtr($endpoint, ['%region%' => $region, '%service%' => $this->getServiceCode()]), 'signRegion' => $region, 'signService' => $this->getSignatureScopeName(), 'signVersions' => [$this->getSignatureVersion()]];
    }
    /**
     * @param string $uri
     * @param mixed[] $query
     * @param string|null $region
     */
    protected function getEndpoint($uri, $query, $region) : string
    {
        $region = $region ?? ($this->configuration->isDefault('region') ? null : $this->configuration->get('region'));
        if (!$this->configuration->isDefault('endpoint')) {
            $endpoint = $this->configuration->get('endpoint');
        } else {
            $metadata = $this->getEndpointMetadata($region);
            $endpoint = $metadata['endpoint'];
        }
        if (\false !== \strpos($endpoint, '%region%') || \false !== \strpos($endpoint, '%service%')) {
            trigger_deprecation('async-aws/core', '1.2', 'providing an endpoint with placeholder is deprecated and will be ignored in version 2.0. Provide full endpoint instead.');
            $endpoint = \strtr($endpoint, ['%region%' => $region ?? $this->configuration->get('region'), '%service%' => $this->getServiceCode()]);
        }
        $endpoint .= $uri;
        if (empty($query)) {
            return $endpoint;
        }
        return $endpoint . (\false === \strpos($endpoint, '?') ? '?' : '&') . \http_build_query($query);
    }
    /**
     * @param string|null $region
     */
    private function getSigner($region)
    {
        $region = $region ?? ($this->configuration->isDefault('region') ? null : $this->configuration->get('region'));
        if (!isset($this->signers[$region])) {
            $factories = $this->getSignerFactories();
            $factory = null;
            if ($this->configuration->isDefault('endpoint') || $this->configuration->isDefault('region')) {
                $metadata = $this->getEndpointMetadata($region);
            } else {
                $metadata = $this->getEndpointMetadata(Configuration::DEFAULT_REGION);
                $metadata['signRegion'] = $region;
            }
            foreach ($metadata['signVersions'] as $signatureVersion) {
                if (isset($factories[$signatureVersion])) {
                    $factory = $factories[$signatureVersion];
                    break;
                }
            }
            if (null === $factory) {
                throw new InvalidArgument(\sprintf('None of the signatures "%s" is implemented.', \implode(', ', $metadata['signVersions'])));
            }
            $this->signers[$region] = $factory($metadata['signService'], $metadata['signRegion']);
        }
        return $this->signers[$region];
    }
}
