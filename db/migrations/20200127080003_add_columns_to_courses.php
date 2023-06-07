<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToCourses extends AbstractMigration

{
    public function change()
    {
        $table = $this->table('Courses');
        if (!$table->hasColumn('subject_id'))
            $table->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'extra_navigation',
            ]);

        if (!$table->hasColumn('entry_point'))
            $table->addColumn('entry_point', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ]);

        if (!$table->hasColumn('activity_id'))
            $table->addColumn('activity_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entry_point',
            ]);

        $table->update();
    }
}