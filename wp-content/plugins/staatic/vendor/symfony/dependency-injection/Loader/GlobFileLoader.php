<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader;

class GlobFileLoader extends FileLoader
{
    /**
     * @param string|null $type
     */
    public function load($resource, $type = null)
    {
        foreach ($this->glob($resource, \false, $globResource) as $path => $info) {
            $this->import($path);
        }
        $this->container->addResource($globResource);
    }
    /**
     * @param string|null $type
     */
    public function supports($resource, $type = null)
    {
        return 'glob' === $type;
    }
}
