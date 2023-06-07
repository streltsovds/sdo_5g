<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToStructureOfOrganTable extends AbstractMigration
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
        $table = $this->table('structure_of_organ');
        if (!$table->hasColumn('original_profile_id'))
            $table->addColumn('original_profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ]);
        if (!$table->hasColumn('position_date'))
            $table->addColumn('position_date', 'datetime', [
                'null' => true,
                'after' => 'is_manager',
            ]);
        if (!$table->hasColumn('employment_type'))
            $table->addColumn('employment_type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'blocked',
            ]);
        if (!$table->hasColumn('employee_status'))
            $table->addColumn('employee_status', 'boolean', [
                'null' => true,
                'after' => 'employment_type',
            ]);
        if (!$table->hasColumn('manager_soid'))
            $table->addColumn('manager_soid', 'integer', [
                'null' => true,
                'after' => 'employee_status',
            ]);
        if (!$table->hasColumn('staff_unit_id'))
            $table->addColumn('staff_unit_id', 'integer', [
                'null' => true,
                'after' => 'manager_soid',
            ]);
        if (!$table->hasColumn('is_first_position'))
            $table->addColumn('is_first_position', 'integer', [
                'null' => true,
                'after' => 'staff_unit_id',
            ]);
        if (!$table->hasColumn('created_at'))
            $table->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'is_first_position',
            ]);
        if (!$table->hasColumn('deleted_at'))
            $table->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ]);
        if (!$table->hasColumn('is_integration2'))
            $table->addColumn('is_integration2', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'deleted_at',
            ]);
        if (!$table->hasColumn('deputy'))
            $table->addColumn('deputy', 'integer', [
                'null' => true,
                'after' => 'is_integration2',
            ]);

        $table->update();
    }
}
