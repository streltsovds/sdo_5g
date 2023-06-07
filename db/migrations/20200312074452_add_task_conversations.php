<?php

use Phinx\Migration\AbstractMigration;

class AddTaskConversations extends AbstractMigration
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
        if (!$this->hasTable('task_conversations')) {
            $this->table('task_conversations', [
                'id' => false,
                'primary_key' => ['conversation_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('conversation_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('teacher_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('type', 'integer', [
                    'null' => false,
                ])
                ->addColumn('variant_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->create();
        }
    }
}
