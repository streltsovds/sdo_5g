<?php

use Phinx\Migration\AbstractMigration;

class CreateTableTimesheets extends AbstractMigration
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
        $this->table('timesheets', [
            'id' => false,
            'primary_key' => ['timesheet_id'],
            'engine' => 'MyISAM',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('timesheet_id', 'integer', [
                'null' => false,
                'identity' => 'enable'
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'timesheet_id',
            ])
            ->addColumn('action_type', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'action_type',
            ])
            ->addColumn('action_date', 'datetime', [
                'null' => true,
                'after' => 'description',
            ])
            ->addColumn('begin_time', 'time', [
                'null' => true,
                'after' => 'action_date',
            ])
            ->addColumn('end_time', 'time', [
                'null' => true,
                'after' => 'begin_time',
            ])
            ->create();
    }
}
