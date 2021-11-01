<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication;

use Staatic\Framework\Build;
use Staatic\Framework\Deployment;
use Staatic\WordPress\Factory\BuildFactory;
use Staatic\WordPress\Factory\DeploymentFactory;

final class PublicationManager implements PublicationManagerInterface
{
    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    /**
     * @var BackgroundPublisher
     */
    private $backgroundPublisher;

    /**
     * @var BuildFactory
     */
    private $buildFactory;

    /**
     * @var DeploymentFactory
     */
    private $deploymentFactory;

    public function __construct(
        PublicationRepository $publicationRepository,
        BackgroundPublisher $backgroundPublisher,
        BuildFactory $buildFactory,
        DeploymentFactory $deploymentFactory
    )
    {
        $this->publicationRepository = $publicationRepository;
        $this->backgroundPublisher = $backgroundPublisher;
        $this->buildFactory = $buildFactory;
        $this->deploymentFactory = $deploymentFactory;
    }

    public function isPublicationInProgress() : bool
    {
        return (bool) get_option('staatic_current_publication_id');
    }

    /**
     * @param mixed[] $metadata
     * @param Build|null $build
     * @param Deployment|null $deployment
     */
    public function createPublication($metadata = [], $build = null, $deployment = null) : Publication
    {
        $build = $build ?? $this->buildFactory->create();
        $deployment = $deployment ?? $this->deploymentFactory->create($build->id());
        $publication = new Publication(
            $this->publicationRepository->nextId(),
            new \DateTimeImmutable(),
            $build,
            $deployment,
            get_current_user_id() ?: null,
            $metadata
        );
        $this->publicationRepository->add($publication);
        return $publication;
    }

    /**
     * @param Publication $publication
     */
    public function claimPublication($publication) : bool
    {
        if (get_option('staatic_current_publication_id')) {
            return \false;
        }
        update_option('staatic_current_publication_id', $publication->id());
        update_option('staatic_latest_publication_id', $publication->id());
        return \true;
    }

    /**
     * @param Publication $publication
     * @return void
     */
    public function cancelPublication($publication)
    {
        $publication->markCanceled();
        $this->publicationRepository->update($publication);
    }

    /**
     * @param Publication $publication
     * @return void
     */
    public function initiateBackgroundPublisher($publication)
    {
        $this->backgroundPublisher->initiate($publication);
    }

    /**
     * @param Publication $publication
     * @return void
     */
    public function cancelBackgroundPublisher($publication)
    {
        $this->backgroundPublisher->cancel($publication);
    }
}
