<?php

namespace Staatic\Vendor\AsyncAws\S3\Result;

use Staatic\Vendor\AsyncAws\Core\Response;
use Staatic\Vendor\AsyncAws\Core\Result;
use Staatic\Vendor\AsyncAws\S3\Enum\RequestCharged;
use Staatic\Vendor\AsyncAws\S3\Enum\ServerSideEncryption;
class PutObjectOutput extends Result
{
    private $expiration;
    private $etag;
    private $serverSideEncryption;
    private $versionId;
    private $sseCustomerAlgorithm;
    private $sseCustomerKeyMd5;
    private $sseKmsKeyId;
    private $sseKmsEncryptionContext;
    private $bucketKeyEnabled;
    private $requestCharged;
    /**
     * @return bool|null
     */
    public function getBucketKeyEnabled()
    {
        $this->initialize();
        return $this->bucketKeyEnabled;
    }
    /**
     * @return string|null
     */
    public function getEtag()
    {
        $this->initialize();
        return $this->etag;
    }
    /**
     * @return string|null
     */
    public function getExpiration()
    {
        $this->initialize();
        return $this->expiration;
    }
    /**
     * @return string|null
     */
    public function getRequestCharged()
    {
        $this->initialize();
        return $this->requestCharged;
    }
    /**
     * @return string|null
     */
    public function getServerSideEncryption()
    {
        $this->initialize();
        return $this->serverSideEncryption;
    }
    /**
     * @return string|null
     */
    public function getSseCustomerAlgorithm()
    {
        $this->initialize();
        return $this->sseCustomerAlgorithm;
    }
    /**
     * @return string|null
     */
    public function getSseCustomerKeyMd5()
    {
        $this->initialize();
        return $this->sseCustomerKeyMd5;
    }
    /**
     * @return string|null
     */
    public function getSseKmsEncryptionContext()
    {
        $this->initialize();
        return $this->sseKmsEncryptionContext;
    }
    /**
     * @return string|null
     */
    public function getSseKmsKeyId()
    {
        $this->initialize();
        return $this->sseKmsKeyId;
    }
    /**
     * @return string|null
     */
    public function getVersionId()
    {
        $this->initialize();
        return $this->versionId;
    }
    /**
     * @param Response $response
     * @return void
     */
    protected function populateResult($response)
    {
        $headers = $response->getHeaders();
        $this->expiration = $headers['x-amz-expiration'][0] ?? null;
        $this->etag = $headers['etag'][0] ?? null;
        $this->serverSideEncryption = $headers['x-amz-server-side-encryption'][0] ?? null;
        $this->versionId = $headers['x-amz-version-id'][0] ?? null;
        $this->sseCustomerAlgorithm = $headers['x-amz-server-side-encryption-customer-algorithm'][0] ?? null;
        $this->sseCustomerKeyMd5 = $headers['x-amz-server-side-encryption-customer-key-md5'][0] ?? null;
        $this->sseKmsKeyId = $headers['x-amz-server-side-encryption-aws-kms-key-id'][0] ?? null;
        $this->sseKmsEncryptionContext = $headers['x-amz-server-side-encryption-context'][0] ?? null;
        $this->bucketKeyEnabled = isset($headers['x-amz-server-side-encryption-bucket-key-enabled'][0]) ? \filter_var($headers['x-amz-server-side-encryption-bucket-key-enabled'][0], \FILTER_VALIDATE_BOOLEAN) : null;
        $this->requestCharged = $headers['x-amz-request-charged'][0] ?? null;
    }
}
