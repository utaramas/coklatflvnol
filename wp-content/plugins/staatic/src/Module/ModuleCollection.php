<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module;

final class ModuleCollection implements \IteratorAggregate
{
    /**
     * @var ModuleInterface[]
     */
    private $modules;

    /**
     * @param \Traversable|ModuleInterface[] $modules
     */
    public function __construct($modules)
    {
        $this->modules = \iterator_to_array($modules);
    }

    /**
     * @return \ArrayIterator|ModuleInterface[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->modules);
    }
}
