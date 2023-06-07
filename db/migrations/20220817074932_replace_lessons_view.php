<?php

use Phinx\Migration\AbstractMigration;

class ReplaceLessonsView extends AbstractMigration
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
        $adapterType = $this->getAdapter()->getAdapterType();

        if ('mysql' === $adapterType) {
            $this->changeMysql();
        } elseif ('sqlsrv' === $adapterType) {
            $this->changeSqlServer();
        }

    }


    public function changeMysql()
    {
        $query = <<<QUERY
        CREATE OR REPLACE VIEW lessons AS        
SELECT SHEID, title, typeID, timetype, descript, CID, createID, vedomost, teacher, moderator, 
cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, isfree, `has_proctoring`, `order`,
CASE 
    WHEN cond_sheid > 0 THEN 1
    WHEN cond_progress > 0 THEN 1
    WHEN cond_avgbal > 0 THEN 1
    WHEN cond_sumbal > 0 THEN 1
    ELSE 0
END AS `condition`,
CASE timetype
    WHEN 0 THEN UNIX_TIMESTAMP(`begin`) 
    WHEN 1 THEN startday
    WHEN 2 THEN 0 
END AS `begin`, 
CASE
    WHEN timetype = 0 THEN UNIX_TIMESTAMP(`end`) 
    WHEN timetype = 1 THEN stopday
    WHEN timetype = 2 THEN 0 
END AS `end`
FROM schedule
QUERY;

        $this->execute($query);
    }

    public function changeSqlServer()
    {
        $query = <<<QUERY
        IF OBJECT_ID('dbo.lessons', 'V') IS NOT NULL
            DROP VIEW dbo.lessons;
QUERY;
        $this->execute($query);

        $query = <<<QUERY
        CREATE VIEW lessons AS
        (
SELECT SHEID, title, typeID, timetype, descript, CID, createID, vedomost, teacher, moderator, cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, isfree, has_proctoring, [order],
CASE 
    WHEN cond_sheid > 0 THEN 1
    WHEN cond_progress > 0 THEN 1
    WHEN cond_avgbal > 0 THEN 1
    WHEN cond_sumbal > 0 THEN 1
    ELSE 0
END AS condition,
CASE 
    WHEN timetype = 0 THEN dbo.UNIX_TIMESTAMP([begin]) 
    WHEN timetype = 1 THEN startday
    WHEN timetype = 2 THEN 0 
END AS [begin], 
CASE
    WHEN timetype = 0 THEN dbo.UNIX_TIMESTAMP([end]) 
    WHEN timetype = 1 THEN stopday
    WHEN timetype = 2 THEN 0 
END AS [end]
FROM schedule
        );
QUERY;

        $this->execute($query);
    }

}
