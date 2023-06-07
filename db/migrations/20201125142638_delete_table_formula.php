<?php

use Phinx\Migration\AbstractMigration;

class DeleteTableFormula extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('formula')->drop()->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    }
}
