<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting;

use Staatic\WordPress\Service\PartialRenderer;

abstract class AbstractSetting implements SettingInterface
{
    /**
     * @var PartialRenderer
     */
    protected $renderer;

    public function __construct(PartialRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return string|null
     */
    public function extendedLabel()
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return null;
    }

    public function value()
    {
        return get_option($this->name(), $this->defaultValue());
    }

    public function defaultValue()
    {
        switch ($this->type()) {
            case self::TYPE_BOOLEAN:
                return \false;
            case self::TYPE_ARRAY:
                return [];
            default:
                return null;
        }
    }

    public function sanitizeValue($value)
    {
        return $value;
    }

    protected function template() : string
    {
        return $this->type();
    }

    /**
     * @param mixed[] $attributes
     * @return void
     */
    public function render($attributes = [])
    {
        $path = \sprintf('admin/settings/%s.php', $this->template());
        $this->renderer->render($path, [
            'setting' => $this,
            'attributes' => $attributes
        ]);
    }
}
