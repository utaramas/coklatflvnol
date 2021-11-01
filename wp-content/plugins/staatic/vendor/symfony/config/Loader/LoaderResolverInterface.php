<?php

namespace Staatic\Vendor\Symfony\Component\Config\Loader;

interface LoaderResolverInterface
{
    /**
     * @param string|null $type
     */
    public function resolve($resource, $type = null);
}
