<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\WordPress\Setting\AbstractSetting;

final class AdditionalUrlsSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_additional_urls';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    protected function template() : string
    {
        return 'textarea';
    }

    public function label() : string
    {
        return __('Additional URLs', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return \sprintf(
            /* translators: %s: Example additional URLs. */
            __('Optionally add URLs that need to be included in the build (one URL per line)<br>Example: <code>%s</code>.', 'staatic'),
            '/sitemap.xml'
        );
    }

    public function defaultValue()
    {
        return \implode("\n", ['/robots.txt', '/sitemap.xml']);
    }

    public function sanitizeValue($value)
    {
        $siteUrl = new Uri(site_url());
        $additionalUrls = [];
        foreach (\explode("\n", $value) as $additionalUrl) {
            $additionalUrl = \trim($additionalUrl);
            if (!$additionalUrl || \substr($additionalUrl, 0, 1) === '#') {
                $additionalUrls[] = $additionalUrl;
                continue;
            }
            $authority = (new Uri($additionalUrl))->getAuthority();
            if ($authority && $authority !== $siteUrl->getAuthority()) {
                add_settings_error('staatic-settings', 'invalid_additional_url', \sprintf(
                    /* translators: %s: Supplied additional URL. */
                    __('The supplied additional URL "%s" is not part of this site and therefore skipped', 'staatic'),
                    $additionalUrl
                ));
                $additionalUrls[] = \sprintf('#%s', $additionalUrl);
                continue;
            }
            if (!\in_array($additionalUrl, $additionalUrls)) {
                $additionalUrls[] = $additionalUrl;
            }
        }
        return \implode("\n", $additionalUrls);
    }
}
