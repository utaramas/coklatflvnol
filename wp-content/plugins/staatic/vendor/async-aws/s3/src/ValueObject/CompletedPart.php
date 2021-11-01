<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

final class CompletedPart
{
    private $etag;
    private $partNumber;
    public function __construct(array $input)
    {
        $this->etag = $input['ETag'] ?? null;
        $this->partNumber = $input['PartNumber'] ?? null;
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
     * @return int|null
     */
    public function getPartNumber()
    {
        return $this->partNumber;
    }
    /**
     * @return void
     */
    public function requestBody(\DomElement $node, \DomDocument $document)
    {
        if (null !== ($v = $this->etag)) {
            $node->appendChild($document->createElement('ETag', $v));
        }
        if (null !== ($v = $this->partNumber)) {
            $node->appendChild($document->createElement('PartNumber', $v));
        }
    }
}
