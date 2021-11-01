<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Alias;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
class ServiceReferenceGraphNode
{
    private $id;
    private $inEdges = [];
    private $outEdges = [];
    private $value;
    public function __construct(string $id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }
    /**
     * @param ServiceReferenceGraphEdge $edge
     */
    public function addInEdge($edge)
    {
        $this->inEdges[] = $edge;
    }
    /**
     * @param ServiceReferenceGraphEdge $edge
     */
    public function addOutEdge($edge)
    {
        $this->outEdges[] = $edge;
    }
    public function isAlias()
    {
        return $this->value instanceof Alias;
    }
    public function isDefinition()
    {
        return $this->value instanceof Definition;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getInEdges()
    {
        return $this->inEdges;
    }
    public function getOutEdges()
    {
        return $this->outEdges;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function clear()
    {
        $this->inEdges = $this->outEdges = [];
    }
}
