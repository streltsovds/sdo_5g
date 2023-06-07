<?php

use Phinx\Migration\AbstractMigration;

class DeleteTableTimesheets extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('timesheets')->drop()->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
