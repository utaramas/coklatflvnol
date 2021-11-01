<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleRetry;

use Staatic\Vendor\GuzzleHttp\Promise\Create;
use Closure;
use DateTime;
use Staatic\Vendor\GuzzleHttp\Exception\BadResponseException;
use Staatic\Vendor\GuzzleHttp\Exception\ConnectException;
use Staatic\Vendor\GuzzleHttp\Promise\Promise;
use Staatic\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Staatic\Vendor\Psr\Http\Message\RequestInterface;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use function call_user_func;
use function call_user_func_array;
use function Staatic\Vendor\GuzzleHttp\Promise\rejection_for;
use function in_array;
use function is_callable;
class GuzzleRetryMiddleware
{
    const DATE_FORMAT = 'D, d M Y H:i:s T';
    const RETRY_HEADER = 'X-Retry-Counter';
    const RETRY_AFTER_HEADER = 'Retry-After';
    private $defaultOptions = ['retry_enabled' => \true, 'default_retry_multiplier' => 1.5, 'max_retry_attempts' => 10, 'max_allowable_timeout_secs' => null, 'retry_only_if_retry_after_header' => \false, 'retry_on_status' => ['429', '503'], 'on_retry_callback' => null, 'retry_on_timeout' => \false, 'expose_retry_header' => \false, 'retry_header' => self::RETRY_HEADER, 'retry_after_header' => self::RETRY_AFTER_HEADER, 'retry_after_date_format' => self::DATE_FORMAT];
    private $nextHandler;
    /**
     * @param mixed[] $defaultOptions
     */
    public static function factory($defaultOptions = []) : Closure
    {
        return function (callable $handler) use($defaultOptions) : self {
            return new static($handler, $defaultOptions);
        };
    }
    public final function __construct(callable $nextHandler, array $defaultOptions = [])
    {
        $this->nextHandler = $nextHandler;
        $this->defaultOptions = \array_replace($this->defaultOptions, $defaultOptions);
    }
    public function __invoke(RequestInterface $request, array $options) : Promise
    {
        $options = \array_replace($this->defaultOptions, $options);
        if (!isset($options['retry_count'])) {
            $options['retry_count'] = 0;
        }
        $next = $this->nextHandler;
        return $next($request, $options)->then($this->onFulfilled($request, $options), $this->onRejected($request, $options));
    }
    /**
     * @param RequestInterface $request
     * @param mixed[] $options
     */
    protected function onFulfilled($request, $options) : callable
    {
        return function (ResponseInterface $response) use($request, $options) {
            return $this->shouldRetryHttpResponse($options, $response) ? $this->doRetry($request, $options, $response) : $this->returnResponse($options, $response);
        };
    }
    /**
     * @param RequestInterface $request
     * @param mixed[] $options
     */
    protected function onRejected($request, $options) : callable
    {
        return function ($reason) use($request, $options) : PromiseInterface {
            if ($reason instanceof BadResponseException) {
                if ($this->shouldRetryHttpResponse($options, $reason->getResponse())) {
                    return $this->doRetry($request, $options, $reason->getResponse());
                }
            } elseif ($reason instanceof ConnectException) {
                if ($this->shouldRetryConnectException($options)) {
                    return $this->doRetry($request, $options);
                }
            }
            if (\class_exists('Staatic\\Vendor\\GuzzleHttp\\Promise\\Create')) {
                return Create::rejectionFor($reason);
            } else {
                return rejection_for($reason);
            }
        };
    }
    /**
     * @param mixed[] $options
     */
    protected function shouldRetryConnectException($options) : bool
    {
        return $options['retry_enabled'] && ($options['retry_on_timeout'] ?? \false) && $this->countRemainingRetries($options) > 0;
    }
    /**
     * @param mixed[] $options
     * @param ResponseInterface|null $response
     */
    protected function shouldRetryHttpResponse($options, $response = null) : bool
    {
        $statuses = \array_map('\\intval', (array) $options['retry_on_status']);
        $hasRetryAfterHeader = $response ? $response->hasHeader('Retry-After') : \false;
        switch (\true) {
            case $options['retry_enabled'] === \false:
            case $this->countRemainingRetries($options) === 0:
            case !$hasRetryAfterHeader && $options['retry_only_if_retry_after_header']:
                return \false;
            default:
                $statusCode = $response ? $response->getStatusCode() : 0;
                return in_array($statusCode, $statuses, \true);
        }
    }
    /**
     * @param mixed[] $options
     */
    protected function countRemainingRetries($options) : int
    {
        $retryCount = isset($options['retry_count']) ? (int) $options['retry_count'] : 0;
        $numAllowed = isset($options['max_retry_attempts']) ? (int) $options['max_retry_attempts'] : $this->defaultOptions['max_retry_attempts'];
        return (int) \max([$numAllowed - $retryCount, 0]);
    }
    /**
     * @param RequestInterface $request
     * @param mixed[] $options
     * @param ResponseInterface|null $response
     */
    protected function doRetry($request, $options, $response = null) : Promise
    {
        ++$options['retry_count'];
        $delayTimeout = $this->determineDelayTimeout($options, $response);
        if ($options['on_retry_callback']) {
            call_user_func_array($options['on_retry_callback'], [(int) $options['retry_count'], $delayTimeout, &$request, &$options, $response]);
        }
        \usleep((int) ($delayTimeout * 1000000.0));
        return $this($request, $options);
    }
    /**
     * @param mixed[] $options
     * @param ResponseInterface $response
     */
    protected function returnResponse($options, $response) : ResponseInterface
    {
        if ($options['expose_retry_header'] === \false || $options['retry_count'] === 0) {
            return $response;
        }
        return $response->withHeader($options['retry_header'], $options['retry_count']);
    }
    /**
     * @param mixed[] $options
     * @param ResponseInterface|null $response
     */
    protected function determineDelayTimeout($options, $response = null) : float
    {
        if (is_callable($options['default_retry_multiplier'])) {
            $defaultDelayTimeout = (float) call_user_func($options['default_retry_multiplier'], $options['retry_count'], $response);
        } else {
            $defaultDelayTimeout = (float) $options['default_retry_multiplier'] * $options['retry_count'];
        }
        if ($response && $response->hasHeader($options['retry_after_header'])) {
            $timeout = $this->deriveTimeoutFromHeader($response->getHeader($options['retry_after_header'])[0], $options['retry_after_date_format']) ?? $defaultDelayTimeout;
        } else {
            $timeout = \abs($defaultDelayTimeout);
        }
        if (!\is_null($options['max_allowable_timeout_secs']) && \abs($options['max_allowable_timeout_secs']) > 0) {
            return \min(\abs($timeout), (float) \abs($options['max_allowable_timeout_secs']));
        } else {
            return \abs($timeout);
        }
    }
    /**
     * @param string $headerValue
     * @param string $dateFormat
     * @return float|null
     */
    protected function deriveTimeoutFromHeader($headerValue, $dateFormat = self::DATE_FORMAT)
    {
        if (\is_numeric($headerValue)) {
            return (float) \trim($headerValue);
        } elseif ($date = DateTime::createFromFormat($dateFormat ?: self::DATE_FORMAT, \trim($headerValue))) {
            return (float) $date->format('U') - \time();
        }
        return null;
    }
}
