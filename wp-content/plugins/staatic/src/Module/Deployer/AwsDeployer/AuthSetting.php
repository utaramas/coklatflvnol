<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\AwsDeployer;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ServiceLocator;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;
use Staatic\WordPress\Setting\ComposedSettingInterface;

final class AuthSetting extends AbstractSetting implements ComposedSettingInterface
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
        return 'staatic_aws_auth';
    }

    public function type() : string
    {
        return self::TYPE_COMPOSED;
    }

    public function label() : string
    {
        return __('Amazon Authentication', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return \sprintf(
            /* translators: %1$s: Link to AWS Documentation. */
            __('In order to authenticate using <a href="%1$s" target="blank">a credentials file and profile</a>, supply the name of the Profile (preferred),<br><strong>or</strong> in order to authenticate directly, supply the Access Key ID and Secret Access Key.', 'staatic'),
            'https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html',
            'https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_hardcoded.html'
        );
    }

    public function settings() : array
    {
        return [
            $this->locator->get(AuthProfileSetting::class),
            $this->locator->get(AuthAccessKeyIdSetting::class),
            $this->locator->get(AuthSecretAccessKeySetting::class)
        ];
    }
}
