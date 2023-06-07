<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToFiles extends AbstractMigration

{
    public function change()
    {
        $table = $this->table('files');
        if (!$table->hasColumn('item_type'))
            $table->addColumn('item_type', 'integer', [
                'null' => true,
                'after' => 'file_size',
            ]);

        if (!$table->hasColumn('item_id'))
            $table->addColumn('item_id', 'integer', [
                'null' => true,
                'after' => 'item_type',
            ]);

        if (!$table->hasColumn('created_by'))
            $table->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'item_id',
            ]);

        if (!$table->hasColumn('created'))
            $table->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'created_by',
            ]);

        $table->update();
    }
}