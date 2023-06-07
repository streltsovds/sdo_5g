<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToHtmlpageTable extends AbstractMigration
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
        $table = $this->table('htmlpage');
        if (!$table->hasColumn('description'))
            $table->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'url',
            ]);

        if (!$table->hasColumn('icon_url'))
            $table->addColumn('icon_url', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ]);

        if (!$table->hasColumn('visible'))
            $table->addColumn('visible', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'icon_url',
            ]);

        $table->update();
    }
}
