<?php

namespace Staatic\Framework\Logger;

use Staatic\Vendor\Psr\Log\LoggerTrait as PsrLoggerTrait;
trait LoggerTrait
{
    use PsrLoggerTrait;
    private function getSourceContext() : array
    {
        $backtrace = \debug_backtrace();
        foreach ($backtrace as $index => $item) {
            if (\preg_match('~^log~i', $item['function'])) {
                $sourceItem = $backtrace[$index + 2];
                return ['sourceFile' => $sourceItem['file'] ?? null, 'sourceLine' => $sourceItem['line'] ?? null, 'sourceClass' => $sourceItem['class'], 'sourceFunction' => $sourceItem['function']];
            }
        }
    }
    private function getShortClassName(string $className) : string
    {
        $classNameParts = \explode('\\', $className);
        return \array_pop($classNameParts);
    }
}
