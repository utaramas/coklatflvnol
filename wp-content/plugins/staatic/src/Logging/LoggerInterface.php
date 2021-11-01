<?php

declare(strict_types=1);

namespace Staatic\WordPress\Logging;

use Staatic\Vendor\Psr\Log\LoggerInterface as PsrLoggerInterface;

interface LoggerInterface extends PsrLoggerInterface, Contextable
{
    public function printLogsEnabled() : bool;

    /**
     * @return void
     */
    public function enablePrintLogs();

    /**
     * @return void
     */
    public function disablePrintLogs();
}
