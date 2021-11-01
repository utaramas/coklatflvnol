<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

final class S3KeyFilter
{
    private $filterRules;
    public function __construct(array $input)
    {
        $this->filterRules = isset($input['FilterRules']) ? \array_map([FilterRule::class, 'create'], $input['FilterRules']) : null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    public function getFilterRules() : array
    {
        return $this->filterRules ?? [];
    }
    /**
     * @return void
     */
    public function requestBody(\DomElement $node, \DomDocument $document)
    {
        if (null !== ($v = $this->filterRules)) {
            foreach ($v as $item) {
                $node->appendChild($child = $document->createElement('FilterRule'));
                $item->requestBody($child, $document);
            }
        }
    }
}
