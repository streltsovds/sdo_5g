<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToStudents extends AbstractMigration

{
    public function change()
    {
        $table = $this->table('Students');
        if (!$table->hasColumn('newcomer_id'))
            $table->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'time_ended_planned',
            ]);

        if (!$table->hasColumn('reserve_id'))
            $table->addColumn('reserve_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'newcomer_id',
            ]);

        if (!$table->hasColumn('application_id'))
            $table->addColumn('application_id', 'integer', [
                'null' => true,
                'after' => 'reserve_id',
            ]);

        if (!$table->hasColumn('notified'))
            $table->addColumn('notified', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'application_id',
            ]);

        if (!$table->hasColumn('comment'))
            $table->addColumn('comment', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'notified',
            ]);

        if (!$table->hasColumn('programm_event_user_id'))
            $table->addColumn('programm_event_user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'comment',
            ]);

        if (!$table->hasColumn('begin_personal'))
            $table->addColumn('begin_personal', 'datetime', [
                'null' => true,
                'after' => 'programm_event_user_id',
            ]);

        if (!$table->hasColumn('end_personal'))
            $table->addColumn('end_personal', 'datetime', [
                'null' => true,
                'after' => 'begin_personal',
            ]);

        $table->update();
    }
}