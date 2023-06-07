<?php

use Phinx\Migration\AbstractMigration;

class CreateTableSubjectCriteria extends AbstractMigration
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
        if (!$this->hasTable('subject_criteria')) {
            $this->table('subject_criteria', [
                'id' => false,
                'primary_key' => ['subject_id', 'criterion_id', 'criterion_type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('subject_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('criterion_type', 'integer', [
                    'null' => false,
                ])
                ->create();
        }
    }
}
