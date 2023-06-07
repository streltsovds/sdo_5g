<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToForumsSections extends AbstractMigration

{
    public function change()
    {
        $table = $this->table('forums_sections');
        if (!$table->hasColumn('subject'))
            $table->addColumn('subject', 'string', [
                'null' => true,
                'default' => 'subject',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lesson_id',
            ]);

        if (!$table->hasColumn('deleted_by'))
            $table->addColumn('deleted_by', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'is_hidden',
            ]);

        if (!$table->hasColumn('deleted'))
            $table->addColumn('deleted', 'datetime', [
                'null' => true,
                'after' => 'deleted_by',
            ]);

        if (!$table->hasColumn('edited_by'))
            $table->addColumn('edited_by', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'deleted',
            ]);

        if (!$table->hasColumn('edited'))
            $table->addColumn('edited', 'datetime', [
                'null' => true,
                'after' => 'edited_by',
            ]);

        $table->update();
    }
}