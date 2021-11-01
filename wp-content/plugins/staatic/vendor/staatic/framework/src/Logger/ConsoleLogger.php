<?php

namespace Staatic\Framework\Logger;

use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\LogLevel;
class ConsoleLogger implements LoggerInterface
{
    use LoggerTrait;
    const FORMAT = "\33[2m[%s %s]\33[0m %s\33[%dm%s\33[0m\n";
    const CONTEXT_FORMAT = "\33[2m[%s]\33[0m ";
    const LOG_LEVEL_COLORS = [LogLevel::EMERGENCY => 91, LogLevel::ALERT => 91, LogLevel::CRITICAL => 91, LogLevel::ERROR => 31, LogLevel::WARNING => 31, LogLevel::NOTICE => 36, LogLevel::INFO => 0, LogLevel::DEBUG => 94];
    private $outputHandle;
    public function __construct($outputHandle = \STDOUT)
    {
        if (!\is_resource($outputHandle)) {
            throw new \InvalidArgumentException(\sprintf('Output handle should be a valid resource type. %s given', \gettype($outputHandle)));
        } elseif (\get_resource_type($outputHandle) !== 'stream') {
            throw new \InvalidArgumentException(\sprintf('Output handle should be a valid stream resource. %s given', \get_resource_type($outputHandle)));
        }
        $this->outputHandle = $outputHandle;
    }
    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = [])
    {
        $sourceContext = $this->getSourceContext();
        $source = $this->getShortClassName($sourceContext['sourceClass']);
        $context = \array_merge(['source' => $source], $context);
        $color = self::LOG_LEVEL_COLORS[$level];
        $date = (new \DateTime())->format('H:i:s.u');
        $memory = \number_format(\memory_get_usage() / 1024 / 1024, 3) . ' MB';
        $contextString = \count($context) > 0 ? \sprintf(self::CONTEXT_FORMAT, \implode('] [', $context)) : '';
        $output = \sprintf(self::FORMAT, $date, $memory, $contextString, $color, $message);
        \fwrite($this->outputHandle, $output);
    }
}
