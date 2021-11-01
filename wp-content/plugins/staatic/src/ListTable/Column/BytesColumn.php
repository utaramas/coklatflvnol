<?php

declare(strict_types=1);

namespace Staatic\WordPress\ListTable\Column;

use Staatic\WordPress\Service\Formatter;

final class BytesColumn extends AbstractColumn
{
    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(Formatter $formatter, string $name, string $label, array $arguments = [])
    {
        parent::__construct($name, $label, $arguments);
        $this->formatter = $formatter;
    }

    public function defaultSortDirection() : string
    {
        return 'DESC';
    }

    /**
     * @return void
     */
    public function render($item)
    {
        $value = $this->itemValue($item);
        if ($value) {
            $result = $this->formatter->bytes($value);
            echo $this->applyDecorators($result, $item);
        } else {
            echo '-';
        }
    }
}
