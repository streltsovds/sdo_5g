<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToAtProfilesTable extends AbstractMigration
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
        $table = $this->table('at_profiles');

        if (!$table->hasColumn('profile_id_external'))
            $table->addColumn('profile_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'profile_id',
            ]);

        if (!$table->hasColumn('position_id_external'))
            $table->addColumn('position_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'profile_id_external',
            ]);

        if (!$table->hasColumn('department_id_external'))
            $table->addColumn('department_id_external', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position_id_external',
            ]);

        if (!$table->hasColumn('department_id'))
            $table->addColumn('department_id', 'integer', [
                'null' => true,
                'after' => 'department_id_external',
            ]);

        if (!$table->hasColumn('department_path'))
            $table->addColumn('department_path', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'department_id',
            ]);

        if (!$table->hasColumn('progress'))
            $table->addColumn('progress', 'integer', [
                'null' => true,
                'after' => 'comments',
            ]);

        if (!$table->hasColumn('double_time'))
            $table->addColumn('double_time', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'progress',
            ]);

        if (!$table->hasColumn('blocked'))
            $table->addColumn('blocked', 'integer', [
                'null' => true,
                'after' => 'double_time',
            ]);

        if (!$table->hasColumn('psk'))
            $table->addColumn('psk', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'blocked',
            ]);

        if (!$table->hasColumn('base_id'))
            $table->addColumn('base_id', 'integer', [
                'null' => true,
                'after' => 'psk',
            ]);

        $table->update();
    }
}
