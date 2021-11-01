<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Advanced;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;
use Staatic\WordPress\Setting\ComposedSettingInterface;

final class HttpAuthenticationSetting extends AbstractSetting implements ComposedSettingInterface
{
    /**
     * @var ServiceLocator
     */
    private $locator;

    public function __construct(PartialRenderer $renderer, ServiceLocator $settingLocator)
    {
        parent::__construct($renderer);
        $this->locator = $settingLocator;
    }

    public function name() : string
    {
        return 'staatic_http_auth';
    }

    public function type() : string
    {
        return self::TYPE_COMPOSED;
    }

    public function label() : string
    {
        return __('HTTP Authentication', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('If your WordPress installation is protected with HTTP authentication, enter the relevant credentials.', 'staatic');
    }

    public function settings() : array
    {
        return [
            $this->locator->get(HttpAuthenticationUsernameSetting::class),
            $this->locator->get(HttpAuthenticationPasswordSetting::class)
        ];
    }
}
