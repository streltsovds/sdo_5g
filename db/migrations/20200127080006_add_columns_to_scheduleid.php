<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToScheduleId extends AbstractMigration

{
    public function change()
    {
        $table = $this->table('scheduleID');

        if ($table->hasColumn('beginRelative') && !$table->hasColumn('begin_personal'))
            $table->renameColumn('beginRelative', 'begin_personal');

        if ($table->hasColumn('endRelative') && !$table->hasColumn('end_personal'))
            $table->renameColumn('endRelative', 'end_personal');

        $table->update();
    }
}