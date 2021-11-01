<?php

namespace Staatic\Vendor\Psr\Log;

abstract class AbstractLogger implements LoggerInterface
{
    /**
     * @param mixed[] $context
     */
    public function emergency($message, $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function alert($message, $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function critical($message, $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function error($message, $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function warning($message, $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function notice($message, $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function info($message, $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    /**
     * @param mixed[] $context
     */
    public function debug($message, $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
