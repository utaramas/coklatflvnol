<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Advanced;

use Staatic\WordPress\Setting\AbstractSetting;

final class SslVerifyPathSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_ssl_verify_path';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return __('CA Bundle Path', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('In case "Verification" is set to "Enabled using custom certificate" this should be the path to the certificate, e.g. "/path/to/cert.pem".', 'staatic');
    }

    public function sanitizeValue($value)
    {
        if ($value && !\realpath($value)) {
            add_settings_error('staatic-settings', 'invalid_ssl_verify_path', \sprintf(
                /* translators: %s: Supplied CA bundle path. */
                __('The supplied CA Bundle Path "%s" does not exist', 'staatic'),
                $value
            ));
        }
        return $value;
    }
}
