<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page\Publications;

use Staatic\WordPress\ListTable\AbstractListTable;
use Staatic\WordPress\ListTable\Column\ColumnFactory;
use Staatic\WordPress\ListTable\Column\DateColumn;
use Staatic\WordPress\ListTable\Column\UserColumn;
use Staatic\WordPress\ListTable\RowAction\RowAction;
use Staatic\WordPress\ListTable\View\View;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Publication\PublicationStatus;
use Staatic\WordPress\Publication\PublicationTaskProvider;
use Staatic\WordPress\Service\Formatter;

class PublicationsTable extends AbstractListTable
{
    /** @var string */
    const NAME = 'publication_list_table';

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    /**
     * @var PublicationTaskProvider
     */
    private $publicationTaskProvider;

    public function __construct(
        Formatter $formatter,
        ColumnFactory $columnFactory,
        PublicationRepository $publicationRepository,
        PublicationTaskProvider $publicationTaskProvider
    )
    {
        parent::__construct('id', ['date_created', 'DESC']);
        $this->formatter = $formatter;
        $this->columnFactory = $columnFactory;
        $this->publicationRepository = $publicationRepository;
        $this->publicationTaskProvider = $publicationTaskProvider;
    }

    /**
     * @param mixed[] $arguments
     * @return void
     */
    public function initialize($arguments = [])
    {
        parent::initialize($arguments);
        $this->setupColumns();
        $this->setupViews();
        $this->setupRowActions();
        // $this->setupBulkActions();
    }

    /**
     * @return void
     */
    public function setupColumns()
    {
        $this->addColumns([$this->columnFactory->create(DateColumn::class, [
            'name' => 'date_created',
            'label' => __('Publication Date', 'staatic')
        ]), $this->columnFactory->create(UserColumn::class, [
            'name' => 'user_id',
            'label' => __('Publisher', 'staatic')
        ]), new PublicationStatusColumn(
            $this->formatter,
            $this->publicationTaskProvider,
            'status',
            __('Status', 'staatic')
        )]);
    }

    /**
     * @return void
     */
    public function setupViews()
    {
        $publicationTypes = PublicationStatus::labels();
        foreach ($publicationTypes as $name => $label) {
            $this->addView(new View($name, $label));
        }
    }

    /**
     * @return void
     */
    public function setupRowActions()
    {
        $this->addRowActions(
            [new RowAction('details', __('Details', 'staatic'), admin_url('admin.php?page=staatic-publication&id=%s'))]
        );
    }

    // public function setupBulkActions(): void
    // {
    //     $this->addBulkAction(
    //         new BulkAction('delete', __('Delete', 'staatic')),
    //     );
    // }

    /**
     * @param string|null $view
     * @param string|null $query
     * @param int $limit
     * @param int $offset
     * @param string|null $orderBy
     * @param string|null $direction
     */
    public function items($view, $query, $limit, $offset, $orderBy, $direction) : array
    {
        return $this->publicationRepository->findWhereMatching($view, $query, $limit, $offset, $orderBy, $direction);
    }

    /**
     * @param string|null $view
     * @param string|null $query
     */
    public function numItems($view, $query) : int
    {
        return $this->publicationRepository->countWhereMatching($view, $query);
    }

    /**
     * @return mixed[]|null
     */
    public function numItemsPerView()
    {
        return $this->publicationRepository->getPublicationsPerStatus();
    }
}
