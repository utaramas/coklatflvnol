<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page\PublicationResults;

use Staatic\WordPress\Bridge\ResultRepository;
use Staatic\WordPress\ListTable\AbstractListTable;
use Staatic\WordPress\ListTable\Column\BytesColumn;
use Staatic\WordPress\ListTable\Column\DateColumn;
use Staatic\WordPress\ListTable\Column\NumberColumn;
use Staatic\WordPress\ListTable\Column\TextColumn;
use Staatic\WordPress\ListTable\Decorator\LinkDecorator;
use Staatic\WordPress\ListTable\View\View;
use Staatic\WordPress\Service\Formatter;

class PublicationResultsTable extends AbstractListTable
{
    /** @var string */
    const NAME = 'result_list_table';

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var ResultRepository
     */
    private $repository;

    public function __construct(Formatter $formatter, ResultRepository $repository)
    {
        parent::__construct('id', ['date_created', 'DESC']);
        $this->formatter = $formatter;
        $this->repository = $repository;
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
    }

    /**
     * @return void
     */
    private function setupColumns()
    {
        $this->addColumns(
            [new NumberColumn(
                $this->formatter,
                'status_code',
                __('HTTP Status Code', 'staatic')
            ), new TextColumn('url', __('URL', 'staatic'), [
            'decorators' => [new LinkDecorator(function ($item) {
                        return $item->url()->getAuthority() ? (string) $item->url() : null;
                    }, \true)]
        ]), new TextColumn('redirect_url', __('Redirect URL', 'staatic'), [
            'decorators' => [new LinkDecorator(function ($item) {
                        return $item->redirectUrl() ? (string) $item->redirectUrl() : null;
                    }, \true)]
        ]), new TextColumn(
            'mime_type',
            __('Mime Type', 'staatic')
        ), new BytesColumn($this->formatter, 'size', __('Size', 'staatic'), [
            'decorators' => [new LinkDecorator(function ($item) {
                        return admin_url(\sprintf('admin.php?staatic=result-download&id=%s', $item->id()));
                    })]
        ]), new TextColumn('original_found_on_url', __('Found On URL', 'staatic'), [
            'decorators' => [new LinkDecorator(function ($item) {
                        return $item->originalFoundOnUrl() ? (string) $item->originalFoundOnUrl() : null;
                    }, \true)]
        ]), new DateColumn($this->formatter, 'date_created', __('Found On Date', 'staatic'))]
        );
    }

    /**
     * @return void
     */
    public function setupViews()
    {
        $statusCategories = [
            1 => __('1xx Informational', 'staatic'),
            2 => __('2xx Success', 'staatic'),
            3 => __('3xx Redirection', 'staatic'),
            4 => __('4xx Client Errors', 'staatic'),
            5 => __('5xx Server Errors', 'staatic')
        ];
        foreach ($statusCategories as $name => $label) {
            $this->addView(new View((string) $name, $label));
        }
    }

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
        return $this->repository->findWhereMatching(
            $this->arguments['buildId'],
            $view ? (int) $view : null,
            $query,
            $limit,
            $offset,
            $orderBy,
            $direction
        );
    }

    /**
     * @param string|null $view
     * @param string|null $query
     */
    public function numItems($view, $query) : int
    {
        return $this->repository->countWhereMatching($this->arguments['buildId'], $view ? (int) $view : null, $query);
    }

    /**
     * @return mixed[]|null
     */
    public function numItemsPerView()
    {
        return $this->repository->getResultsPerStatusCategory($this->arguments['buildId']);
    }
}
