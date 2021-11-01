<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page\Publications;

use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Service\AdminNavigation;
use Staatic\WordPress\Service\PartialRenderer;

final class PublicationsPage implements ModuleInterface
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
     * @var PublicationsTable
     */
    private $listTable;

    public function __construct(AdminNavigation $navigation, PartialRenderer $renderer, PublicationsTable $listTable)
    {
        $this->navigation = $navigation;
        $this->renderer = $renderer;
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
        add_action('init', [$this, 'addMenuItem']);
        $this->listTable->registerHooks('staatic_page_staatic-publications');
    }

    /**
     * @return void
     */
    public function addMenuItem()
    {
        $this->navigation->addMenuItem(
            __('Publications', 'staatic'),
            __('Latest Publications', 'staatic'),
            'staatic-publications',
            [$this, 'render'],
            'edit_posts',
            [$this, 'loadPage'],
            10
        );
    }

    /**
     * @return void
     */
    public function loadPage()
    {
        $this->listTable->initialize([
            'baseUrl' => admin_url('admin.php?page=staatic-publications')
        ]);
    }

    /**
     * @return void
     */
    public function render()
    {
        $listTable = $this->listTable->wpListTable();
        $listTable->prepare_items();
        $this->renderer->render('admin/publication/list.php', \compact('listTable'));
    }
}
