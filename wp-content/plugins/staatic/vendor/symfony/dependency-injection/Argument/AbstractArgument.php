<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Argument;

final class AbstractArgument
{
    private $text;
    private $context;
    public function __construct(string $text = '')
    {
        $this->text = \trim($text, '. ');
    }
    /**
     * @return void
     */
    public function setContext(string $context)
    {
        $this->context = $context . ' is abstract' . ('' === $this->text ? '' : ': ');
    }
    public function getText() : string
    {
        return $this->text;
    }
    public function getTextWithContext() : string
    {
        return $this->context . $this->text . '.';
    }
}
