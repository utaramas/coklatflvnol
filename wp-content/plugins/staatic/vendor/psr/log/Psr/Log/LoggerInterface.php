<?php

namespace Staatic\Vendor\Psr\Log;

interface LoggerInterface
{
    /**
     * @param mixed[] $context
     */
    public function emergency($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function alert($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function critical($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function error($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function warning($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function notice($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function info($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function debug($message, $context = array());
    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = array());
}
