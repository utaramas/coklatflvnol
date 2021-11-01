<?php

declare(strict_types=1);

namespace Staatic\WordPress\Logging;

use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\Framework\Logger\LoggerTrait;

final class DatabaseLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var LogEntryRepository
     */
    private $repository;

    public function __construct(LogEntryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = array())
    {
        $context = \array_merge($this->getSourceContext(), $context);
        $this->repository->add(
            new LogEntry($this->repository->nextId(), new \DateTimeImmutable(), $level, $message, $context)
        );
    }
}
