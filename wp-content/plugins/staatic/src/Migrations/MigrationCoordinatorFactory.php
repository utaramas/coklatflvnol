<?php

declare(strict_types=1);

namespace Staatic\WordPress\Migrations;

final class MigrationCoordinatorFactory
{
    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $pluginVersion;

    public function __construct(\wpdb $wpdb, string $pluginVersion)
    {
        $this->wpdb = $wpdb;
        $this->pluginVersion = $pluginVersion;
    }

    public function __invoke(string $namespace, string $migrationsDir) : MigrationCoordinator
    {
        $migrator = new Migrator($this->wpdb, $migrationsDir);
        $targetVersion = $this->getTargetVersion($this->pluginVersion);
        return new MigrationCoordinator($migrator, $namespace, $targetVersion);
    }

    private function getTargetVersion() : string
    {
        if (\preg_match('~^(\\d+\\.\\d+\\.\\d+)~', $this->pluginVersion, $match) === 0) {
            throw new \RuntimeException(\sprintf('Plugin version has an invalid format: "%s"', $this->pluginVersion));
        }
        return $match[1];
    }
}
