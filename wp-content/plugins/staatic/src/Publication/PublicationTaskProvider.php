<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication;

use Staatic\WordPress\Publication\Task\TaskInterface;

final class PublicationTaskProvider
{
    /** @var TaskInterface[] */
    private $tasks;

    /**
     * @var bool
     */
    private $initialized = \false;

    /**
     * @param iterable $publicationTasks
     */
    public function __construct($publicationTasks)
    {
        foreach ($publicationTasks as $task) {
            $this->tasks[\get_class($task)] = $task;
        }
        if (empty($this->tasks)) {
            throw new \InvalidArgumentException('No tasks provided!');
        }
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks() : array
    {
        if (!$this->initialized) {
            $this->tasks = apply_filters('staatic_publication_tasks', $this->tasks);
            $this->initialized = \true;
        }
        return $this->tasks;
    }

    public function getTask(string $taskName) : TaskInterface
    {
        $tasks = $this->getTasks();
        if (!isset($tasks[$taskName])) {
            throw new \InvalidArgumentException(\sprintf('Task with name %s does not exist', $taskName));
        }
        return $tasks[$taskName];
    }

    public function firstTask() : TaskInterface
    {
        $tasks = $this->getTasks();
        $firstTask = \array_shift($tasks);
        if (!$firstTask) {
            throw new \RuntimeException('No publication tasks are configured');
        }
        return $firstTask;
    }

    /**
     * @return TaskInterface|null
     */
    public function nextTask(TaskInterface $currentTask)
    {
        $tasks = $this->getTasks();
        $keys = \array_keys($tasks);
        $index = \array_search(\get_class($currentTask), $keys);
        if (isset($keys[$index + 1])) {
            return $tasks[$keys[$index + 1]];
        } else {
            return null;
        }
    }
}
