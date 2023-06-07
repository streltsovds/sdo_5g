<?php

use Phinx\Migration\AbstractMigration;

class AddColumnsToPeopleTable extends AbstractMigration
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
        $table = $this->table('People');
        if (!$table->hasColumn('Domain'))
            $table->addColumn('Domain', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Login',
            ]);

        if (!$table->hasColumn('Age'))
            $table->addColumn('Age', 'integer', [
                'null' => true,
                'after' => 'javapassword',
            ]);

        if (!$table->hasColumn('email_backup'))
            $table->addColumn('email_backup', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'need_edit',
            ]);

        if (!$table->hasColumn('duplicate_of'))
            $table->addColumn('duplicate_of', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'dublicate',
            ]);

        if (!$table->hasColumn('contact_displayed'))
            $table->addColumn('contact_displayed', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'duplicate_of',
            ]);
        if (!$table->hasIndex(['MID', 'blocked']))
            $table->addIndex(['MID', 'blocked'], [
                'name' => 'mid_blocked',
                'unique' => false,
            ]);

        $table->update();
    }
}
