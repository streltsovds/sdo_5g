<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToCertificatesTable extends AbstractMigration
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
        $table = $this->table('certificates');
        if (!$table->hasColumn('name'))
            $table->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'created',
            ]);
        if (!$table->hasColumn('description'))
            $table->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ]);
        if (!$table->hasColumn('organization'))
            $table->addColumn('organization', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ]);
        if (!$table->hasColumn('startdate'))
            $table->addColumn('startdate', 'datetime', [
                'null' => true,
                'after' => 'organization',
            ]);
        if (!$table->hasColumn('enddate'))
            $table->addColumn('enddate', 'datetime', [
                'null' => true,
                'after' => 'startdate',
            ]);
        if (!$table->hasColumn('filename'))
            $table->addColumn('filename', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'enddate',
            ]);
        if (!$table->hasColumn('type'))
            $table->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'filename',
            ]);
        if (!$table->hasColumn('number'))
            $table->addColumn('number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ]);

        $table->update();
    }
}
