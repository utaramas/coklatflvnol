<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\WordPress\Bootstrap;
use Staatic\WordPress\Logging\DatabaseLogger;
use Staatic\WordPress\Logging\Logger;

final class LoggerFactory
{
    /**
     * @var DatabaseLogger
     */
    private $databaseLogger;

    public function __construct(DatabaseLogger $databaseLogger)
    {
        $this->databaseLogger = $databaseLogger;
    }

    public function __invoke() : LoggerInterface
    {
        $isDebug = Bootstrap::instance()->isDebug();
        return new Logger($this->databaseLogger, $isDebug);
    }
}
