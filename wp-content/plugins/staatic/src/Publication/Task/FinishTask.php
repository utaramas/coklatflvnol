<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication\Task;

use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Publication\PublicationRepository;

final class FinishTask implements TaskInterface
{
    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    public function __construct(PublicationRepository $publicationRepository)
    {
        $this->publicationRepository = $publicationRepository;
    }

    public function name() : string
    {
        return 'finish';
    }

    public function description() : string
    {
        return __('Finishing', 'staatic');
    }

    /**
     * @param Publication $publication
     */
    public function execute($publication) : bool
    {
        $publication->markFinished();
        $this->publicationRepository->update($publication);
        update_option('staatic_active_publication_id', $publication->id());
        update_option('staatic_current_publication_id', '');
        return \true;
    }
}
