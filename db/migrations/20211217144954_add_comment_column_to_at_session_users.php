<?php

use Phinx\Migration\AbstractMigration;

class AddCommentColumnToAtSessionUsers extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('at_session_users');
        if (!$table->hasColumn('comment')) {
            $table
                ->addColumn('comment', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'reserve_id',
                ])
                ->save();
        }
    }

    public function down()
    {
        $table = $this->table('at_session_users');
        if ($table->hasColumn('comment')) {
            $table
                ->removeColumn('comment')
                ->save();
        }
    }
}
