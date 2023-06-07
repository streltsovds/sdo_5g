<?php

use Phinx\Migration\AbstractMigration;

class FilesConstantsAdditionalChange extends AbstractMigration
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
        $this->execute("update files set item_type='0' where item_type='unknown'");
        $this->execute("update files set item_type='1' where item_type='text'");
        $this->execute("update files set item_type='2' where item_type='html'");
        $this->execute("update files set item_type='3' where item_type='image'");
        $this->execute("update files set item_type='4' where item_type='audio'");
        $this->execute("update files set item_type='5' where item_type='video'");
        $this->execute("update files set item_type='6' where item_type='flash'");
        $this->execute("update files set item_type='7' where item_type='doc'");
        $this->execute("update files set item_type='8' where item_type='xls'");
        $this->execute("update files set item_type='81' where item_type='xlsx'");
        $this->execute("update files set item_type='9' where item_type='ppt'");
        $this->execute("update files set item_type='10' where item_type='pdf'");
        $this->execute("update files set item_type='99' where item_type='zip'");

        $this->table('files')
            ->changeColumn('item_type', 'integer', [
                'null' => true,
            ])
            ->update();
    }
}
