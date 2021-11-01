<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Framework\ResourceRepository\FilesystemResourceRepository;
use Staatic\WordPress\Setting\Advanced\WorkDirectorySetting;

final class FilesystemResourceRepositoryFactory
{
    /**
     * @var WorkDirectorySetting
     */
    private $workDirectory;

    public function __construct(WorkDirectorySetting $workDirectory)
    {
        $this->workDirectory = $workDirectory;
    }

    public function __invoke() : FilesystemResourceRepository
    {
        $buildDirectory = trailingslashit($this->workDirectory->value()) . 'build';
        return new FilesystemResourceRepository($buildDirectory);
    }
}
