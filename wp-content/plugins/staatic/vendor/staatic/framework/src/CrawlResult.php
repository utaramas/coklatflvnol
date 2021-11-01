<?php

namespace Staatic\Framework;

use Staatic\Vendor\GuzzleHttp\Exception\RequestException;
use Staatic\Vendor\GuzzleHttp\Exception\TransferException;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class CrawlResult
{
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var UriInterface
     */
    private $transformedUrl;
    /**
     * @var ResponseInterface|null
     */
    private $response;
    /**
     * @var UriInterface|null
     */
    private $foundOnUrl;
    /**
     * @param ResponseInterface|null $response
     * @param UriInterface|null $foundOnUrl
     */
    private function __construct(UriInterface $url, UriInterface $transformedUrl, $response, $foundOnUrl = null)
    {
        $this->url = $url;
        $this->transformedUrl = $transformedUrl;
        $this->foundOnUrl = $foundOnUrl;
        $this->response = $response;
    }
    /**
     * @param UriInterface|null $foundOnUrl
     */
    public static function fromFulfilledCrawlRequest(UriInterface $url, UriInterface $transformedUrl, ResponseInterface $response, $foundOnUrl = null) : self
    {
        return new static($url, $transformedUrl, $response, $foundOnUrl);
    }
    /**
     * @param UriInterface|null $foundOnUrl
     */
    public static function fromRejectedCrawlRequest(UriInterface $url, UriInterface $transformedUrl, TransferException $transferException, $foundOnUrl = null) : self
    {
        $response = null;
        if ($transferException instanceof RequestException) {
            $response = $transferException->getResponse();
        }
        return new static($url, $transformedUrl, $response, $foundOnUrl);
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function transformedUrl() : UriInterface
    {
        return $this->transformedUrl;
    }
    /**
     * @return ResponseInterface|null
     */
    public function response()
    {
        return $this->response;
    }
    /**
     * @return UriInterface|null
     */
    public function foundOnUrl()
    {
        return $this->foundOnUrl;
    }
}
