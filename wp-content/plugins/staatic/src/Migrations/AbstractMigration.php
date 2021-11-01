<?php

declare(strict_types=1);

namespace Staatic\WordPress\Migrations;

abstract class AbstractMigration implements MigrationInterface
{
    /**
     * @param \wpdb $wpdb
     * @param string $query
     */
    protected function query($wpdb, $query)
    {
        $result = $wpdb->query($query);
        if ($result === \false) {
            throw new \RuntimeException(\sprintf('Unable to execute query: "%s": %s', $query, $wpdb->last_error));
        }
        return $result;
    }
}
