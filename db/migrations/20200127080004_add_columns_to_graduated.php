<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToGraduated extends AbstractMigration

{
    public function change()
    {
        $table = $this->table('graduated');

        if (!$table->hasColumn('effectivity'))
            $table->addColumn('effectivity', 'float', [
                'null' => true,
                'after' => 'is_lookable',
            ]);

        if (!$table->hasColumn('application_id'))
            $table->addColumn('application_id', 'integer', [
                'null' => true,
                'after' => 'effectivity',
            ]);

        $table->update();
    }
}