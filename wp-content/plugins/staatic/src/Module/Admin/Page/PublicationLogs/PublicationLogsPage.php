<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page\PublicationLogs;

use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Service\AdminNavigation;
use Staatic\WordPress\Service\PartialRenderer;

final class PublicationLogsPage implements ModuleInterface
{
    /**
     * @var AdminNavigation
     */
    private $navigation;

    /**
     * @var PartialRenderer
     */
    private $renderer;

    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    /**
     * @var PublicationLogsTable
     */
    private $listTable;

    public function __construct(
        AdminNavigation $navigation,
        PartialRenderer $renderer,
        PublicationRepository $publicationRepository,
        PublicationLogsTable $listTable
    )
    {
        $this->navigation = $navigation;
        $this->renderer = $renderer;
        $this->publicationRepository = $publicationRepository;
        $this->listTable = $listTable;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (!is_admin()) {
            return;
        }
        add_action('init', [$this, 'addPage']);
        $this->listTable->registerHooks('staatic_page_staatic-publication-logs');
    }

    /**
     * @return void
     */
    public function addPage()
    {
        $this->navigation->addPage(
            __('Publication Logs', 'staatic'),
            'staatic-publication-logs',
            [$this, 'render'],
            'edit_posts',
            'staatic-publications',
            [$this, 'loadPage']
        );
    }

    /**
     * @return void
     */
    public function loadPage()
    {
        $this->listTable->initialize();
    }

    /**
     * @return void
     */
    public function render()
    {
        $publicationId = isset($_REQUEST['id']) ? sanitize_key($_REQUEST['id']) : null;
        if (!$publicationId) {
            wp_die(__('Missing publication id.', 'staatic'));
        }
        if (!($publication = $this->publicationRepository->find($publicationId))) {
            wp_die(__('Invalid publication.', 'staatic'));
        }
        $this->listTable->setArguments([
            'baseUrl' => admin_url(\sprintf('admin.php?page=staatic-publication-logs&id=%s', $publication->id())),
            'publicationId' => $publication->id()
        ]);
        $listTable = $this->listTable->wpListTable();
        $listTable->prepare_items();
        $this->renderer->render('admin/publication/logs.php', \compact('publication', 'listTable'));
    }
}
