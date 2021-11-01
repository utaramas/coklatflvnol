<?php

declare(strict_types=1);

namespace Staatic\WordPress\ListTable\Column;

use Staatic\WordPress\Service\Formatter;

final class IdentifierColumn extends AbstractColumn
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

    /**
     * @return void
     */
    public function render($item)
    {
        $value = $this->itemValue($item);
        $result = $this->formatter->identifier($value);
        echo $this->applyDecorators($result, $item);
    }
}
