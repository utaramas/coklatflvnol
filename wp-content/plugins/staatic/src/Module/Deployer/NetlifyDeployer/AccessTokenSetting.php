<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\NetlifyDeployer;

use Staatic\WordPress\Setting\AbstractSetting;

final class AccessTokenSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_netlify_access_token';
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
        return __('Netlify Access Token', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return \sprintf(
            /* translators: %1$s: Link to Netlify Documentation. */
            __('You can find or create your Netlify (Personal) Access Token <a href="%1$s" target="_blank">here</a>.', 'staatic'),
            'https://app.netlify.com/user/applications#personal-access-tokens'
        );
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
