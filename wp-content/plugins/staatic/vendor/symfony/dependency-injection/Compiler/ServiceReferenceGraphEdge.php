<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

class ServiceReferenceGraphEdge
{
    private $sourceNode;
    private $destNode;
    private $value;
    private $lazy;
    private $weak;
    private $byConstructor;
    public function __construct(ServiceReferenceGraphNode $sourceNode, ServiceReferenceGraphNode $destNode, $value = null, bool $lazy = \false, bool $weak = \false, bool $byConstructor = \false)
    {
        $this->sourceNode = $sourceNode;
        $this->destNode = $destNode;
        $this->value = $value;
        $this->lazy = $lazy;
        $this->weak = $weak;
        $this->byConstructor = $byConstructor;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getSourceNode()
    {
        return $this->sourceNode;
    }
    public function getDestNode()
    {
        return $this->destNode;
    }
    public function isLazy()
    {
        return $this->lazy;
    }
    public function isWeak()
    {
        return $this->weak;
    }
    public function isReferencedByConstructor()
    {
        return $this->byConstructor;
    }
}
