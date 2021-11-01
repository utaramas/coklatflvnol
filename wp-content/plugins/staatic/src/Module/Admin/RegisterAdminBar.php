<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin;

use Staatic\Crawler\UrlTransformer\BasicUrlTransformer;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Service\Formatter;
use Staatic\WordPress\Setting\Build\DestinationUrlSetting;

final class RegisterAdminBar implements ModuleInterface
{
    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    /**
     * @var DestinationUrlSetting
     */
    private $destinationUrl;

    public function __construct(
        Formatter $formatter,
        PublicationRepository $publicationRepository,
        DestinationUrlSetting $destinationUrl
    )
    {
        $this->formatter = $formatter;
        $this->publicationRepository = $publicationRepository;
        $this->destinationUrl = $destinationUrl;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (is_network_admin()) {
            return;
        }
        // Load admin bar once WordPress, plugins and themes are loaded
        add_action('wp_loaded', [$this, 'loadAdminBar']);
    }

    /**
     * @return void
     */
    public function loadAdminBar()
    {
        add_action('admin_bar_menu', [$this, 'adminBarMenuSetup'], 90);
    }

    /**
     * @param \WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function adminBarMenuSetup($wp_admin_bar)
    {
        $currentPublicationId = get_option('staatic_current_publication_id');
        if ($currentPublicationId) {
            $title = \sprintf(
                '<span class="ab-icon staatic-loading staatic-spin"></span><span class="ab-label">%s</span>',
                __('Staatic', 'staatic')
            );
        } else {
            $title = \sprintf('<span class="ab-label">%s</span>', __('Staatic', 'staatic'));
        }
        $wp_admin_bar->add_node([
            'id' => 'staatic-toolbar',
            'title' => $title
        ]);
        if ($currentPublicationId) {
            $wp_admin_bar->add_node([
                'parent' => 'staatic-toolbar',
                'id' => 'staatic-toolbar-publish-status',
                'title' => __('Publication Status', 'staatic'),
                'href' => admin_url(\sprintf('admin.php?page=staatic-publication&id=%s', $currentPublicationId))
            ]);
            $wp_admin_bar->add_node([
                'parent' => 'staatic-toolbar',
                'id' => 'staatic-toolbar-publish-cancel',
                'title' => __('Cancel Publication', 'staatic'),
                'href' => wp_nonce_url(
                    admin_url(\sprintf('admin.php?page=staatic-publish&cancel=%s', $currentPublicationId)),
                    'staatic-publish_cancel'
                )
            ]);
        } else {
            $wp_admin_bar->add_node([
                'parent' => 'staatic-toolbar',
                'id' => 'staatic-toolbar-publish',
                'title' => __('Publish', 'staatic'),
                'href' => wp_nonce_url(admin_url('admin.php?page=staatic-publish'), 'staatic-publish')
            ]);
            if (!is_admin()) {
                $currentPublishedUrl = $this->getCurrentPublishedUrl();
                $wp_admin_bar->add_node([
                    'parent' => 'staatic-toolbar',
                    'id' => 'staatic-toolbar-view-page',
                    'title' => __('View on Static Site', 'staatic'),
                    'href' => $currentPublishedUrl,
                    'meta' => [
                        'target' => '_blank'
                        
                    ]]);
            }
            $activePublicationId = get_option('staatic_active_publication_id');
            if ($activePublicationId && ($activePublication = $this->publicationRepository->find(
                $activePublicationId
            ))) {
                $wp_admin_bar->add_node([
                    'parent' => 'staatic-toolbar',
                    'id' => 'staatic-toolbar-latest-publication',
                    'title' => __('Publication Details', 'staatic'),
                    'href' => admin_url(\sprintf('admin.php?page=staatic-publication&id=%s', $activePublication->id()))
                ]);
                $wp_admin_bar->add_node([
                    'parent' => 'staatic-toolbar',
                    'id' => 'staatic-toolbar-latest-publication-status',
                    'title' => \sprintf(
                                        /* translators: %s: Last publication date. */
                                        __('<em>Last Publication: %s</em>', 'staatic'),
                                        $this->formatter->shortDate($activePublication->dateCreated())
                                    )
                ]);
            }
        }
    }

    private function getCurrentPublishedUrl() : string
    {
        $urlTransformer = new BasicUrlTransformer(new Uri($this->destinationUrl->value()));
        return (string) $urlTransformer->transform(new Uri($this->getCurrentUrl()));
    }

    private function getCurrentUrl() : string
    {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)) {
            $protocol = 'https://';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}
