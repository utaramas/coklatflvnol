<?php

namespace Staatic\Vendor\AsyncAws\S3\Input;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Input;
use Staatic\Vendor\AsyncAws\Core\Request;
use Staatic\Vendor\AsyncAws\Core\Stream\StreamFactory;
use Staatic\Vendor\AsyncAws\S3\Enum\RequestPayer;
use Staatic\Vendor\AsyncAws\S3\ValueObject\Delete;
final class DeleteObjectsRequest extends Input
{
    private $bucket;
    private $delete;
    private $mfa;
    private $requestPayer;
    private $bypassGovernanceRetention;
    private $expectedBucketOwner;
    public function __construct(array $input = [])
    {
        $this->bucket = $input['Bucket'] ?? null;
        $this->delete = isset($input['Delete']) ? Delete::create($input['Delete']) : null;
        $this->mfa = $input['MFA'] ?? null;
        $this->requestPayer = $input['RequestPayer'] ?? null;
        $this->bypassGovernanceRetention = $input['BypassGovernanceRetention'] ?? null;
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
     * @return bool|null
     */
    public function getBypassGovernanceRetention()
    {
        return $this->bypassGovernanceRetention;
    }
    /**
     * @return Delete|null
     */
    public function getDelete()
    {
        return $this->delete;
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
    public function getMfa()
    {
        return $this->mfa;
    }
    /**
     * @return string|null
     */
    public function getRequestPayer()
    {
        return $this->requestPayer;
    }
    public function request() : Request
    {
        $headers = ['content-type' => 'application/xml'];
        if (null !== $this->mfa) {
            $headers['x-amz-mfa'] = $this->mfa;
        }
        if (null !== $this->requestPayer) {
            if (!RequestPayer::exists($this->requestPayer)) {
                throw new InvalidArgument(\sprintf('Invalid parameter "RequestPayer" for "%s". The value "%s" is not a valid "RequestPayer".', __CLASS__, $this->requestPayer));
            }
            $headers['x-amz-request-payer'] = $this->requestPayer;
        }
        if (null !== $this->bypassGovernanceRetention) {
            $headers['x-amz-bypass-governance-retention'] = $this->bypassGovernanceRetention ? 'true' : 'false';
        }
        if (null !== $this->expectedBucketOwner) {
            $headers['x-amz-expected-bucket-owner'] = $this->expectedBucketOwner;
        }
        $query = [];
        $uri = [];
        if (null === ($v = $this->bucket)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Bucket" for "%s". The value cannot be null.', __CLASS__));
        }
        $uri['Bucket'] = $v;
        $uriString = '/' . \rawurlencode($uri['Bucket']) . '?delete';
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = \false;
        $this->requestBody($document, $document);
        $body = $document->hasChildNodes() ? $document->saveXML() : '';
        return new Request('POST', $uriString, $query, $headers, StreamFactory::create($body));
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
     * @param bool|null $value
     */
    public function setBypassGovernanceRetention($value) : self
    {
        $this->bypassGovernanceRetention = $value;
        return $this;
    }
    /**
     * @param Delete|null $value
     */
    public function setDelete($value) : self
    {
        $this->delete = $value;
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
    public function setMfa($value) : self
    {
        $this->mfa = $value;
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
     * @return void
     */
    private function requestBody(\DomNode $node, \DomDocument $document)
    {
        if (null === ($v = $this->delete)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Delete" for "%s". The value cannot be null.', __CLASS__));
        }
        $node->appendChild($child = $document->createElement('Delete'));
        $child->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');
        $v->requestBody($child, $document);
    }
}
