<?php

use Phinx\Migration\AbstractMigration;

class ChangeFiletypeConstants extends AbstractMigration
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
        if ($this->table('resources')->hasIndex(['filetype'])) {
            $this->table('resources')
                ->removeIndex('filetype')
                ->update();
        }

        $this->table('resources')
            ->changeColumn('filetype', 'string', [
                'null' => false,
                'limit' => 249,
                'default' => '',
            ])
            ->addIndex(['filetype'], [
                'name' => 'filetype_idx',
                'unique' => false,
            ])
            ->update();

        if ($this->table('resource_revisions')->hasIndex(['filetype'])) {
            $this->table('resource_revisions')
                ->removeIndex('filetype')
                ->update();
        }

        $this->table('resource_revisions')
            ->changeColumn('filetype', 'string', [
                'null' => false,
                'limit' => 249,
                'default' => '',
            ])
            ->addIndex(['filetype'], [
                'name' => 'filetype_idx',
                'unique' => false,
            ])
            ->update();

        $this->table('files')
            ->changeColumn('item_type', 'string', [
                'null' => true,
                'default' => '',
            ])
            ->update();

        $this->execute("update resources set filetype='unknown' where filetype='0'");
        $this->execute("update resources set filetype='text' where filetype='1'");
        $this->execute("update resources set filetype='html' where filetype='2'");
        $this->execute("update resources set filetype='image' where filetype='3'");
        $this->execute("update resources set filetype='audio' where filetype='4'");
        $this->execute("update resources set filetype='video' where filetype='5'");
        $this->execute("update resources set filetype='flash' where filetype='6'");
        $this->execute("update resources set filetype='doc' where filetype='7'");
        $this->execute("update resources set filetype='xls' where filetype='8'");
        $this->execute("update resources set filetype='xlsx' where filetype='81'");
        $this->execute("update resources set filetype='ppt' where filetype='9'");
        $this->execute("update resources set filetype='pdf' where filetype='10'");
        $this->execute("update resources set filetype='zip' where filetype='99'");

        $this->execute("update resource_revisions set filetype='unknown' where filetype='0'");
        $this->execute("update resource_revisions set filetype='text' where filetype='1'");
        $this->execute("update resource_revisions set filetype='html' where filetype='2'");
        $this->execute("update resource_revisions set filetype='image' where filetype='3'");
        $this->execute("update resource_revisions set filetype='audio' where filetype='4'");
        $this->execute("update resource_revisions set filetype='video' where filetype='5'");
        $this->execute("update resource_revisions set filetype='flash' where filetype='6'");
        $this->execute("update resource_revisions set filetype='doc' where filetype='7'");
        $this->execute("update resource_revisions set filetype='xls' where filetype='8'");
        $this->execute("update resource_revisions set filetype='xlsx' where filetype='81'");
        $this->execute("update resource_revisions set filetype='ppt' where filetype='9'");
        $this->execute("update resource_revisions set filetype='pdf' where filetype='10'");
        $this->execute("update resource_revisions set filetype='zip' where filetype='99'");

        $this->execute("update files set item_type='unknown' where item_type='0'");
        $this->execute("update files set item_type='text' where item_type='1'");
        $this->execute("update files set item_type='html' where item_type='2'");
        $this->execute("update files set item_type='image' where item_type='3'");
        $this->execute("update files set item_type='audio' where item_type='4'");
        $this->execute("update files set item_type='video' where item_type='5'");
        $this->execute("update files set item_type='flash' where item_type='6'");
        $this->execute("update files set item_type='doc' where item_type='7'");
        $this->execute("update files set item_type='xls' where item_type='8'");
        $this->execute("update files set item_type='xlsx' where item_type='81'");
        $this->execute("update files set item_type='ppt' where item_type='9'");
        $this->execute("update files set item_type='pdf' where item_type='10'");
        $this->execute("update files set item_type='zip' where item_type='99'");
    }
}
