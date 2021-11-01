<?php

namespace Staatic\Framework;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class Result
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $buildId;
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var string
     */
    private $resourceId;
    /**
     * @var string|null
     */
    private $md5;
    /**
     * @var string|null
     */
    private $sha1;
    /**
     * @var int|null
     */
    private $size;
    /**
     * @var string|null
     */
    private $mimeType;
    /**
     * @var string|null
     */
    private $charset;
    /**
     * @var UriInterface|null
     */
    private $redirectUrl;
    /**
     * @var UriInterface|null
     */
    private $originalUrl;
    /**
     * @var UriInterface|null
     */
    private $originalFoundOnUrl;
    /**
     * @var \DateTimeInterface
     */
    private $dateCreated;
    /**
     * @param string|null $md5
     * @param string|null $sha1
     * @param int|null $size
     * @param string|null $mimeType
     * @param string|null $charset
     * @param UriInterface|null $redirectUrl
     * @param UriInterface|null $originalUrl
     * @param UriInterface|null $originalFoundOnUrl
     * @param \DateTimeInterface|null $dateCreated
     */
    public function __construct(string $id, string $buildId, UriInterface $url, int $statusCode, string $resourceId, $md5 = null, $sha1 = null, $size = null, $mimeType = null, $charset = null, $redirectUrl = null, $originalUrl = null, $originalFoundOnUrl = null, $dateCreated = null)
    {
        $this->validateUrl($url);
        $this->id = $id;
        $this->buildId = $buildId;
        $this->url = $url;
        $this->statusCode = $statusCode;
        $this->resourceId = $resourceId;
        $this->md5 = $md5;
        $this->sha1 = $sha1;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->charset = $charset;
        $this->redirectUrl = $redirectUrl;
        $this->originalUrl = $originalUrl;
        $this->originalFoundOnUrl = $originalFoundOnUrl;
        $this->dateCreated = $dateCreated ?: new \DateTimeImmutable();
    }
    /**
     * @return void
     */
    private function validateUrl(UriInterface $url)
    {
        $path = $url->getPath();
        if (\substr($path, 0, 1) !== '/') {
            throw new \InvalidArgumentException(\sprintf('Result URL should be an absolute URL, got: %s', (string) $url));
        }
    }
    /**
     * @param string $id
     * @param string $buildId
     * @param UriInterface $url
     * @param Resource $resource
     * @param mixed[] $properties
     */
    public static function create($id, $buildId, $url, $resource, $properties = [])
    {
        return new self($id, $buildId, $url, $properties['statusCode'] ?? 200, $resource->id(), $resource->md5(), $resource->sha1(), $resource->size(), $properties['mimeType'] ?? 'text/html', $properties['charset'] ?? null, $properties['redirectUrl'] ?? null, $properties['originalUrl'] ?? null, $properties['originalFoundOnUrl'] ?? null, $properties['dateCreated'] ?? null);
    }
    /**
     * @param \Staatic\Framework\Result $originalResult
     * @param string $id
     * @param string $buildId
     */
    public static function createFromResult($originalResult, $id, $buildId) : self
    {
        $result = clone $originalResult;
        $result->id = $id;
        $result->buildId = $buildId;
        return $result;
    }
    public function __toString()
    {
        return \implode(' ~ ', [$this->url, $this->statusCode, $this->mimeType]);
    }
    public function id() : string
    {
        return $this->id;
    }
    public function buildId() : string
    {
        return $this->buildId;
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function statusCode() : int
    {
        return $this->statusCode;
    }
    public function statusCodeCategory() : int
    {
        return (int) \floor($this->statusCode / 100);
    }
    public function resourceId() : string
    {
        return $this->resourceId;
    }
    /**
     * @return string|null
     */
    public function md5()
    {
        return $this->md5;
    }
    /**
     * @param string|null $md5
     * @return void
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;
    }
    /**
     * @return string|null
     */
    public function sha1()
    {
        return $this->sha1;
    }
    /**
     * @param string|null $sha1
     * @return void
     */
    public function setSha1($sha1)
    {
        $this->sha1 = $sha1;
    }
    /**
     * @return int|null
     */
    public function size()
    {
        return $this->size;
    }
    /**
     * @param int|null $size
     * @return void
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
    /**
     * @return string|null
     */
    public function mimeType()
    {
        return $this->mimeType;
    }
    /**
     * @param string|null $mimeType
     * @return void
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }
    /**
     * @return string|null
     */
    public function charset()
    {
        return $this->charset;
    }
    /**
     * @param string|null $charset
     * @return void
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }
    /**
     * @return UriInterface|null
     */
    public function redirectUrl()
    {
        return $this->redirectUrl;
    }
    /**
     * @param UriInterface|null $redirectUrl
     * @return void
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }
    /**
     * @return UriInterface|null
     */
    public function originalUrl()
    {
        return $this->originalUrl;
    }
    /**
     * @return UriInterface|null
     */
    public function originalFoundOnUrl()
    {
        return $this->originalFoundOnUrl;
    }
    public function dateCreated() : \DateTimeInterface
    {
        return $this->dateCreated;
    }
}
