<?php

use Phinx\Migration\AbstractMigration;

class AddOriginalFilenameAndFiletypeToResources extends AbstractMigration
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
        $table = $this->table('resources');

        if (!$table->hasColumn('origin_filename'))
            $table->addColumn('origin_filename', 'string', [
                'null' => false,
                'default' => '',
            ]);

        if (!$table->hasColumn('origin_filetype'))
            $table->addColumn('origin_filetype', 'string', [
                'null' => false,
                'default' => '',
            ]);

        $table->update();
    }
}
