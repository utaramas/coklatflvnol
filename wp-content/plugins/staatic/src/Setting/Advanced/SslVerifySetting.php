<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Advanced;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;
use Staatic\WordPress\Setting\ComposedSettingInterface;

final class SslVerifySetting extends AbstractSetting implements ComposedSettingInterface
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
        return 'staatic_ssl_verify';
    }

    public function type() : string
    {
        return self::TYPE_COMPOSED;
    }

    public function label() : string
    {
        return __('SSL Certificates', 'staatic');
    }

    public function settings() : array
    {
        return [$this->locator->get(SslVerifyBehaviorSetting::class), $this->locator->get(SslVerifyPathSetting::class)];
    }
}
