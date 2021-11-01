<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

final class CopyObjectResult
{
    private $etag;
    private $lastModified;
    public function __construct(array $input)
    {
        $this->etag = $input['ETag'] ?? null;
        $this->lastModified = $input['LastModified'] ?? null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    /**
     * @return string|null
     */
    public function getEtag()
    {
        return $this->etag;
    }
    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }
}
