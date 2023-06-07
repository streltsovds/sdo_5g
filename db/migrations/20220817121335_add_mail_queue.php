<?php

use Phinx\Migration\AbstractMigration;

class AddMailQueue extends AbstractMigration
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
        if (!$this->hasTable('mail_queue')) {
            $this->table('mail_queue')
                ->addColumn('subject', 'string', [
                    'null' => false,
                    'limit' => 1024,
                    'default' => '',
                ])
                ->addColumn('recipient', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'default' => '',
                ])
                ->addColumn('body', 'text', [
                    'null' => false,
                    'default' => '',
                ])
                ->addColumn('created', 'datetime', [
                ])
                ->addColumn('data', 'text', [
                    'null' => false,
                    'default' => '',
                ])
                ->addColumn('sended', 'integer', [
                    'null' => false,
                    'default' => 0,
                ])
                ->addIndex(['created'], [
                    'name' => 'created',
                    'unique' => false,
                ])
                ->create();
        }
    }
}
