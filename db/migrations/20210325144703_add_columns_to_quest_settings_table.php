<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToQuestSettingsTable extends AbstractMigration
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
        $table = $this->table('quest_settings');
        if (!$table->hasColumn('mode_test_page'))
            $table->addColumn('mode_test_page', 'integer', [
                'null' => true,
                'default' => '0',
                'comment' => 'Переключение между страницами теста (0 - последовательное перемещение, 1 - Свободное переключение между страницами)',
                'after' => 'limit_clean',
            ]);

        if (!$table->hasColumn('mode_self_test'))
            $table->addColumn('mode_self_test', 'integer', [
                'null' => true,
                'after' => 'mode_test_page',
            ]);

        $table->update();
    }
}
