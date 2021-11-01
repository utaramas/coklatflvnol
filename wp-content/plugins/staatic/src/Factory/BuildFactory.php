<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Framework\Build;
use Staatic\Framework\BuildRepository\BuildRepositoryInterface;
use Staatic\WordPress\Setting\Build\DestinationUrlSetting;

final class BuildFactory
{
    /**
     * @var BuildRepositoryInterface
     */
    private $buildRepository;

    /**
     * @var DestinationUrlSetting
     */
    private $destinationUrl;

    public function __construct(BuildRepositoryInterface $buildRepository, DestinationUrlSetting $destinationUrl)
    {
        $this->buildRepository = $buildRepository;
        $this->destinationUrl = $destinationUrl;
    }

    /**
     * @param string|null $parentBuildId
     * @param string|null $entryUrl
     */
    public function create($parentBuildId = null, $entryUrl = null) : Build
    {
        if ($entryUrl === null) {
            $entryUrl = site_url('/');
        }
        $build = new Build($this->buildRepository->nextId(), new Uri($entryUrl), new Uri(
            $this->destinationUrl->value()
        ), $parentBuildId);
        $this->buildRepository->add($build);
        return $build;
    }
}
