<?php

use Phinx\Migration\AbstractMigration;

class ChangeCourseConstantsTypesAndValues extends AbstractMigration
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
        $hasTypeIndex = $this->table('Courses')->hasIndex('format');
        if ($hasTypeIndex) {
            $this->table('Courses')
                ->removeIndex('format')
                ->update();
        }

        $this->table('Courses')
            ->changeColumn('format', 'string', [
                'collation' => 'utf8mb4_unicode_ci',
                'null' => false,
                'limit' => 249,
                'default' => '0',
            ])
            ->update();

        $this->table('Courses')
            ->addIndex(
                ['format'],
                [
                    'name' => 'format_idx',
                    'unique' => false,
                ]
            )
            ->update();

        $this->execute("update Courses set format='free' where format='999'");
        $this->execute("update Courses set format='unknown' where format='0'");
        $this->execute("update Courses set format='scorm' where format='2'");
        $this->execute("update Courses set format='aicc' where format='3'");
        $this->execute("update Courses set format='eau3' where format='4'");
        $this->execute("update Courses set format='zip' where format='5'");
        $this->execute("update Courses set format='tincan' where format='6'");
    }
}
