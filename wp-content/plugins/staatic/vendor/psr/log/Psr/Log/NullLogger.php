<?php

namespace Staatic\Vendor\Psr\Log;

class NullLogger extends AbstractLogger
{
    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = array())
    {
    }
}
