<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page;

use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\WordPress\Factory\FilesystemResourceRepositoryFactory;
use Staatic\WordPress\Module\ModuleInterface;

final class BuildResultPage implements ModuleInterface
{
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * @var FilesystemResourceRepositoryFactory
     */
    private $resourceRepositoryFactory;

    public function __construct(
        ResultRepositoryInterface $resultRepository,
        FilesystemResourceRepositoryFactory $resourceRepositoryFactory
    )
    {
        $this->resultRepository = $resultRepository;
        $this->resourceRepositoryFactory = $resourceRepositoryFactory;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (!is_admin()) {
            return;
        }
        add_action('wp_loaded', [$this, 'handle']);
    }

    /**
     * @return void
     */
    public function handle()
    {
        if (!isset($_REQUEST['staatic']) || $_REQUEST['staatic'] !== 'result-download') {
            return;
        }
        $resultId = isset($_REQUEST['id']) ? sanitize_key($_REQUEST['id']) : null;
        if (!$resultId) {
            wp_die(__('Missing result id.', 'staatic'));
        }
        if (!($result = $this->resultRepository->find($resultId))) {
            wp_die(__('Invalid result.', 'staatic'));
        }
        $resourceRepository = ($this->resourceRepositoryFactory)();
        if (!($resource = $resourceRepository->find($result->resourceId()))) {
            wp_die(__('Invalid resource.', 'staatic'));
        }
        $filename = \basename($result->url()->getPath());
        \header(\sprintf('Content-Disposition: attachment; filename="%s"', $filename));
        \header(\sprintf('Content-Type: %s', $result->mimeType()));
        \header(\sprintf('Content-Length: %d', $result->size()));
        while (!$resource->content()->eof()) {
            echo $resource->content()->read(4096);
        }
        die;
    }
}
