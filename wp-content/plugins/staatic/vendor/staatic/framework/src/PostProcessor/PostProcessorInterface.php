<?php

namespace Staatic\Framework\PostProcessor;

interface PostProcessorInterface
{
    public function createsOrRemovesResults() : bool;
    /**
     * @param string $buildId
     * @return void
     */
    public function apply($buildId);
}
