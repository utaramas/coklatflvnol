<?php

declare(strict_types=1);

namespace Staatic\WordPress\Migrations;

final class MigrationCoordinator
{
    const MIGRATION_OPTION_NAME = '%s_database_version';

    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $targetVersion;

    /**
     * @var mixed[]|null
     */
    private $status;

    public function __construct(Migrator $migrator, string $namespace, string $targetVersion)
    {
        $this->migrator = $migrator;
        $this->namespace = $namespace;
        $this->targetVersion = $targetVersion;
    }

    public function status() : array
    {
        if ($this->status === null) {
            $this->status = get_option($this->optionName());
            if (\is_string($this->status) && !empty($this->status)) {
                $this->status = [
                    'version' => $this->status
                ];
            } elseif (!\is_array($this->status) || !isset($this->status['version'])) {
                $this->status = [
                    'version' => '0.0.0'
                ];
            }
        }
        return $this->status;
    }

    public function isMigrating() : bool
    {
        $status = $this->status();
        if (!\array_key_exists('lock', $status)) {
            return \false;
        }
        return \strtotime('-30 minutes') <= $status['lock'];
    }

    public function shouldMigrate() : bool
    {
        $status = $this->status();
        if ($this->isMigrating()) {
            return \false;
        }
        return \version_compare($status['version'], $this->targetVersion, '<');
    }

    public function migrate() : bool
    {
        if (!$this->lockMigration()) {
            return \false;
        }
        $status = $this->status();
        $installedVersion = $status['version'];
        try {
            $this->migrator->migrate($this->targetVersion, $installedVersion);
        } catch (\Exception $e) {
            $this->migrationFailed($e->getMessage());
            return \false;
        }
        $this->migrationSuccessful();
        return \true;
    }

    /**
     * @return void
     */
    private function migrationSuccessful()
    {
        $status = $this->status();
        unset($status['lock'], $status['error']);
        $status['version'] = $this->targetVersion;
        $this->setStatus($status);
    }

    /**
     * @return void
     */
    private function migrationFailed(string $message)
    {
        $status = $this->status();
        $status['error'] = [
            'time' => \time(),
            'version' => $this->targetVersion,
            'message' => $message
        ];
        $this->setStatus($status);
    }

    private function lockMigration() : bool
    {
        $status = $this->status();
        $status['lock'] = \time();
        return $this->setStatus($status);
    }

    private function setStatus($status) : bool
    {
        $this->status = $status;
        return update_option($this->optionName(), $status);
    }

    private function optionName() : string
    {
        return \sprintf(self::MIGRATION_OPTION_NAME, $this->namespace);
    }
}
