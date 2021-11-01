<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

final class Part
{
    private $partNumber;
    private $lastModified;
    private $etag;
    private $size;
    public function __construct(array $input)
    {
        $this->partNumber = $input['PartNumber'] ?? null;
        $this->lastModified = $input['LastModified'] ?? null;
        $this->etag = $input['ETag'] ?? null;
        $this->size = $input['Size'] ?? null;
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
    public function getSize()
    {
        return $this->size;
    }
}
