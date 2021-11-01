<?php

declare (strict_types=1);
namespace Staatic\Vendor\voku\helper;

abstract class AbstractSimpleHtmlDomNode extends \ArrayObject
{
    public function __get($name)
    {
        $name = \strtolower($name);
        if ($name === 'length') {
            return $this->count();
        }
        if ($this->count() > 0) {
            $return = [];
            foreach ($this as $node) {
                if ($node instanceof SimpleHtmlDomInterface) {
                    $return[] = $node->{$name};
                }
            }
            return $return;
        }
        if ($name === 'plaintext' || $name === 'outertext') {
            return [];
        }
        return null;
    }
    public function __invoke($selector, $idx = null)
    {
        return $this->find($selector, $idx);
    }
    public function __toString()
    {
        $html = '';
        foreach ($this as $node) {
            $html .= $node->outertext;
        }
        return $html;
    }
    /**
     * @param string $selector
     */
    public abstract function find($selector, $idx = null);
}