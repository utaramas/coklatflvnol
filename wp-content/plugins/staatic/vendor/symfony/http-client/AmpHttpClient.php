<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient;

use Staatic\Vendor\Amp\CancelledException;
use Staatic\Vendor\Amp\Http\Client\DelegateHttpClient;
use Staatic\Vendor\Amp\Http\Client\InterceptedHttpClient;
use Staatic\Vendor\Amp\Http\Client\PooledHttpClient;
use Staatic\Vendor\Amp\Http\Client\Request;
use Staatic\Vendor\Amp\Http\Tunnel\Http1TunnelConnector;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Symfony\Component\HttpClient\Exception\TransportException;
use Staatic\Vendor\Symfony\Component\HttpClient\Internal\AmpClientState;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\AmpResponse;
use Staatic\Vendor\Symfony\Component\HttpClient\Response\ResponseStream;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Staatic\Vendor\Symfony\Contracts\Service\ResetInterface;
if (!\interface_exists(DelegateHttpClient::class)) {
    throw new \LogicException('You cannot use "Symfony\\Component\\HttpClient\\AmpHttpClient" as the "amphp/http-client" package is not installed. Try running "composer require amphp/http-client".');
}
final class AmpHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    use HttpClientTrait;
    use LoggerAwareTrait;
    private $defaultOptions = self::OPTIONS_DEFAULTS;
    private $multi;
    public function __construct(array $defaultOptions = [], callable $clientConfigurator = null, int $maxHostConnections = 6, int $maxPendingPushes = 50)
    {
        $callable = [__CLASS__, 'shouldBuffer'];
        $this->defaultOptions['buffer'] = $this->defaultOptions['buffer'] ?? function () use ($callable) {
            return $callable(...func_get_args());
        };
        if ($defaultOptions) {
            list(, $this->defaultOptions) = self::prepareRequest(null, null, $defaultOptions, $this->defaultOptions);
        }
        $this->multi = new AmpClientState($clientConfigurator, $maxHostConnections, $maxPendingPushes, $this->logger);
    }
    /**
     * @param string $method
     * @param string $url
     * @param mixed[] $options
     */
    public function request($method, $url, $options = []) : ResponseInterface
    {
        list($url, $options) = self::prepareRequest($method, $url, $options, $this->defaultOptions);
        $options['proxy'] = self::getProxy($options['proxy'], $url, $options['no_proxy']);
        if (null !== $options['proxy'] && !\class_exists(Http1TunnelConnector::class)) {
            throw new \LogicException('You cannot use the "proxy" option as the "amphp/http-tunnel" package is not installed. Try running "composer require amphp/http-tunnel".');
        }
        if ($options['bindto']) {
            if (0 === \strpos($options['bindto'], 'if!')) {
                throw new TransportException(__CLASS__ . ' cannot bind to network interfaces, use e.g. CurlHttpClient instead.');
            }
            if (0 === \strpos($options['bindto'], 'host!')) {
                $options['bindto'] = \substr($options['bindto'], 5);
            }
        }
        if ('' !== $options['body'] && 'POST' === $method && !isset($options['normalized_headers']['content-type'])) {
            $options['headers'][] = 'Content-Type: application/x-www-form-urlencoded';
        }
        if (!isset($options['normalized_headers']['user-agent'])) {
            $options['headers'][] = 'User-Agent: Symfony HttpClient/Amp';
        }
        if (0 < $options['max_duration']) {
            $options['timeout'] = \min($options['max_duration'], $options['timeout']);
        }
        if ($options['resolve']) {
            $this->multi->dnsCache = $options['resolve'] + $this->multi->dnsCache;
        }
        if ($options['peer_fingerprint'] && !isset($options['peer_fingerprint']['pin-sha256'])) {
            throw new TransportException(__CLASS__ . ' supports only "pin-sha256" fingerprints.');
        }
        $request = new Request(\implode('', $url), $method);
        if ($options['http_version']) {
            switch ((float) $options['http_version']) {
                case 1.0:
                    $request->setProtocolVersions(['1.0']);
                    break;
                case 1.1:
                    $request->setProtocolVersions(['1.1', '1.0']);
                    break;
                default:
                    $request->setProtocolVersions(['2', '1.1', '1.0']);
                    break;
            }
        }
        foreach ($options['headers'] as $v) {
            $h = \explode(': ', $v, 2);
            $request->addHeader($h[0], $h[1]);
        }
        $request->setTcpConnectTimeout(1000 * $options['timeout']);
        $request->setTlsHandshakeTimeout(1000 * $options['timeout']);
        $request->setTransferTimeout(1000 * $options['max_duration']);
        if (\method_exists($request, 'setInactivityTimeout')) {
            $request->setInactivityTimeout(0);
        }
        if ('' !== $request->getUri()->getUserInfo() && !$request->hasHeader('authorization')) {
            $auth = \explode(':', $request->getUri()->getUserInfo(), 2);
            $auth = \array_map('rawurldecode', $auth) + [1 => ''];
            $request->setHeader('Authorization', 'Basic ' . \base64_encode(\implode(':', $auth)));
        }
        return new AmpResponse($this->multi, $request, $options, $this->logger);
    }
    /**
     * @param float|null $timeout
     */
    public function stream($responses, $timeout = null) : ResponseStreamInterface
    {
        if ($responses instanceof AmpResponse) {
            $responses = [$responses];
        } elseif (!(is_array($responses) || $responses instanceof \Traversable)) {
            throw new \TypeError(\sprintf('"%s()" expects parameter 1 to be an iterable of AmpResponse objects, "%s" given.', __METHOD__, \get_debug_type($responses)));
        }
        return new ResponseStream(AmpResponse::stream($responses, $timeout));
    }
    public function reset()
    {
        $this->multi->dnsCache = [];
        foreach ($this->multi->pushedResponses as $authority => $pushedResponses) {
            foreach ($pushedResponses as list($pushedUrl, $pushDeferred)) {
                $pushDeferred->fail(new CancelledException());
                if ($this->logger) {
                    $this->logger->debug(\sprintf('Unused pushed response: "%s"', $pushedUrl));
                }
            }
        }
        $this->multi->pushedResponses = [];
    }
}
