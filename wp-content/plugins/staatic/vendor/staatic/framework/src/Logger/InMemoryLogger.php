<?php

namespace Staatic\Framework\Logger;

use Staatic\Vendor\Psr\Log\LoggerInterface;
class InMemoryLogger implements LoggerInterface
{
    use LoggerTrait;
    const FORMAT = "[%s %s] %s%s\n";
    const CONTEXT_FORMAT = "[%s] ";
    /**
     * @var mixed[]
     */
    private $logEntries = [];
    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = [])
    {
        $context = \array_merge($this->getSourceContext(), $context);
        $date = (new \DateTime())->format('H:i:s.u');
        $memory = \number_format(\memory_get_usage() / 1024 / 1024, 3) . ' MB';
        $contextString = \count($context) > 0 ? \sprintf(self::CONTEXT_FORMAT, \implode('] [', $context)) : '';
        $this->logEntries[] = \sprintf(self::FORMAT, $date, $memory, $contextString, $message);
    }
    public function getLogEntries() : array
    {
        return $this->logEntries;
    }
}
