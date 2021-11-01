<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\AwsDeployer;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;
use Staatic\WordPress\Setting\ComposedSettingInterface;

final class S3Setting extends AbstractSetting implements ComposedSettingInterface
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
        return 'staatic_aws_s3';
    }

    public function type() : string
    {
        return self::TYPE_COMPOSED;
    }

    public function label() : string
    {
        return __('Amazon S3', 'staatic');
    }

    public function settings() : array
    {
        return [$this->locator->get(S3BucketSetting::class), $this->locator->get(S3PrefixSetting::class)];
    }
}
