<?php

declare(strict_types=1);

namespace Staatic\Vendor;

use Staatic\WordPress\Migrations\MigrationInterface;

return new class implements MigrationInterface {
    /**
     * @param \wpdb $wpdb
     * @return void
     */
    public function up($wpdb)
    {
        $role = get_role('administrator');
        $role->add_cap('staatic_manage_settings', \true);
    }

    /**
     * @param \wpdb $wpdb
     * @return void
     */
    public function down($wpdb)
    {
        $role = get_role('administrator');
        $role->remove_cap('staatic_manage_settings');
    }
};
