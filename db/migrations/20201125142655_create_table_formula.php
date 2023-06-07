<?php

use Phinx\Migration\AbstractMigration;

class CreateTableFormula extends AbstractMigration
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
        $this->table('formula', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'MyISAM',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'identity' => 'enable'
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('formula', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'formula',
            ])
            ->addColumn('CID', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'type',
            ])
            ->create();
    }
}
