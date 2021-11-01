<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module;

use Staatic\WordPress\Setting\Advanced\HttpAuthenticationPasswordSetting;
use Staatic\WordPress\Setting\Advanced\HttpAuthenticationUsernameSetting;

final class HttpAuthHeaders implements ModuleInterface
{
    /**
     * @var HttpAuthenticationUsernameSetting
     */
    private $httpAuthUsername;

    /**
     * @var HttpAuthenticationPasswordSetting
     */
    private $httpAuthPassword;

    public function __construct(
        HttpAuthenticationUsernameSetting $httpAuthUsername,
        HttpAuthenticationPasswordSetting $httpAuthPassword
    )
    {
        $this->httpAuthUsername = $httpAuthUsername;
        $this->httpAuthPassword = $httpAuthPassword;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        add_filter('cron_request', [$this, 'updateCronRequest'], 10);
        add_filter('staatic_background_publisher_post_args', [$this, 'updatePostArgs'], 10);
    }

    /**
     * @param mixed[] $cronRequest
     */
    public function updateCronRequest($cronRequest) : array
    {
        if (!$this->httpAuthUsername->value() && !$this->httpAuthPassword->value()) {
            return $cronRequest;
        }
        $cronRequest['args']['headers']['Authorization'] = \sprintf(
            'Basic %s',
            \base64_encode(\sprintf('%s:%s', $this->httpAuthUsername->value(), $this->httpAuthPassword->value()))
        );
        return $cronRequest;
    }

    /**
     * @param mixed[] $args
     */
    public function updatePostArgs($args) : array
    {
        if (!$this->httpAuthUsername->value() && !$this->httpAuthPassword->value()) {
            return $args;
        }
        $args['headers']['Authorization'] = \sprintf(
            'Basic %s',
            \base64_encode(\sprintf('%s:%s', $this->httpAuthUsername->value(), $this->httpAuthPassword->value()))
        );
        return $args;
    }
}
