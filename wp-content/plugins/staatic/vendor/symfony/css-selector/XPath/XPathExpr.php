<?php

namespace Staatic\Vendor\Symfony\Component\CssSelector\XPath;

class XPathExpr
{
    private $path;
    private $element;
    private $condition;
    public function __construct(string $path = '', string $element = '*', string $condition = '', bool $starPrefix = \false)
    {
        $this->path = $path;
        $this->element = $element;
        $this->condition = $condition;
        if ($starPrefix) {
            $this->addStarPrefix();
        }
    }
    public function getElement() : string
    {
        return $this->element;
    }
    /**
     * @param string $condition
     */
    public function addCondition($condition) : self
    {
        $this->condition = $this->condition ? \sprintf('(%s) and (%s)', $this->condition, $condition) : $condition;
        return $this;
    }
    public function getCondition() : string
    {
        return $this->condition;
    }
    public function addNameTest() : self
    {
        if ('*' !== $this->element) {
            $this->addCondition('name() = ' . Translator::getXpathLiteral($this->element));
            $this->element = '*';
        }
        return $this;
    }
    public function addStarPrefix() : self
    {
        $this->path .= '*/';
        return $this;
    }
    /**
     * @param string $combiner
     * @param $this $expr
     */
    public function join($combiner, $expr) : self
    {
        $path = $this->__toString() . $combiner;
        if ('*/' !== $expr->path) {
            $path .= $expr->path;
        }
        $this->path = $path;
        $this->element = $expr->element;
        $this->condition = $expr->condition;
        return $this;
    }
    public function __toString() : string
    {
        $path = $this->path . $this->element;
        $condition = null === $this->condition || '' === $this->condition ? '' : '[' . $this->condition . ']';
        return $path . $condition;
    }
}
