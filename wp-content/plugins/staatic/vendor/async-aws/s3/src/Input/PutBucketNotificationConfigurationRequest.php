<?php

namespace Staatic\Vendor\AsyncAws\S3\Input;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Input;
use Staatic\Vendor\AsyncAws\Core\Request;
use Staatic\Vendor\AsyncAws\Core\Stream\StreamFactory;
use Staatic\Vendor\AsyncAws\S3\ValueObject\NotificationConfiguration;
final class PutBucketNotificationConfigurationRequest extends Input
{
    private $bucket;
    private $notificationConfiguration;
    private $expectedBucketOwner;
    public function __construct(array $input = [])
    {
        $this->bucket = $input['Bucket'] ?? null;
        $this->notificationConfiguration = isset($input['NotificationConfiguration']) ? NotificationConfiguration::create($input['NotificationConfiguration']) : null;
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
     * @return NotificationConfiguration|null
     */
    public function getNotificationConfiguration()
    {
        return $this->notificationConfiguration;
    }
    public function request() : Request
    {
        $headers = ['content-type' => 'application/xml'];
        if (null !== $this->expectedBucketOwner) {
            $headers['x-amz-expected-bucket-owner'] = $this->expectedBucketOwner;
        }
        $query = [];
        $uri = [];
        if (null === ($v = $this->bucket)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Bucket" for "%s". The value cannot be null.', __CLASS__));
        }
        $uri['Bucket'] = $v;
        $uriString = '/' . \rawurlencode($uri['Bucket']) . '?notification';
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
    public function setExpectedBucketOwner($value) : self
    {
        $this->expectedBucketOwner = $value;
        return $this;
    }
    /**
     * @param NotificationConfiguration|null $value
     */
    public function setNotificationConfiguration($value) : self
    {
        $this->notificationConfiguration = $value;
        return $this;
    }
    /**
     * @return void
     */
    private function requestBody(\DomNode $node, \DomDocument $document)
    {
        if (null === ($v = $this->notificationConfiguration)) {
            throw new InvalidArgument(\sprintf('Missing parameter "NotificationConfiguration" for "%s". The value cannot be null.', __CLASS__));
        }
        $node->appendChild($child = $document->createElement('NotificationConfiguration'));
        $child->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');
        $v->requestBody($child, $document);
    }
}
