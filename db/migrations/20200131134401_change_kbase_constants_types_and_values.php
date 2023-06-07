<?php

use Phinx\Migration\AbstractMigration;

class ChangeKbaseConstantsTypesAndValues extends AbstractMigration
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

        if ($this->hasTable('kbase_assessment'))
            $this->table('kbase_assessment')
                ->changeColumn('type', 'string', [
                    'collation' => 'utf8mb4_unicode_ci',
                    'null' => false,
                    'default' => '0',
                ])
                ->update();

        $this->execute("update kbase_assessment set type='resource' where type='1'");
        $this->execute("update kbase_assessment set type='course' where type='2'");
        $this->execute("update kbase_assessment set type='test' where type='3'");
        $this->execute("update kbase_assessment set type='poll' where type='4'");
        $this->execute("update kbase_assessment set type='task' where type='5'");

    }
}
