<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication\Task;

use Staatic\WordPress\Factory\StaticGeneratorFactory;
use Staatic\WordPress\Publication\Publication;

final class PostProcessTask implements TaskInterface
{
    /**
     * @var StaticGeneratorFactory
     */
    private $factory;

    public function __construct(StaticGeneratorFactory $factory)
    {
        $this->factory = $factory;
    }

    public function name() : string
    {
        return 'post_process';
    }

    public function description() : string
    {
        return __('Post-processing', 'staatic');
    }

    /**
     * @param Publication $publication
     */
    public function execute($publication) : bool
    {
        $staticGenerator = ($this->factory)($publication);
        $staticGenerator->postProcess($publication->build());
        return \true;
    }
}
