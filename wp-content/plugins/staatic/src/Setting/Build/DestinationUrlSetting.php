<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\WordPress\Setting\AbstractSetting;

final class DestinationUrlSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_destination_url';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return __('Destination URL', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('This should be the URL of your published website, e.g. https://www.domain.com.', 'staatic');
    }

    public function defaultValue()
    {
        return site_url('/');
    }
}
