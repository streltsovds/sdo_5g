<?php

use Phinx\Migration\AbstractMigration;

class ChangeConversationTypeConstant extends AbstractMigration
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
        $hasTypeIndex = $this->table('task_conversations')->hasIndex('type');

        if ($hasTypeIndex) {
            $this->table('task_conversations')
                ->removeIndex('type')
                ->update();
        }

        $this->table('task_conversations')
            ->changeColumn('type', 'string', [
                'collation' => 'utf8mb4_unicode_ci',
                'null' => false,
                'default' => '0',
                'limit' => 249,
            ])
            ->update();

        $this->table('task_conversations')
            ->addIndex(
                ['type'],
                [
                    'name' => 'type_idx',
                    'unique' => false,
                ]
            )
            ->update();

        $this->execute("update task_conversations set type='task' where type='0'");
        $this->execute("update task_conversations set type='question' where type='1'");
        $this->execute("update task_conversations set type='to_prove' where type='2'");
        $this->execute("update task_conversations set type='answer' where type='3'");
        $this->execute("update task_conversations set type='condition' where type='4'");
        $this->execute("update task_conversations set type='ball' where type='5'");
        $this->execute("update task_conversations set type='empty' where type='6'");
    }
}
