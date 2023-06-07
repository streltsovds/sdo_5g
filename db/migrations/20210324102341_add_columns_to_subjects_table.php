<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToSubjectsTable extends AbstractMigration
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
        $table = $this->table('subjects');
        if (!$table->hasColumn('is_labor_safety'))
            $table->addColumn('is_labor_safety', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subid',
            ]);

        if (!$table->hasColumn('created'))
            $table->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'period',
            ]);

        if (!$table->hasColumn('create_from_tc_session'))
            $table->addColumn('create_from_tc_session', 'integer', [
                'null' => true,
                'after' => 'in_banner',
            ]);

        if (!$table->hasColumn('provider_id'))
            $table->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'create_from_tc_session',
            ]);

        if (!$table->hasColumn('status'))
            $table->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ]);

        if (!$table->hasColumn('format'))
            $table->addColumn('format', 'integer', [
                'null' => true,
                'after' => 'status',
            ]);

        if (!$table->hasColumn('criterion_id'))
            $table->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'format',
            ]);
        if (!$table->hasColumn('criterion_type'))
            $table->addColumn('criterion_type', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ]);

        if (!$table->hasColumn('created_by'))
            $table->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'criterion_type',
            ]);

        if (!$table->hasColumn('category'))
            $table->addColumn('category', 'integer', [
                'null' => true,
                'after' => 'created_by',
            ]);

        if (!$table->hasColumn('city'))
            $table->addColumn('city', 'integer', [
                'null' => true,
                'after' => 'category',
            ]);

        if (!$table->hasColumn('primary_type'))
            $table->addColumn('primary_type', 'integer', [
                'null' => true,
                'after' => 'city',
            ]);

        if (!$table->hasColumn('mark_required'))
            $table->addColumn('mark_required', 'integer', [
                'null' => true,
                'after' => 'primary_type',
            ]);

        if (!$table->hasColumn('check_form'))
            $table->addColumn('check_form', 'integer', [
                'null' => true,
                'after' => 'mark_required',
            ]);

        if (!$table->hasColumn('provider_type'))
            $table->addColumn('provider_type', 'integer', [
                'null' => true,
                'default' => '2',
                'after' => 'check_form',
            ]);

        if (!$table->hasColumn('after_training'))
            $table->addColumn('after_training', 'integer', [
                'null' => true,
                'after' => 'provider_type',
            ]);

        if (!$table->hasColumn('feedback'))
            $table->addColumn('feedback', 'integer', [
                'null' => true,
                'after' => 'after_training',
            ]);

        if (!$table->hasColumn('education_type'))
            $table->addColumn('education_type', 'integer', [
                'null' => true,
                'default' => '2',
                'after' => 'feedback',
            ]);

        if (!$table->hasColumn('rating'))
            $table->addColumn('rating', 'float', [
                'null' => true,
                'after' => 'education_type',
            ]);

        if (!$table->hasColumn('direction_id'))
            $table->addColumn('direction_id', 'integer', [
                'null' => true,
                'after' => 'rating',
            ]);

        if (!$table->hasColumn('banner_url'))
            $table->addColumn('banner_url', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'direction_id',
            ]);

        if (!$table->hasColumn('auto_notification'))
            $table->addColumn('auto_notification', 'integer', [
                'null' => false,
                'default' => 0,
                'after' => 'banner_url',
            ]);

        $table->update();
    }
}
