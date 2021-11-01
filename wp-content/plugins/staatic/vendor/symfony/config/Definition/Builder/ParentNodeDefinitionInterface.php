<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition\Builder;

interface ParentNodeDefinitionInterface extends BuilderAwareInterface
{
    public function children();
    /**
     * @param NodeDefinition $node
     */
    public function append($node);
    public function getChildNodeDefinitions();
}
