<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Setting\AbstractSetting;

final class AdditionalRedirectsSetting extends AbstractSetting
{
    /**
     * @var DestinationUrlSetting
     */
    protected $destinationUrl;

    public function __construct(PartialRenderer $renderer, DestinationUrlSetting $destinationUrl)
    {
        parent::__construct($renderer);
        $this->destinationUrl = $destinationUrl;
    }

    public function name() : string
    {
        return 'staatic_additional_redirects';
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
        return __('Additional Redirects', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return \sprintf(
            /* translators: %s: Example additional redirects. */
            __('Optionally add redirects that need to be included in the build (one redirect per line, in the format PATH REDIRECT_URL HTTP_STATUS_CODE).<br>Examples: %s.', 'staatic'),
            \implode(
                ', ',
                ['<code>/old-post /new-post 301</code>',
                '<code>/some-other-post https://othersite.example/some-other-post 302</code>'
            ])
        );
    }

    public function sanitizeValue($value)
    {
        $destinationUrl = $this->destinationUrl->value();
        $destinationUrlAuthority = (new Uri($destinationUrl))->getAuthority();
        $additionalRedirects = [];
        foreach (\explode("\n", $value) as $additionalRedirect) {
            $additionalRedirect = \trim($additionalRedirect);
            if (!$additionalRedirect || \substr($additionalRedirect, 0, 1) === '#') {
                $additionalRedirects[] = $additionalRedirect;
                continue;
            }
            list($path, $redirectUrl, $statusCode) = \array_pad(\explode(' ', $additionalRedirect, 3), 3, null);
            $pathAuthority = (new Uri($path))->getAuthority();
            if ($pathAuthority && $pathAuthority !== $destinationUrlAuthority) {
                add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: %s: Redirect path. */
                    __('The supplied additional redirect with path "%s" is not part of this site and therefore skipped', 'staatic'),
                    $path
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);
                continue;
            }
            if ($statusCode && !\in_array($statusCode, [301, 302, 307, 308])) {
                add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: %1$s: Redirect path, %2$s: HTTP status code. */
                    __('The supplied additional redirect with path "%1$s" has an invalid HTTP status code "%2$s" and therefore skipped', 'staatic'),
                    $path,
                    $statusCode
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);
                continue;
            }
            if (!\in_array($additionalRedirect, $additionalRedirects)) {
                $additionalRedirects[] = $additionalRedirect;
            }
        }
        return \implode("\n", $additionalRedirects);
    }

    /**
     * @param string|null $value
     * @param UriInterface $destinationUrl
     */
    public static function resolvedValue($value, $destinationUrl) : array
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        $destinationUrlAuthority = $destinationUrl->getAuthority();
        foreach (\explode("\n", $value) as $additionalRedirect) {
            if (!$additionalRedirect || \substr($additionalRedirect, 0, 1) === '#') {
                continue;
            }
            list($path, $redirectUrl, $statusCode) = \array_pad(\explode(' ', $additionalRedirect, 3), 3, null);
            $pathAuthority = (new Uri($path))->getAuthority();
            // Check path once again to take change of destination URL into account.
            if ($pathAuthority && !$destinationUrlAuthority) {
                continue;
            } elseif ($pathAuthority && $pathAuthority !== $destinationUrlAuthority) {
                continue;
            }
            $redirectUrl = new Uri($redirectUrl);
            if (!$redirectUrl->getAuthority()) {
                $redirectUrl = UriResolver::resolve($destinationUrl, $redirectUrl);
            }
            $statusCode = $statusCode ? (int) $statusCode : 302;
            $resolvedValue[$path] = \compact('redirectUrl', 'statusCode');
        }
        return $resolvedValue;
    }
}
