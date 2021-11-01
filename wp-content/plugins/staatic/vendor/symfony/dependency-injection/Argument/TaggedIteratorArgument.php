<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Argument;

class TaggedIteratorArgument extends IteratorArgument
{
    private $tag;
    private $indexAttribute;
    private $defaultIndexMethod;
    private $defaultPriorityMethod;
    private $needsIndexes = \false;
    public function __construct(string $tag, string $indexAttribute = null, string $defaultIndexMethod = null, bool $needsIndexes = \false, string $defaultPriorityMethod = null)
    {
        parent::__construct([]);
        if (null === $indexAttribute && $needsIndexes) {
            $indexAttribute = \preg_match('/[^.]++$/', $tag, $m) ? $m[0] : $tag;
        }
        $this->tag = $tag;
        $this->indexAttribute = $indexAttribute;
        $this->defaultIndexMethod = $defaultIndexMethod ?: ($indexAttribute ? 'getDefault' . \str_replace(' ', '', \ucwords(\preg_replace('/[^a-zA-Z0-9\\x7f-\\xff]++/', ' ', $indexAttribute))) . 'Name' : null);
        $this->needsIndexes = $needsIndexes;
        $this->defaultPriorityMethod = $defaultPriorityMethod ?: ($indexAttribute ? 'getDefault' . \str_replace(' ', '', \ucwords(\preg_replace('/[^a-zA-Z0-9\\x7f-\\xff]++/', ' ', $indexAttribute))) . 'Priority' : null);
    }
    public function getTag()
    {
        return $this->tag;
    }
    /**
     * @return string|null
     */
    public function getIndexAttribute()
    {
        return $this->indexAttribute;
    }
    /**
     * @return string|null
     */
    public function getDefaultIndexMethod()
    {
        return $this->defaultIndexMethod;
    }
    public function needsIndexes() : bool
    {
        return $this->needsIndexes;
    }
    /**
     * @return string|null
     */
    public function getDefaultPriorityMethod()
    {
        return $this->defaultPriorityMethod;
    }
}
