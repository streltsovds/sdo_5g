<?php

use Phinx\Migration\AbstractMigration;

class AddChat extends AbstractMigration
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
        if (!$this->hasTable('chat_messages')) {
            $this->table('chat_messages', [
                'id' => false,
                'primary_key' => ['message_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('message_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('room_type', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('room_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('created_at', 'datetime', [
                    'null' => true,
                ])
                ->create();
        }

        if (!$this->hasTable('chat_message_items')) {
            $this->table('chat_message_items', [
                'id' => false,
                'primary_key' => ['message_item_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('message_item_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('matched_text', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('type', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('message_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                ])
                ->create();
        }
    }
}
