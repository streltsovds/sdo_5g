<?php

use Phinx\Migration\AbstractMigration;

class AddEventDateView extends AbstractMigration
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
        CREATE OR REPLACE
        VIEW `event_date` AS
        select
            `sch`.`SHEID` AS `id`,
            `sch`.`title` AS `name`,
            `sid`.`begin_personal` AS `begin_date`,
            'lesson' AS `type`,
            `sid`.`MID` AS `user_id`
        from
            (`scheduleID` `sid`
        join `schedule` `sch` on
            ((`sch`.`SHEID` = `sid`.`SHEID`)))
        where
            (`sid`.`begin_personal` is not null)
        union
        select
            `s`.`subid` AS `id`,
            `s`.`name` AS `name`,
            `s`.`begin` AS `begin_date`,
            'subject' AS `type`,
            `st`.`MID` AS `user_id`
        from
            (`subjects` `s`
        join `Students` `st` on
            ((`st`.`CID` = `s`.`subid`)))
        where
            (`s`.`begin` is not null)
        union
        select
            `s`.`session_id` AS `id`,
            `s`.`name` AS `name`,
            `s`.`begin_date` AS `begin_date`,
            'at_session' AS `type`,
            `su`.`user_id` AS `user_id`
        from
            (`at_sessions` `s`
        join `at_session_users` `su` on
            ((`s`.`session_id` = `su`.`session_id`)))
        where
            (`s`.`begin_date` is not null)
QUERY;

        $this->execute($query);
    }

    public function changeSqlServer()
    {
        $query = <<<QUERY
        IF OBJECT_ID('dbo.event_date', 'V') IS NOT NULL
            DROP VIEW dbo.event_date;
QUERY;
        $this->execute($query);

        $query = <<<QUERY
            create view event_date as
            (
                select 
                    sch.SHEID as 'id',
                    sch.title as 'name',
                    sid.begin_personal as 'begin_date', 
                    'lesson' as 'type',
                    sid.MID as 'user_id'
                from scheduleID sid
                inner join schedule sch on sch.SHEID=sid.SHEID
                where begin_personal is not null
            union
                select 
                    s.subid as 'id',
                    s.name as 'name',
                    [begin] as 'begin_date', 
                    'subject' as 'type', 
                    st.MID as 'user_id'
                from subjects s
                inner join Students st on st.CID=s.subid
                where [begin] is not null
            union
                select 
                    s.session_id as 'id',
                    s.name as 'name',
                    [begin_date] as 'begin_date', 
                    'at_session' as 'type', 
                    su.user_id
                from at_sessions s
                inner join at_session_users su on  s.session_id=su.session_id
                where [begin_date] is not null
            );
QUERY;

        $this->execute($query);
    }
}
