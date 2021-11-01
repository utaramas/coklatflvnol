<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
final class Delete
{
    private $objects;
    private $quiet;
    public function __construct(array $input)
    {
        $this->objects = isset($input['Objects']) ? \array_map([ObjectIdentifier::class, 'create'], $input['Objects']) : null;
        $this->quiet = $input['Quiet'] ?? null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    public function getObjects() : array
    {
        return $this->objects ?? [];
    }
    /**
     * @return bool|null
     */
    public function getQuiet()
    {
        return $this->quiet;
    }
    /**
     * @return void
     */
    public function requestBody(\DomElement $node, \DomDocument $document)
    {
        if (null === ($v = $this->objects)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Objects" for "%s". The value cannot be null.', __CLASS__));
        }
        foreach ($v as $item) {
            $node->appendChild($child = $document->createElement('Object'));
            $item->requestBody($child, $document);
        }
        if (null !== ($v = $this->quiet)) {
            $node->appendChild($document->createElement('Quiet', $v ? 'true' : 'false'));
        }
    }
}
