<?php

namespace Staatic\Vendor\AsyncAws\S3\Input;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Input;
use Staatic\Vendor\AsyncAws\Core\Request;
use Staatic\Vendor\AsyncAws\Core\Stream\StreamFactory;
use Staatic\Vendor\AsyncAws\S3\ValueObject\CORSConfiguration;
final class PutBucketCorsRequest extends Input
{
    private $bucket;
    private $corsConfiguration;
    private $contentMd5;
    private $expectedBucketOwner;
    public function __construct(array $input = [])
    {
        $this->bucket = $input['Bucket'] ?? null;
        $this->corsConfiguration = isset($input['CORSConfiguration']) ? CORSConfiguration::create($input['CORSConfiguration']) : null;
        $this->contentMd5 = $input['ContentMD5'] ?? null;
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
    public function getContentMd5()
    {
        return $this->contentMd5;
    }
    /**
     * @return CORSConfiguration|null
     */
    public function getCorsConfiguration()
    {
        return $this->corsConfiguration;
    }
    /**
     * @return string|null
     */
    public function getExpectedBucketOwner()
    {
        return $this->expectedBucketOwner;
    }
    public function request() : Request
    {
        $headers = ['content-type' => 'application/xml'];
        if (null !== $this->contentMd5) {
            $headers['Content-MD5'] = $this->contentMd5;
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
        $uriString = '/' . \rawurlencode($uri['Bucket']) . '?cors';
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = \false;
        $this->requestBody($document, $document);
        $body = $document->hasChildNodes() ? $document->saveXML() : '';
        return new Request('PUT', $uriString, $query, $headers, StreamFactory::create($body));
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
    public function setContentMd5($value) : self
    {
        $this->contentMd5 = $value;
        return $this;
    }
    /**
     * @param CORSConfiguration|null $value
     */
    public function setCorsConfiguration($value) : self
    {
        $this->corsConfiguration = $value;
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
     * @return void
     */
    private function requestBody(\DomNode $node, \DomDocument $document)
    {
        if (null === ($v = $this->corsConfiguration)) {
            throw new InvalidArgument(\sprintf('Missing parameter "CORSConfiguration" for "%s". The value cannot be null.', __CLASS__));
        }
        $node->appendChild($child = $document->createElement('CORSConfiguration'));
        $child->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');
        $v->requestBody($child, $document);
    }
}
