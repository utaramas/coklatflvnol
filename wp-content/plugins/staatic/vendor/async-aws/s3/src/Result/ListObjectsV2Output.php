<?php

namespace Staatic\Vendor\AsyncAws\S3\Result;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Response;
use Staatic\Vendor\AsyncAws\Core\Result;
use Staatic\Vendor\AsyncAws\S3\Enum\EncodingType;
use Staatic\Vendor\AsyncAws\S3\Input\ListObjectsV2Request;
use Staatic\Vendor\AsyncAws\S3\S3Client;
use Staatic\Vendor\AsyncAws\S3\ValueObject\AwsObject;
use Staatic\Vendor\AsyncAws\S3\ValueObject\CommonPrefix;
use Staatic\Vendor\AsyncAws\S3\ValueObject\Owner;
class ListObjectsV2Output extends Result implements \IteratorAggregate
{
    private $isTruncated;
    private $contents = [];
    private $name;
    private $prefix;
    private $delimiter;
    private $maxKeys;
    private $commonPrefixes = [];
    private $encodingType;
    private $keyCount;
    private $continuationToken;
    private $nextContinuationToken;
    private $startAfter;
    /**
     * @param bool $currentPageOnly
     * @return mixed[]
     */
    public function getCommonPrefixes($currentPageOnly = \false)
    {
        if ($currentPageOnly) {
            $this->initialize();
            yield from $this->commonPrefixes;
            return;
        }
        $client = $this->awsClient;
        if (!$client instanceof S3Client) {
            throw new InvalidArgument('missing client injected in paginated result');
        }
        if (!$this->input instanceof ListObjectsV2Request) {
            throw new InvalidArgument('missing last request injected in paginated result');
        }
        $input = clone $this->input;
        $page = $this;
        while (\true) {
            if ($page->getNextContinuationToken()) {
                $input->setContinuationToken($page->getNextContinuationToken());
                $this->registerPrefetch($nextPage = $client->listObjectsV2($input));
            } else {
                $nextPage = null;
            }
            yield from $page->getCommonPrefixes(\true);
            if (null === $nextPage) {
                break;
            }
            $this->unregisterPrefetch($nextPage);
            $page = $nextPage;
        }
    }
    /**
     * @param bool $currentPageOnly
     * @return mixed[]
     */
    public function getContents($currentPageOnly = \false)
    {
        if ($currentPageOnly) {
            $this->initialize();
            yield from $this->contents;
            return;
        }
        $client = $this->awsClient;
        if (!$client instanceof S3Client) {
            throw new InvalidArgument('missing client injected in paginated result');
        }
        if (!$this->input instanceof ListObjectsV2Request) {
            throw new InvalidArgument('missing last request injected in paginated result');
        }
        $input = clone $this->input;
        $page = $this;
        while (\true) {
            if ($page->getNextContinuationToken()) {
                $input->setContinuationToken($page->getNextContinuationToken());
                $this->registerPrefetch($nextPage = $client->listObjectsV2($input));
            } else {
                $nextPage = null;
            }
            yield from $page->getContents(\true);
            if (null === $nextPage) {
                break;
            }
            $this->unregisterPrefetch($nextPage);
            $page = $nextPage;
        }
    }
    /**
     * @return string|null
     */
    public function getContinuationToken()
    {
        $this->initialize();
        return $this->continuationToken;
    }
    /**
     * @return string|null
     */
    public function getDelimiter()
    {
        $this->initialize();
        return $this->delimiter;
    }
    /**
     * @return string|null
     */
    public function getEncodingType()
    {
        $this->initialize();
        return $this->encodingType;
    }
    /**
     * @return bool|null
     */
    public function getIsTruncated()
    {
        $this->initialize();
        return $this->isTruncated;
    }
    public function getIterator() : \Traversable
    {
        $client = $this->awsClient;
        if (!$client instanceof S3Client) {
            throw new InvalidArgument('missing client injected in paginated result');
        }
        if (!$this->input instanceof ListObjectsV2Request) {
            throw new InvalidArgument('missing last request injected in paginated result');
        }
        $input = clone $this->input;
        $page = $this;
        while (\true) {
            if ($page->getNextContinuationToken()) {
                $input->setContinuationToken($page->getNextContinuationToken());
                $this->registerPrefetch($nextPage = $client->listObjectsV2($input));
            } else {
                $nextPage = null;
            }
            yield from $page->getContents(\true);
            yield from $page->getCommonPrefixes(\true);
            if (null === $nextPage) {
                break;
            }
            $this->unregisterPrefetch($nextPage);
            $page = $nextPage;
        }
    }
    /**
     * @return int|null
     */
    public function getKeyCount()
    {
        $this->initialize();
        return $this->keyCount;
    }
    /**
     * @return int|null
     */
    public function getMaxKeys()
    {
        $this->initialize();
        return $this->maxKeys;
    }
    /**
     * @return string|null
     */
    public function getName()
    {
        $this->initialize();
        return $this->name;
    }
    /**
     * @return string|null
     */
    public function getNextContinuationToken()
    {
        $this->initialize();
        return $this->nextContinuationToken;
    }
    /**
     * @return string|null
     */
    public function getPrefix()
    {
        $this->initialize();
        return $this->prefix;
    }
    /**
     * @return string|null
     */
    public function getStartAfter()
    {
        $this->initialize();
        return $this->startAfter;
    }
    /**
     * @param Response $response
     * @return void
     */
    protected function populateResult($response)
    {
        $data = new \SimpleXMLElement($response->getContent());
        $this->isTruncated = ($v = $data->IsTruncated) ? \filter_var((string) $v, \FILTER_VALIDATE_BOOLEAN) : null;
        $this->contents = !$data->Contents ? [] : $this->populateResultObjectList($data->Contents);
        $this->name = ($v = $data->Name) ? (string) $v : null;
        $this->prefix = ($v = $data->Prefix) ? (string) $v : null;
        $this->delimiter = ($v = $data->Delimiter) ? (string) $v : null;
        $this->maxKeys = ($v = $data->MaxKeys) ? (int) (string) $v : null;
        $this->commonPrefixes = !$data->CommonPrefixes ? [] : $this->populateResultCommonPrefixList($data->CommonPrefixes);
        $this->encodingType = ($v = $data->EncodingType) ? (string) $v : null;
        $this->keyCount = ($v = $data->KeyCount) ? (int) (string) $v : null;
        $this->continuationToken = ($v = $data->ContinuationToken) ? (string) $v : null;
        $this->nextContinuationToken = ($v = $data->NextContinuationToken) ? (string) $v : null;
        $this->startAfter = ($v = $data->StartAfter) ? (string) $v : null;
    }
    private function populateResultCommonPrefixList(\SimpleXMLElement $xml) : array
    {
        $items = [];
        foreach ($xml as $item) {
            $items[] = new CommonPrefix(['Prefix' => ($v = $item->Prefix) ? (string) $v : null]);
        }
        return $items;
    }
    private function populateResultObjectList(\SimpleXMLElement $xml) : array
    {
        $items = [];
        foreach ($xml as $item) {
            $items[] = new AwsObject(['Key' => ($v = $item->Key) ? (string) $v : null, 'LastModified' => ($v = $item->LastModified) ? new \DateTimeImmutable((string) $v) : null, 'ETag' => ($v = $item->ETag) ? (string) $v : null, 'Size' => ($v = $item->Size) ? (string) $v : null, 'StorageClass' => ($v = $item->StorageClass) ? (string) $v : null, 'Owner' => !$item->Owner ? null : new Owner(['DisplayName' => ($v = $item->Owner->DisplayName) ? (string) $v : null, 'ID' => ($v = $item->Owner->ID) ? (string) $v : null])]);
        }
        return $items;
    }
}
