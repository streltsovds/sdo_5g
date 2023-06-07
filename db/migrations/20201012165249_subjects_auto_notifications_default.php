<?php

use Phinx\Migration\AbstractMigration;

/**
 * Значения по-умолчанию для полей, чтобы работал sql-код из db_dump2.sql
 */
class SubjectsAutoNotificationsDefault extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('subjects');
        $column = $table->hasColumn('auto_notification');

        if ($column) {
            $table->changeColumn('auto_notification', 'integer', [
                'null' => false,
                'default' => 0,
            ])
                ->update();
        } else {
            $table->addColumn(
                'auto_notification', 'integer', [
                    'null' => false,
                    'default' => 0,
                ])
                ->update();
        }
    }
}
