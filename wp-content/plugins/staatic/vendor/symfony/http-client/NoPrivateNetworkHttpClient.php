<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\HttpClient\Exception\TransportException;
use Staatic\Vendor\Symfony\Component\HttpFoundation\IpUtils;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
final class NoPrivateNetworkHttpClient implements HttpClientInterface, LoggerAwareInterface
{
    use HttpClientTrait;
    const PRIVATE_SUBNETS = ['127.0.0.0/8', '10.0.0.0/8', '192.168.0.0/16', '172.16.0.0/12', '169.254.0.0/16', '0.0.0.0/8', '240.0.0.0/4', '::1/128', 'fc00::/7', 'fe80::/10', '::ffff:0:0/96', '::/128'];
    private $client;
    private $subnets;
    public function __construct(HttpClientInterface $client, $subnets = null)
    {
        if (!(\is_array($subnets) || \is_string($subnets) || null === $subnets)) {
            throw new \TypeError(\sprintf('Argument 2 passed to "%s()" must be of the type array, string or null. "%s" given.', __METHOD__, \get_debug_type($subnets)));
        }
        if (!\class_exists(IpUtils::class)) {
            throw new \LogicException(\sprintf('You can not use "%s" if the HttpFoundation component is not installed. Try running "composer require symfony/http-foundation".', __CLASS__));
        }
        $this->client = $client;
        $this->subnets = $subnets;
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public function request($method, $url, $options = []) : ResponseInterface
    {
        $onProgress = $options['on_progress'] ?? null;
        if (null !== $onProgress && !\is_callable($onProgress)) {
            throw new InvalidArgumentException(\sprintf('Option "on_progress" must be callable, "%s" given.', \get_debug_type($onProgress)));
        }
        $subnets = $this->subnets;
        $lastPrimaryIp = '';
        $options['on_progress'] = function (int $dlNow, int $dlSize, array $info) use($onProgress, $subnets, &$lastPrimaryIp) {
            if ($info['primary_ip'] !== $lastPrimaryIp) {
                if (IpUtils::checkIp($info['primary_ip'], $subnets ?? self::PRIVATE_SUBNETS)) {
                    throw new TransportException(\sprintf('IP "%s" is blocked for "%s".', $info['primary_ip'], $info['url']));
                }
                $lastPrimaryIp = $info['primary_ip'];
            }
            null !== $onProgress && $onProgress($dlNow, $dlSize, $info);
        };
        return $this->client->request($method, $url, $options);
    }
    /**
     * @param float|null $timeout
     */
    public function stream($responses, $timeout = null) : ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }
    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger($logger)
    {
        if ($this->client instanceof LoggerAwareInterface) {
            $this->client->setLogger($logger);
        }
    }
}
