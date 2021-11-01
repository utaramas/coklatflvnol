<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait DeprecateTrait
{
    public final function deprecate() : self
    {
        $args = \func_get_args();
        $package = $version = $message = '';
        if (\func_num_args() < 3) {
            trigger_deprecation('symfony/dependency-injection', '5.1', 'The signature of method "%s()" requires 3 arguments: "string $package, string $version, string $message", not defining them is deprecated.', __METHOD__);
            $message = (string) ($args[0] ?? null);
        } else {
            $package = (string) $args[0];
            $version = (string) $args[1];
            $message = (string) $args[2];
        }
        $this->definition->setDeprecated($package, $version, $message);
        return $this;
    }
}
