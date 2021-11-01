<?php

declare(strict_types=1);

namespace Staatic\WordPress\ListTable\BulkAction;

final class BulkAction implements BulkActionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function label() : string
    {
        return $this->label;
    }
}
