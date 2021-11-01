<?php

namespace Staatic\Vendor\Symfony\Component\Config\Loader;

class GlobFileLoader extends FileLoader
{
    /**
     * @param string|null $type
     */
    public function load($resource, $type = null)
    {
        return $this->import($resource);
    }
    /**
     * @param string|null $type
     */
    public function supports($resource, $type = null)
    {
        return 'glob' === $type;
    }
}
