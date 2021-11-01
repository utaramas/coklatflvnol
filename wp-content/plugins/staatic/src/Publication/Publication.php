<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication;

use Staatic\Framework\Build;
use Staatic\Framework\Deployment;

final class Publication
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTimeInterface
     */
    private $dateCreated;

    /**
     * @var Build
     */
    private $build;

    /**
     * @var Deployment
     */
    private $deployment;

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var mixed[]
     */
    private $metadata;

    /**
     * @var PublicationStatus
     */
    private $status;

    /**
     * @var \DateTimeInterface|null
     */
    private $dateFinished;

    /**
     * @var string|null
     */
    private $currentTask;

    /**
     * @param int|null $userId
     * @param \DateTimeInterface|null $dateFinished
     * @param string|null $currentTask
     */
    public function __construct(
        string $id,
        \DateTimeInterface $dateCreated,
        Build $build,
        Deployment $deployment,
        $userId = null,
        array $metadata = [],
        PublicationStatus $status = null,
        $dateFinished = null,
        $currentTask = null
    )
    {
        $this->id = $id;
        $this->dateCreated = $dateCreated;
        $this->build = $build;
        $this->deployment = $deployment;
        $this->userId = $userId;
        $this->metadata = $metadata;
        $this->status = $status ?? PublicationStatus::create(PublicationStatus::STATUS_PENDING);
        $this->dateFinished = $dateFinished;
        $this->currentTask = $currentTask;
    }

    public function id() : string
    {
        return $this->id;
    }

    public function dateCreated() : \DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function build() : Build
    {
        return $this->build;
    }

    public function deployment() : Deployment
    {
        return $this->deployment;
    }

    /**
     * @return int|null
     */
    public function userId()
    {
        return $this->userId;
    }

    /**
     * @return \WP_User|null
     */
    public function publisher()
    {
        if (!$this->userId) {
            return null;
        }
        $user = get_userdata($this->userId);
        return $user ?: null;
    }

    public function metadata() : array
    {
        return $this->metadata;
    }

    public function status() : PublicationStatus
    {
        return $this->status;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function dateFinished()
    {
        return $this->dateFinished;
    }

    /**
     * @return string|null
     */
    public function currentTask()
    {
        return $this->currentTask;
    }

    /**
     * @return void
     */
    public function setStatus(PublicationStatus $status)
    {
        $this->status = $status;
    }

    /**
     * @param string|null $currentTask
     * @return void
     */
    public function setCurrentTask($currentTask)
    {
        $this->currentTask = $currentTask;
    }

    /**
     * @return void
     */
    public function markInProgress()
    {
        $this->status = PublicationStatus::create(PublicationStatus::STATUS_IN_PROGRESS);
    }

    /**
     * @return void
     */
    public function markCanceled()
    {
        $this->currentTask = null;
        $this->status = PublicationStatus::create(PublicationStatus::STATUS_CANCELED);
        $this->dateFinished = new \DateTimeImmutable();
    }

    /**
     * @return void
     */
    public function markFailed()
    {
        $this->currentTask = null;
        $this->status = PublicationStatus::create(PublicationStatus::STATUS_FAILED);
        $this->dateFinished = new \DateTimeImmutable();
    }

    /**
     * @return void
     */
    public function markFinished()
    {
        $this->currentTask = null;
        $this->status = PublicationStatus::create(PublicationStatus::STATUS_FINISHED);
        $this->dateFinished = new \DateTimeImmutable();
    }

    /**
     * @return void
     */
    public function updateMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }
}
