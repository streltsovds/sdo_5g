<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToQuestionnairesTable extends AbstractMigration
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
        $table = $this->table('questionnaires');
        if (!$table->hasColumn('creator_role'))
            $table->addColumn('creator_role', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'scale_id',
            ]);

        if (!$table->hasColumn('profile_id'))
            $table->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'displaycomment',
            ]);

        $table->update();
    }
}
