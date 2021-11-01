<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page\Publications;

use Staatic\WordPress\ListTable\Column\AbstractColumn;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Publication\PublicationTaskProvider;
use Staatic\WordPress\Service\Formatter;

final class PublicationStatusColumn extends AbstractColumn
{
    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var PublicationTaskProvider
     */
    private $taskProvider;

    public function __construct(
        Formatter $formatter,
        PublicationTaskProvider $taskProvider,
        string $name,
        string $label,
        array $arguments = [
    ])
    {
        parent::__construct($name, $label, $arguments);
        $this->formatter = $formatter;
        $this->taskProvider = $taskProvider;
    }

    /**
     * @param Publication $publication
     * @return void
     */
    public function render($publication)
    {
        $result = $publication->status()->label();
        if ($this->shouldDisplayTaskDescription($publication)) {
            $currentTask = $this->taskProvider->getTask($publication->currentTask());
            $result = $currentTask->description();
        } elseif ($this->shouldDisplayDateFinished($publication)) {
            $timeTaken = $this->formatter->difference($publication->dateCreated(), $publication->dateFinished());
            $result = \sprintf('%s (<em>%s</em>)<br>%s', $publication->status()->label(), $this->formatter->shortDate(
                $publication->dateFinished()
            ), \sprintf(
                /* translators: %s: Date interval time taken. */
                __('Time taken: %s', 'staatic'),
                $timeTaken
            ));
        } else {
            $result = $publication->status()->label();
        }
        echo $this->applyDecorators($result, $publication);
    }

    private function shouldDisplayTaskDescription(Publication $publication) : bool
    {
        return $publication->status()->isInProgress() && $publication->currentTask();
    }

    private function shouldDisplayDateFinished(Publication $publication) : bool
    {
        return $publication->status()->isFinished() || $publication->status()->isFailed();
    }

    /**
     * @return string|null
     */
    public function defaultSortColumn()
    {
        return 'status';
    }
}
