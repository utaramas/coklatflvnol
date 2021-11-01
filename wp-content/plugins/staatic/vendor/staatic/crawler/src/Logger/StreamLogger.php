<?php

namespace Staatic\Crawler\Logger;

use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\StreamInterface;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Vendor\Psr\Log\LoggerTrait;
class StreamLogger implements LoggerInterface
{
    use LoggerTrait;
    const FORMAT = "[%s %s] %s%s\n";
    const CONTEXT_FORMAT = "[%s] ";
    /**
     * @var StreamInterface
     */
    private $logStream;
    public function __construct(StreamInterface $logStream)
    {
        if (!$logStream->isWritable()) {
            throw new \InvalidArgumentException('Log stream is not writable');
        }
        $this->logStream = $logStream;
    }
    /**
     * @param string $path
     */
    public static function createFromFile($path) : self
    {
        try {
            $handle = \fopen($path, 'a');
        } catch (\Error $error) {
            throw new \InvalidArgumentException(\sprintf('Unable to open file for writing in "%s": %s', $path, $error->getMessage()));
        }
        return new self(Utils::streamFor($handle));
    }
    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = [])
    {
        $date = (new \DateTime())->format('H:i:s.u');
        $memory = \number_format(\memory_get_usage() / 1024 / 1024, 3) . ' MB';
        $contextString = \count($context) > 0 ? \sprintf(self::CONTEXT_FORMAT, \implode('] [', $context)) : '';
        $this->logStream->write(\sprintf(self::FORMAT, $date, $memory, $contextString, $message));
        if (\strpos($message, 'forkawesome-webfont.eot') === \false) {
            return;
        }
        \printf(self::FORMAT, $date, $memory, $contextString, $message);
        \flush();
    }
}
