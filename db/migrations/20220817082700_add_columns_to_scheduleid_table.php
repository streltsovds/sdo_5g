<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToScheduleidTable extends AbstractMigration
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
        if (!$this->table('scheduleID')->hasColumn('passed_proctoring')) {
            $this->table('scheduleID')
                ->addColumn('passed_proctoring', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->update();
        }
        if (!$this->table('scheduleID')->hasColumn('video_proctoring')) {
            $this->table('scheduleID')
                ->addColumn('video_proctoring', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->update();
        }
        if (!$this->table('scheduleID')->hasColumn('file_id')) {
            $this->table('scheduleID')
                ->addColumn('file_id', 'integer', [
                    'null' => true,
                ])
                ->update();
        }
        if (!$this->table('scheduleID')->hasColumn('remote_event_id')) {
            $this->table('scheduleID')
                ->addColumn('remote_event_id', 'integer', [
                    'null' => true,
                ])
                ->update();
        }

    }
}
