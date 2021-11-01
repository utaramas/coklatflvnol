<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication;

use Staatic\Framework\Build;
use Staatic\Framework\Deployment;

interface PublicationManagerInterface
{
    public function isPublicationInProgress() : bool;

    /**
     * @param mixed[] $metadata
     * @param Build|null $build
     * @param Deployment|null $deployment
     */
    public function createPublication($metadata = [], $build = null, $deployment = null) : Publication;

    /**
     * @param Publication $publication
     */
    public function claimPublication($publication) : bool;

    /**
     * @param Publication $publication
     * @return void
     */
    public function cancelPublication($publication);

    /**
     * @param Publication $publication
     * @return void
     */
    public function initiateBackgroundPublisher($publication);

    /**
     * @param Publication $publication
     * @return void
     */
    public function cancelBackgroundPublisher($publication);
}
