<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToResourcesTable extends AbstractMigration
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
        if (!$table->hasColumn('edit_type'))
            $table->addColumn('edit_type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'filetype',
            ]);

        if (!$table->hasColumn('subject'))
            $table->addColumn('subject', 'string', [
                'null' => true,
                'default' => 'subject',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'services',
            ]);

        if (!$table->hasColumn('storage_id'))
            $table->addColumn('storage_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'external_viewer',
            ]);

        $table->update();
    }
}
