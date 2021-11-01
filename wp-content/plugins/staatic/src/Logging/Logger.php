<?php

declare(strict_types=1);

namespace Staatic\WordPress\Logging;

use Staatic\Vendor\Psr\Log\LoggerTrait;
use Staatic\Vendor\Psr\Log\LogLevel;

final class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var DatabaseLogger
     */
    private $dbLogger;

    /**
     * @var bool
     */
    private $logDebug;

    /**
     * @var bool
     */
    private $printLogsEnabled = \false;

    /**
     * @var mixed[]
     */
    private $context = [];

    public function __construct(DatabaseLogger $dbLogger, bool $logDebug = \true)
    {
        $this->dbLogger = $dbLogger;
        $this->logDebug = $logDebug;
    }

    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = array())
    {
        if ($level === LogLevel::DEBUG && !$this->logDebug) {
            return;
        }
        $context = \array_merge([
            'memory' => \memory_get_usage()
        ], $this->context, $context);
        $this->dbLogger->log($level, $message, $context);
        if ($this->printLogsEnabled) {
            \printf("%s [%s]: %s\n", (new \DateTime())->format('c'), $level, $message);
        }
    }

    public function printLogsEnabled() : bool
    {
        return $this->printLogsEnabled;
    }

    /**
     * @return void
     */
    public function enablePrintLogs()
    {
        $this->printLogsEnabled = \true;
    }

    /**
     * @return void
     */
    public function disablePrintLogs()
    {
        $this->printLogsEnabled = \false;
    }

    /**
     * @param mixed[] $context
     * @return void
     */
    public function changeContext($context)
    {
        $this->context = $context;
    }
}
