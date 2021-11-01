<?php

namespace Staatic\Vendor\Symfony\Component\CssSelector\Node;

class SelectorNode extends AbstractNode
{
    private $tree;
    private $pseudoElement;
    public function __construct(NodeInterface $tree, string $pseudoElement = null)
    {
        $this->tree = $tree;
        $this->pseudoElement = $pseudoElement ? \strtolower($pseudoElement) : null;
    }
    public function getTree() : NodeInterface
    {
        return $this->tree;
    }
    /**
     * @return string|null
     */
    public function getPseudoElement()
    {
        return $this->pseudoElement;
    }
    public function getSpecificity() : Specificity
    {
        return $this->tree->getSpecificity()->plus(new Specificity(0, 0, $this->pseudoElement ? 1 : 0));
    }
    public function __toString() : string
    {
        return \sprintf('%s[%s%s]', $this->getNodeName(), $this->tree, $this->pseudoElement ? '::' . $this->pseudoElement : '');
    }
}
