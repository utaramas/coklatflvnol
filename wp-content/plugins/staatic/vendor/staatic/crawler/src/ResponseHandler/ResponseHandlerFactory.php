<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Crawler\CrawlerInterface;
final class ResponseHandlerFactory
{
    public static function create(string $handler, CrawlerInterface $crawler, ...$constructorArguments) : ResponseHandlerInterface
    {
        $className = \ucfirst($handler) . 'ResponseHandler';
        $fullyQualifiedClassName = \sprintf('%s\\%s', __NAMESPACE__, $className);
        if (!\class_exists($fullyQualifiedClassName)) {
            throw new \RuntimeException(\sprintf('Unable to create response handler for "%s": class "%s" does not exist', $handler, $fullyQualifiedClassName));
        }
        return new $fullyQualifiedClassName($crawler, ...$constructorArguments);
    }
    public static function createChain(array $handlers, CrawlerInterface $crawler, ...$constructorArguments) : ResponseHandlerInterface
    {
        $initialInstance = null;
        $previousInstance = null;
        foreach ($handlers as $handler) {
            $instance = self::create($handler, $crawler, ...$constructorArguments);
            if ($previousInstance) {
                $previousInstance->setNext($instance);
            } else {
                $initialInstance = $instance;
            }
            $previousInstance = $instance;
        }
        return $initialInstance;
    }
}
