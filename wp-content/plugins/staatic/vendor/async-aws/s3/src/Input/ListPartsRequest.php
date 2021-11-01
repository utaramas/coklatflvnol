<?php

namespace Staatic\Vendor\AsyncAws\S3\Input;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Input;
use Staatic\Vendor\AsyncAws\Core\Request;
use Staatic\Vendor\AsyncAws\Core\Stream\StreamFactory;
use Staatic\Vendor\AsyncAws\S3\Enum\RequestPayer;
final class ListPartsRequest extends Input
{
    private $bucket;
    private $key;
    private $maxParts;
    private $partNumberMarker;
    private $uploadId;
    private $requestPayer;
    private $expectedBucketOwner;
    public function __construct(array $input = [])
    {
        $this->bucket = $input['Bucket'] ?? null;
        $this->key = $input['Key'] ?? null;
        $this->maxParts = $input['MaxParts'] ?? null;
        $this->partNumberMarker = $input['PartNumberMarker'] ?? null;
        $this->uploadId = $input['UploadId'] ?? null;
        $this->requestPayer = $input['RequestPayer'] ?? null;
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
    public function getKey()
    {
        return $this->key;
    }
    /**
     * @return int|null
     */
    public function getMaxParts()
    {
        return $this->maxParts;
    }
    /**
     * @return int|null
     */
    public function getPartNumberMarker()
    {
        return $this->partNumberMarker;
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
    public function getUploadId()
    {
        return $this->uploadId;
    }
    public function request() : Request
    {
        $headers = ['content-type' => 'application/xml'];
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
        if (null !== $this->maxParts) {
            $query['max-parts'] = (string) $this->maxParts;
        }
        if (null !== $this->partNumberMarker) {
            $query['part-number-marker'] = (string) $this->partNumberMarker;
        }
        if (null === ($v = $this->uploadId)) {
            throw new InvalidArgument(\sprintf('Missing parameter "UploadId" for "%s". The value cannot be null.', __CLASS__));
        }
        $query['uploadId'] = $v;
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
    public function setKey($value) : self
    {
        $this->key = $value;
        return $this;
    }
    /**
     * @param int|null $value
     */
    public function setMaxParts($value) : self
    {
        $this->maxParts = $value;
        return $this;
    }
    /**
     * @param int|null $value
     */
    public function setPartNumberMarker($value) : self
    {
        $this->partNumberMarker = $value;
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
    public function setUploadId($value) : self
    {
        $this->uploadId = $value;
        return $this;
    }
}
