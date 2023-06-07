<?php

use Phinx\Migration\AbstractMigration;

class AddMobileFieldsToTables extends AbstractMigration
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
        if (!$this->table('People')->hasColumn('push_token')) {
            $this->table('People')
                ->addColumn(
                    'push_token', 'string', [
                    'null' => false,
                    'default' => '',
                ])
                ->update();
        }

        if (!$this->table('news')->hasColumn('mobile')) {
            $this->table('news')
                ->addColumn(
                    'mobile', 'integer', [
                    'null' => true,
                    'default' => '0',
                ])
                ->update();
        }

        if (!$this->table('messages')->hasColumn('readed')) {
            $this->table('messages')
                ->addColumn(
                    'readed', 'integer', [
                    'null' => true,
                    'default' => '0',
                ])
                ->update();
        }

        if (!$this->table('support_requests')->hasColumn('file_id')) {
            $this->table('support_requests')
                ->addColumn(
                    'file_id', 'integer', [
                    'null' => true,
                ])
                ->update();
        }

    }
}
