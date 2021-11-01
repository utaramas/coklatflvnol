<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Dumper;

interface DumperInterface
{
    /**
     * @param mixed[] $options
     */
    public function dump($options = []);
}
