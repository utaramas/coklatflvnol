<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication\Task;

use Staatic\WordPress\Publication\Publication;

interface TaskInterface
{
    public function name() : string;

    public function description() : string;

    /**
     * @param Publication $publication
     */
    public function execute($publication) : bool;
}
