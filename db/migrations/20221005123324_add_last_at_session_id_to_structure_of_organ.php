<?php

use Phinx\Migration\AbstractMigration;

class AddLastAtSessionIdToStructureOfOrgan extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        if (!$this->table('structure_of_organ')->hasColumn('last_at_session_id')) {
            $this->table('structure_of_organ')
                ->addColumn('last_at_session_id', 'integer', [
                    'null' => false,
                    'default' => 0,
                ])
                ->update();
        }
    }
}
