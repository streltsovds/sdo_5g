<?php

use Phinx\Migration\AbstractMigration;

class ChangeKbaseItemsType extends AbstractMigration
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
            CREATE OR REPLACE VIEW `kbase_items` AS
            select
                `Courses`.`Status` AS `status`,
                'course' AS `type`,
                `Courses`.`Title` AS `title`,
                `Courses`.`CID` AS `id`,
                `Courses`.`createdate` AS `cdate`
            from
                `Courses`
            where
                (((`Courses`.`Status` = 1)
                and isnull(`Courses`.`chain`))
                or (`Courses`.`chain` = 0))
            union
            select
                `resources`.`status` AS `status`,
                'resource' AS `type`,
                `resources`.`title` AS `title`,
                `resources`.`resource_id` AS `id`,
                `resources`.`created` AS `cdate`
            from
                `resources`
            where
                ((`resources`.`location` = 1)
                and (`resources`.`status` = 1)
                and (`resources`.`parent_id` = 0));
QUERY;

        $this->execute($query);
    }

    public function changeSqlServer()
    {
        $query = <<<QUERY
        IF OBJECT_ID('dbo.kbase_items', 'V') IS NOT NULL
            DROP VIEW dbo.kbase_items;
QUERY;
        $this->execute($query);

        $query = <<<QUERY
        CREATE VIEW kbase_items AS
        (
            SELECT 
                Courses.Status AS status, 
                'course' as [type], 
                Courses.Title AS title, 
                Courses.CID AS id, 
                Courses.createdate AS cdate 
            FROM Courses 
            WHERE 
                Courses.Status = 1 AND 
                Courses.chain IS NULL OR 
                Courses.chain = 0 
            UNION 
            SELECT 
                resources.Status AS status, 
                'resource' as [type], 
                resources.title AS title, 
                resources.resource_id AS id, 
                resources.created AS cdate 
            FROM resources  
            WHERE 
                resources.location = 1 AND 
                resources.Status = 1 AND 
                resources.parent_id = 0
        );
QUERY;

        $this->execute($query);
    }
}
