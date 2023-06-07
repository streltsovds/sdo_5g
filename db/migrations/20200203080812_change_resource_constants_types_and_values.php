<?php

use Phinx\Migration\AbstractMigration;

class ChangeResourceConstantsTypesAndValues extends AbstractMigration
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
        $hasTypeIndex = $this->table('resources')->hasIndex('type');
        if ($hasTypeIndex) {
            $this->table('resources')
                ->removeIndex('type')
                ->update();
        }

        $this->table('resources')
            ->changeColumn('type', 'string', [
                'collation' => 'utf8mb4_unicode_ci',
                'null' => false,
                'limit' => 249,
                'default' => '0',
            ])
            ->update();

        $this->table('resources')
            ->addIndex(
                ['type'],
                [
                    'name' => 'type_idx',
                    'unique' => false,
                ]
            )
            ->update();

        $this->execute("update resources set type='external' where type='0'");
        $this->execute("update resources set type='html' where type='1'");
        $this->execute("update resources set type='url' where type='2'");
        $this->execute("update resources set type='fileset' where type='3'");
        $this->execute("update resources set type='webinar' where type='4'");
        $this->execute("update resources set type='activity' where type='5'");
        $this->execute("update resources set type='html_slider' where type='10'");
        $this->execute("update resources set type='card' where type='99'");
    }
}
