<?php

use Phinx\Migration\AbstractMigration;

class TaskIndexes extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('task_conversations');
        if (!$table->hasIndex(['lesson_id'])) {
            $table->addIndex(['lesson_id'],
                [
                    'name' => 'lesson_id_idx'
                ]);
        }

        if (!$table->hasIndex(['user_id'])) {
            $table->addIndex(['user_id'],
                [
                    'name' => 'user_id_idx'
                ]);
        }

        if (!$table->hasIndex(['variant_id'])) {
            $table->addIndex(['variant_id'],
                [
                    'name' => 'variant_id_idx'
                ]);
        }

        if (!$table->hasIndex(['date'])) {
            $table->addIndex(['date'],
                [
                    'name' => 'date_idx'
                ]);
        }

        $table->update();

    }

}
