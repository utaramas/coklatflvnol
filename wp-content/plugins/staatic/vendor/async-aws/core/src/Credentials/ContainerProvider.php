<?php

declare (strict_types=1);
namespace Staatic\Vendor\AsyncAws\Core\Credentials;

use Staatic\Vendor\AsyncAws\Core\Configuration;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Symfony\Component\HttpClient\HttpClient;
use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
final class ContainerProvider implements CredentialProvider
{
    const ENDPOINT = 'http://169.254.170.2';
    private $logger;
    private $httpClient;
    private $timeout;
    /**
     * @param HttpClientInterface|null $httpClient
     * @param LoggerInterface|null $logger
     */
    public function __construct($httpClient = null, $logger = null, float $timeout = 1.0)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->timeout = $timeout;
    }
    /**
     * @param Configuration $configuration
     * @return Credentials|null
     */
    public function getCredentials($configuration)
    {
        $relativeUri = $configuration->get(Configuration::OPTION_CONTAINER_CREDENTIALS_RELATIVE_URI);
        if (empty($relativeUri)) {
            return null;
        }
        try {
            $response = $this->httpClient->request('GET', self::ENDPOINT . $relativeUri, ['timeout' => $this->timeout]);
            $result = $response->toArray();
        } catch (DecodingExceptionInterface $e) {
            $this->logger->info('Failed to decode Credentials.', ['exception' => $e]);
            return null;
        } catch (TransportExceptionInterface $e) {
            $this->logger->info('Failed to fetch Profile from Instance Metadata.', ['exception' => $e]);
            return null;
        } catch (HttpExceptionInterface $e) {
            $this->logger->info('Failed to fetch Profile from Instance Metadata.', ['exception' => $e]);
            return null;
        }
        if (null !== ($date = $response->getHeaders(\false)['date'][0] ?? null)) {
            $date = new \DateTimeImmutable($date);
        }
        return new Credentials($result['AccessKeyId'], $result['SecretAccessKey'], $result['Token'], Credentials::adjustExpireDate(new \DateTimeImmutable($result['Expiration']), $date));
    }
}
