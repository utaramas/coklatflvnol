<?php

namespace Staatic\Framework\ResourceRepository;

use Staatic\Framework\Resource;
interface ResourceRepositoryInterface
{
    /**
     * @param Resource $resource
     * @return void
     */
    public function write($resource);
    /**
     * @param string $resourceId
     * @return Resource|null
     */
    public function find($resourceId);
}
