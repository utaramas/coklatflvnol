<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication\Task;

use Staatic\WordPress\Factory\StaticGeneratorFactory;
use Staatic\WordPress\Publication\Publication;

final class InitializeCrawlerTask implements TaskInterface
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
        return 'initialize_crawler';
    }

    public function description() : string
    {
        return __('Initializing crawler', 'staatic');
    }

    /**
     * @param Publication $publication
     */
    public function execute($publication) : bool
    {
        $staticGenerator = ($this->factory)($publication);
        $numEnqueued = $staticGenerator->initializeCrawler($publication->build());
        if (!$numEnqueued) {
            throw new \RuntimeException('No crawl urls were enqueued; nothing to do!');
        }
        return \true;
    }
}
