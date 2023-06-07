<?php

use Phinx\Migration\AbstractMigration;

class CreateProctoringfilesTable extends AbstractMigration
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

        if(!$this->hasTable('proctoring_files')) {

            $this->table('proctoring_files', [
                'id' => false,
                'primary_key' => ['proctoring_file_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('proctoring_file_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('type', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('SSID', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('file_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                ])
                ->addColumn('stamp', 'datetime', [
                    'null' => true,
                ])
                ->create();

        }


    }
}
