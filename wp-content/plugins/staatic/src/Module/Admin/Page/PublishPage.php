<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page;

use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\PublicationManager;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Service\AdminNavigation;
use Staatic\WordPress\Service\PartialRenderer;

final class PublishPage implements ModuleInterface
{
    use FlashMessageTrait;

    /**
     * @var AdminNavigation
     */
    private $navigation;

    /**
     * @var PartialRenderer
     */
    private $renderer;

    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    /**
     * @var PublicationManager
     */
    private $publicationManager;

    public function __construct(
        AdminNavigation $navigation,
        PartialRenderer $renderer,
        PublicationRepository $publicationRepository,
        PublicationManager $publicationManager
    )
    {
        $this->navigation = $navigation;
        $this->renderer = $renderer;
        $this->publicationRepository = $publicationRepository;
        $this->publicationManager = $publicationManager;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (!is_admin()) {
            return;
        }
        add_action('init', [$this, 'addMenuItem']);
    }

    /**
     * @return void
     */
    public function addMenuItem()
    {
        $this->navigation->addPage(
            __('Publish', 'staatic'),
            'staatic-publish',
            [$this, 'render'],
            'edit_posts',
            'staatic-publications'
        );
    }

    /**
     * @return void
     */
    public function render()
    {
        $title = __('Publish', 'staatic');
        $message = null;
        $redirectUrl = null;
        if (!empty($_REQUEST['cancel'])) {
            check_admin_referer('staatic-publish_cancel');
            $publicationId = sanitize_key($_REQUEST['cancel']);
            if (!($publication = $this->publicationRepository->find($publicationId))) {
                update_option('staatic_current_publication_id', null);
                wp_die(\sprintf(
                    /* translators: %s: Publication ID. */
                    __('Publication (#%s) not found', 'staatic'),
                    $publicationId
                ));
            }
            //!TODO: what if ran from CLI command?
            $this->publicationManager->cancelBackgroundPublisher($publication);
            $message = __('The publication will be canceled as soon as possible.', 'staatic');
            $this->renderFlashMessage($title, $message, $redirectUrl);
            return;
        }
        check_admin_referer('staatic-publish');
        if (!$this->publicationManager->isPublicationInProgress()) {
            $publication = $this->publicationManager->createPublication();
            if ($this->publicationManager->claimPublication($publication)) {
                $this->publicationManager->initiateBackgroundPublisher($publication);
                $message = __('A new publication will be started and deployed automatically.', 'staatic');
                $redirectUrl = admin_url(\sprintf('admin.php?page=staatic-publication&id=%s', $publication->id()));
            } else {
                $this->publicationManager->cancelPublication($publication);
                $message = __('Publication could not be started because another publication is pending.', 'staatic');
            }
        } else {
            $message = __('Publication could not be started because another publication is pending.', 'staatic');
        }
        $this->renderFlashMessage($title, $message, $redirectUrl);
    }
}
