<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Advanced;

use Staatic\WordPress\Setting\AbstractSetting;

final class HttpAuthenticationPasswordSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_http_auth_password';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    protected function template() : string
    {
        return 'password';
    }

    public function label() : string
    {
        return __('Password', 'staatic');
    }

    /**
     * @param mixed[] $attributes
     * @return void
     */
    public function render($attributes = [])
    {
        parent::render(\array_merge([
            'disableAutocomplete' => \true
        ], $attributes));
    }
}
