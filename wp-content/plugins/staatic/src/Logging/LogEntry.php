<?php

declare(strict_types=1);

namespace Staatic\WordPress\Logging;

final class LogEntry
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var string
     */
    private $level;

    /**
     * @var string
     */
    private $message;

    /**
     * @var mixed[]|null
     */
    private $context;

    /**
     * @param mixed[]|null $context
     */
    public function __construct(string $id, \DateTimeInterface $date, string $level, string $message, $context = [])
    {
        $this->id = $id;
        $this->date = $date;
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    public function id() : string
    {
        return $this->id;
    }

    public function date() : \DateTimeInterface
    {
        return $this->date;
    }

    public function level() : string
    {
        return $this->level;
    }

    public function message() : string
    {
        return $this->message;
    }

    /**
     * @return mixed[]|null
     */
    public function context()
    {
        return $this->context;
    }
}
