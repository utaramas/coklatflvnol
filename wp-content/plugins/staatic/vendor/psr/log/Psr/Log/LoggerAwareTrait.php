<?php

namespace Staatic\Vendor\Psr\Log;

trait LoggerAwareTrait
{
    protected $logger;
    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
