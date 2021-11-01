<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\AwsDeployer;

use Staatic\WordPress\Setting\AbstractSetting;

final class S3BucketSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_aws_s3_bucket';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return __('Bucket', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('The name of the S3 bucket to store the static site\'s data.', 'staatic');
    }
}
