<?php

namespace Staatic\Vendor\AsyncAws\S3\Result;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
use Staatic\Vendor\AsyncAws\Core\Response;
use Staatic\Vendor\AsyncAws\Core\Result;
use Staatic\Vendor\AsyncAws\S3\Enum\EncodingType;
use Staatic\Vendor\AsyncAws\S3\Input\ListMultipartUploadsRequest;
use Staatic\Vendor\AsyncAws\S3\S3Client;
use Staatic\Vendor\AsyncAws\S3\ValueObject\CommonPrefix;
use Staatic\Vendor\AsyncAws\S3\ValueObject\Initiator;
use Staatic\Vendor\AsyncAws\S3\ValueObject\MultipartUpload;
use Staatic\Vendor\AsyncAws\S3\ValueObject\Owner;
class ListMultipartUploadsOutput extends Result implements \IteratorAggregate
{
    private $bucket;
    private $keyMarker;
    private $uploadIdMarker;
    private $nextKeyMarker;
    private $prefix;
    private $delimiter;
    private $nextUploadIdMarker;
    private $maxUploads;
    private $isTruncated;
    private $uploads = [];
    private $commonPrefixes = [];
    private $encodingType;
    /**
     * @return string|null
     */
    public function getBucket()
    {
        $this->initialize();
        return $this->bucket;
    }
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
        if (!$this->input instanceof ListMultipartUploadsRequest) {
            throw new InvalidArgument('missing last request injected in paginated result');
        }
        $input = clone $this->input;
        $page = $this;
        while (\true) {
            if ($page->getIsTruncated()) {
                $input->setKeyMarker($page->getNextKeyMarker());
                $input->setUploadIdMarker($page->getNextUploadIdMarker());
                $this->registerPrefetch($nextPage = $client->listMultipartUploads($input));
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
        if (!$this->input instanceof ListMultipartUploadsRequest) {
            throw new InvalidArgument('missing last request injected in paginated result');
        }
        $input = clone $this->input;
        $page = $this;
        while (\true) {
            if ($page->getIsTruncated()) {
                $input->setKeyMarker($page->getNextKeyMarker());
                $input->setUploadIdMarker($page->getNextUploadIdMarker());
                $this->registerPrefetch($nextPage = $client->listMultipartUploads($input));
            } else {
                $nextPage = null;
            }
            yield from $page->getUploads(\true);
            yield from $page->getCommonPrefixes(\true);
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
    public function getKeyMarker()
    {
        $this->initialize();
        return $this->keyMarker;
    }
    /**
     * @return int|null
     */
    public function getMaxUploads()
    {
        $this->initialize();
        return $this->maxUploads;
    }
    /**
     * @return string|null
     */
    public function getNextKeyMarker()
    {
        $this->initialize();
        return $this->nextKeyMarker;
    }
    /**
     * @return string|null
     */
    public function getNextUploadIdMarker()
    {
        $this->initialize();
        return $this->nextUploadIdMarker;
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
    public function getUploadIdMarker()
    {
        $this->initialize();
        return $this->uploadIdMarker;
    }
    /**
     * @param bool $currentPageOnly
     * @return mixed[]
     */
    public function getUploads($currentPageOnly = \false)
    {
        if ($currentPageOnly) {
            $this->initialize();
            yield from $this->uploads;
            return;
        }
        $client = $this->awsClient;
        if (!$client instanceof S3Client) {
            throw new InvalidArgument('missing client injected in paginated result');
        }
        if (!$this->input instanceof ListMultipartUploadsRequest) {
            throw new InvalidArgument('missing last request injected in paginated result');
        }
        $input = clone $this->input;
        $page = $this;
        while (\true) {
            if ($page->getIsTruncated()) {
                $input->setKeyMarker($page->getNextKeyMarker());
                $input->setUploadIdMarker($page->getNextUploadIdMarker());
                $this->registerPrefetch($nextPage = $client->listMultipartUploads($input));
            } else {
                $nextPage = null;
            }
            yield from $page->getUploads(\true);
            if (null === $nextPage) {
                break;
            }
            $this->unregisterPrefetch($nextPage);
            $page = $nextPage;
        }
    }
    /**
     * @param Response $response
     * @return void
     */
    protected function populateResult($response)
    {
        $data = new \SimpleXMLElement($response->getContent());
        $this->bucket = ($v = $data->Bucket) ? (string) $v : null;
        $this->keyMarker = ($v = $data->KeyMarker) ? (string) $v : null;
        $this->uploadIdMarker = ($v = $data->UploadIdMarker) ? (string) $v : null;
        $this->nextKeyMarker = ($v = $data->NextKeyMarker) ? (string) $v : null;
        $this->prefix = ($v = $data->Prefix) ? (string) $v : null;
        $this->delimiter = ($v = $data->Delimiter) ? (string) $v : null;
        $this->nextUploadIdMarker = ($v = $data->NextUploadIdMarker) ? (string) $v : null;
        $this->maxUploads = ($v = $data->MaxUploads) ? (int) (string) $v : null;
        $this->isTruncated = ($v = $data->IsTruncated) ? \filter_var((string) $v, \FILTER_VALIDATE_BOOLEAN) : null;
        $this->uploads = !$data->Upload ? [] : $this->populateResultMultipartUploadList($data->Upload);
        $this->commonPrefixes = !$data->CommonPrefixes ? [] : $this->populateResultCommonPrefixList($data->CommonPrefixes);
        $this->encodingType = ($v = $data->EncodingType) ? (string) $v : null;
    }
    private function populateResultCommonPrefixList(\SimpleXMLElement $xml) : array
    {
        $items = [];
        foreach ($xml as $item) {
            $items[] = new CommonPrefix(['Prefix' => ($v = $item->Prefix) ? (string) $v : null]);
        }
        return $items;
    }
    private function populateResultMultipartUploadList(\SimpleXMLElement $xml) : array
    {
        $items = [];
        foreach ($xml as $item) {
            $items[] = new MultipartUpload(['UploadId' => ($v = $item->UploadId) ? (string) $v : null, 'Key' => ($v = $item->Key) ? (string) $v : null, 'Initiated' => ($v = $item->Initiated) ? new \DateTimeImmutable((string) $v) : null, 'StorageClass' => ($v = $item->StorageClass) ? (string) $v : null, 'Owner' => !$item->Owner ? null : new Owner(['DisplayName' => ($v = $item->Owner->DisplayName) ? (string) $v : null, 'ID' => ($v = $item->Owner->ID) ? (string) $v : null]), 'Initiator' => !$item->Initiator ? null : new Initiator(['ID' => ($v = $item->Initiator->ID) ? (string) $v : null, 'DisplayName' => ($v = $item->Initiator->DisplayName) ? (string) $v : null])]);
        }
        return $items;
    }
}
