<?php

namespace Staatic\Vendor\AsyncAws\S3\Input;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Input;
use Staatic\Vendor\AsyncAws\Core\Request;
use Staatic\Vendor\AsyncAws\Core\Stream\StreamFactory;
use Staatic\Vendor\AsyncAws\S3\Enum\RequestPayer;
final class GetObjectRequest extends Input
{
    private $bucket;
    private $ifMatch;
    private $ifModifiedSince;
    private $ifNoneMatch;
    private $ifUnmodifiedSince;
    private $key;
    private $range;
    private $responseCacheControl;
    private $responseContentDisposition;
    private $responseContentEncoding;
    private $responseContentLanguage;
    private $responseContentType;
    private $responseExpires;
    private $versionId;
    private $sseCustomerAlgorithm;
    private $sseCustomerKey;
    private $sseCustomerKeyMd5;
    private $requestPayer;
    private $partNumber;
    private $expectedBucketOwner;
    public function __construct(array $input = [])
    {
        $this->bucket = $input['Bucket'] ?? null;
        $this->ifMatch = $input['IfMatch'] ?? null;
        $this->ifModifiedSince = !isset($input['IfModifiedSince']) ? null : ($input['IfModifiedSince'] instanceof \DateTimeImmutable ? $input['IfModifiedSince'] : new \DateTimeImmutable($input['IfModifiedSince']));
        $this->ifNoneMatch = $input['IfNoneMatch'] ?? null;
        $this->ifUnmodifiedSince = !isset($input['IfUnmodifiedSince']) ? null : ($input['IfUnmodifiedSince'] instanceof \DateTimeImmutable ? $input['IfUnmodifiedSince'] : new \DateTimeImmutable($input['IfUnmodifiedSince']));
        $this->key = $input['Key'] ?? null;
        $this->range = $input['Range'] ?? null;
        $this->responseCacheControl = $input['ResponseCacheControl'] ?? null;
        $this->responseContentDisposition = $input['ResponseContentDisposition'] ?? null;
        $this->responseContentEncoding = $input['ResponseContentEncoding'] ?? null;
        $this->responseContentLanguage = $input['ResponseContentLanguage'] ?? null;
        $this->responseContentType = $input['ResponseContentType'] ?? null;
        $this->responseExpires = !isset($input['ResponseExpires']) ? null : ($input['ResponseExpires'] instanceof \DateTimeImmutable ? $input['ResponseExpires'] : new \DateTimeImmutable($input['ResponseExpires']));
        $this->versionId = $input['VersionId'] ?? null;
        $this->sseCustomerAlgorithm = $input['SSECustomerAlgorithm'] ?? null;
        $this->sseCustomerKey = $input['SSECustomerKey'] ?? null;
        $this->sseCustomerKeyMd5 = $input['SSECustomerKeyMD5'] ?? null;
        $this->requestPayer = $input['RequestPayer'] ?? null;
        $this->partNumber = $input['PartNumber'] ?? null;
        $this->expectedBucketOwner = $input['ExpectedBucketOwner'] ?? null;
        parent::__construct($input);
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    /**
     * @return string|null
     */
    public function getBucket()
    {
        return $this->bucket;
    }
    /**
     * @return string|null
     */
    public function getExpectedBucketOwner()
    {
        return $this->expectedBucketOwner;
    }
    /**
     * @return string|null
     */
    public function getIfMatch()
    {
        return $this->ifMatch;
    }
    /**
     * @return \DateTimeImmutable|null
     */
    public function getIfModifiedSince()
    {
        return $this->ifModifiedSince;
    }
    /**
     * @return string|null
     */
    public function getIfNoneMatch()
    {
        return $this->ifNoneMatch;
    }
    /**
     * @return \DateTimeImmutable|null
     */
    public function getIfUnmodifiedSince()
    {
        return $this->ifUnmodifiedSince;
    }
    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }
    /**
     * @return int|null
     */
    public function getPartNumber()
    {
        return $this->partNumber;
    }
    /**
     * @return string|null
     */
    public function getRange()
    {
        return $this->range;
    }
    /**
     * @return string|null
     */
    public function getRequestPayer()
    {
        return $this->requestPayer;
    }
    /**
     * @return string|null
     */
    public function getResponseCacheControl()
    {
        return $this->responseCacheControl;
    }
    /**
     * @return string|null
     */
    public function getResponseContentDisposition()
    {
        return $this->responseContentDisposition;
    }
    /**
     * @return string|null
     */
    public function getResponseContentEncoding()
    {
        return $this->responseContentEncoding;
    }
    /**
     * @return string|null
     */
    public function getResponseContentLanguage()
    {
        return $this->responseContentLanguage;
    }
    /**
     * @return string|null
     */
    public function getResponseContentType()
    {
        return $this->responseContentType;
    }
    /**
     * @return \DateTimeImmutable|null
     */
    public function getResponseExpires()
    {
        return $this->responseExpires;
    }
    /**
     * @return string|null
     */
    public function getSseCustomerAlgorithm()
    {
        return $this->sseCustomerAlgorithm;
    }
    /**
     * @return string|null
     */
    public function getSseCustomerKey()
    {
        return $this->sseCustomerKey;
    }
    /**
     * @return string|null
     */
    public function getSseCustomerKeyMd5()
    {
        return $this->sseCustomerKeyMd5;
    }
    /**
     * @return string|null
     */
    public function getVersionId()
    {
        return $this->versionId;
    }
    public function request() : Request
    {
        $headers = ['content-type' => 'application/xml'];
        if (null !== $this->ifMatch) {
            $headers['If-Match'] = $this->ifMatch;
        }
        if (null !== $this->ifModifiedSince) {
            $headers['If-Modified-Since'] = $this->ifModifiedSince->format(\DateTimeInterface::RFC822);
        }
        if (null !== $this->ifNoneMatch) {
            $headers['If-None-Match'] = $this->ifNoneMatch;
        }
        if (null !== $this->ifUnmodifiedSince) {
            $headers['If-Unmodified-Since'] = $this->ifUnmodifiedSince->format(\DateTimeInterface::RFC822);
        }
        if (null !== $this->range) {
            $headers['Range'] = $this->range;
        }
        if (null !== $this->sseCustomerAlgorithm) {
            $headers['x-amz-server-side-encryption-customer-algorithm'] = $this->sseCustomerAlgorithm;
        }
        if (null !== $this->sseCustomerKey) {
            $headers['x-amz-server-side-encryption-customer-key'] = $this->sseCustomerKey;
        }
        if (null !== $this->sseCustomerKeyMd5) {
            $headers['x-amz-server-side-encryption-customer-key-MD5'] = $this->sseCustomerKeyMd5;
        }
        if (null !== $this->requestPayer) {
            if (!RequestPayer::exists($this->requestPayer)) {
                throw new InvalidArgument(\sprintf('Invalid parameter "RequestPayer" for "%s". The value "%s" is not a valid "RequestPayer".', __CLASS__, $this->requestPayer));
            }
            $headers['x-amz-request-payer'] = $this->requestPayer;
        }
        if (null !== $this->expectedBucketOwner) {
            $headers['x-amz-expected-bucket-owner'] = $this->expectedBucketOwner;
        }
        $query = [];
        if (null !== $this->responseCacheControl) {
            $query['response-cache-control'] = $this->responseCacheControl;
        }
        if (null !== $this->responseContentDisposition) {
            $query['response-content-disposition'] = $this->responseContentDisposition;
        }
        if (null !== $this->responseContentEncoding) {
            $query['response-content-encoding'] = $this->responseContentEncoding;
        }
        if (null !== $this->responseContentLanguage) {
            $query['response-content-language'] = $this->responseContentLanguage;
        }
        if (null !== $this->responseContentType) {
            $query['response-content-type'] = $this->responseContentType;
        }
        if (null !== $this->responseExpires) {
            $query['response-expires'] = $this->responseExpires->format(\DateTimeInterface::RFC822);
        }
        if (null !== $this->versionId) {
            $query['versionId'] = $this->versionId;
        }
        if (null !== $this->partNumber) {
            $query['partNumber'] = (string) $this->partNumber;
        }
        $uri = [];
        if (null === ($v = $this->bucket)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Bucket" for "%s". The value cannot be null.', __CLASS__));
        }
        $uri['Bucket'] = $v;
        if (null === ($v = $this->key)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Key" for "%s". The value cannot be null.', __CLASS__));
        }
        $uri['Key'] = $v;
        $uriString = '/' . \rawurlencode($uri['Bucket']) . '/' . \str_replace('%2F', '/', \rawurlencode($uri['Key']));
        $body = '';
        return new Request('GET', $uriString, $query, $headers, StreamFactory::create($body));
    }
    /**
     * @param string|null $value
     */
    public function setBucket($value) : self
    {
        $this->bucket = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setExpectedBucketOwner($value) : self
    {
        $this->expectedBucketOwner = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setIfMatch($value) : self
    {
        $this->ifMatch = $value;
        return $this;
    }
    /**
     * @param \DateTimeImmutable|null $value
     */
    public function setIfModifiedSince($value) : self
    {
        $this->ifModifiedSince = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setIfNoneMatch($value) : self
    {
        $this->ifNoneMatch = $value;
        return $this;
    }
    /**
     * @param \DateTimeImmutable|null $value
     */
    public function setIfUnmodifiedSince($value) : self
    {
        $this->ifUnmodifiedSince = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setKey($value) : self
    {
        $this->key = $value;
        return $this;
    }
    /**
     * @param int|null $value
     */
    public function setPartNumber($value) : self
    {
        $this->partNumber = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setRange($value) : self
    {
        $this->range = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setRequestPayer($value) : self
    {
        $this->requestPayer = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setResponseCacheControl($value) : self
    {
        $this->responseCacheControl = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setResponseContentDisposition($value) : self
    {
        $this->responseContentDisposition = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setResponseContentEncoding($value) : self
    {
        $this->responseContentEncoding = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setResponseContentLanguage($value) : self
    {
        $this->responseContentLanguage = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setResponseContentType($value) : self
    {
        $this->responseContentType = $value;
        return $this;
    }
    /**
     * @param \DateTimeImmutable|null $value
     */
    public function setResponseExpires($value) : self
    {
        $this->responseExpires = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setSseCustomerAlgorithm($value) : self
    {
        $this->sseCustomerAlgorithm = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setSseCustomerKey($value) : self
    {
        $this->sseCustomerKey = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setSseCustomerKeyMd5($value) : self
    {
        $this->sseCustomerKeyMd5 = $value;
        return $this;
    }
    /**
     * @param string|null $value
     */
    public function setVersionId($value) : self
    {
        $this->versionId = $value;
        return $this;
    }
}
