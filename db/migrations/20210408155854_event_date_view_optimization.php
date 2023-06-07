<?php

use Phinx\Migration\AbstractMigration;

class EventDateViewOptimization extends AbstractMigration
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

        if (!$this->table('schedule')->hasIndex(['title'])) {
            $this->table('schedule')
                ->addIndex(['title'], [
                    'name' => 'title',
                    'unique' => false,
                ])->update();
        }


        if (!$this->table('schedule')->hasIndex(['typeID'])) {
            $this->table('schedule')
                ->addIndex(['typeID'], [
                    'name' => 'typeID',
                    'unique' => false,
                ])->update();
        }


        if (!$this->table('schedule')->hasIndex(['CID'])) {
            $this->table('schedule')
                ->addIndex(['CID'], [
                    'name' => 'CID',
                    'unique' => false,
                ])->update();
        }


        if (!$this->table('schedule')->hasIndex(['timetype'])) {
            $this->table('schedule')
                ->addIndex(['timetype'], [
                    'name' => 'timetype',
                    'unique' => false,
                ])->update();
        }

        if (!$this->table('events')->hasIndex(['tool'])) {
            $this->table('events')
                ->addIndex(['tool'], [
                    'name' => 'tool',
                    'unique' => false,
                ])->update();
        }

        if (!$this->table('subjects')->hasIndex(['name'])) {
            $this->table('subjects')
                ->addIndex(['name'], [
                    'name' => 'name',
                    'unique' => false,
                ])->update();
        }

        if (!$this->table('subjects')->hasIndex(['period'])) {
            $this->table('subjects')
                ->addIndex(['period'], [
                    'name' => 'period',
                    'unique' => false,
                ])->update();
        }

        if (!$this->table('at_sessions')->hasIndex(['name'])) {
            $this->table('at_sessions')
                ->addIndex(['name'], [
                    'name' => 'name',
                    'unique' => false,
                ])->update();
        }

        if (!$this->table('at_sessions')->hasIndex(['begin_date'])) {
            $this->table('at_sessions')
                ->addIndex(['begin_date'], [
                    'name' => 'begin_date',
                    'unique' => false,
                ])->update();
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
            CASE WHEN ev.tool is not null THEN ev.tool ELSE sch.typeID END as `subtype`,
            `sch`.`CID` as `subject_id`,
            `sid`.`MID` AS `user_id`
        from
            (`scheduleID` `sid`
        join `schedule` `sch` on
            ((`sch`.`SHEID` = `sid`.`SHEID`)))
        left join events ev on CASE WHEN concat('', typeID * 1) = typeID THEN abs(CONVERT(typeID, SIGNED INTEGER)) ELSE 0 END = ev.event_id
        where
            `sid`.`begin_personal` is not null and
            `sch`.`timetype` <> 2
        union all
        select
            `s`.`subid` AS `id`,
            `s`.`name` AS `name`,
            `s`.`begin` AS `begin_date`,
            'subject' AS `type`,
            '' as `subtype`,
            0 as `subject_id`,
            `st`.`MID` AS `user_id`
        from
            (`subjects` `s`
        join `Students` `st` on
            ((`st`.`CID` = `s`.`subid`)))
        where
            `s`.`begin` is not null and 
            `s`.`period` <> 1
        union all
        select
            `s`.`session_id` AS `id`,
            `s`.`name` AS `name`,
            `s`.`begin_date` AS `begin_date`,
            'at_session' AS `type`,
            '' as `subtype`,
            0 as `subject_id`,
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
                    CASE WHEN ev.tool is not null THEN ev.tool ELSE sch.typeID END AS 'subtype', 
                    sch.CID as 'subject_id',
                    sid.MID as 'user_id'
                from scheduleID sid
                inner join schedule sch on sch.SHEID=sid.SHEID
                left join events ev on CASE WHEN isnumeric(sch.typeID)=1 THEN abs(CAST(sch.typeID as INT)) ELSE 0 END=ev.event_id
                where begin_personal is not null and
                    sch.timetype <> 2
            union all
                select 
                    s.subid as 'id',
                    s.name as 'name',
                    [begin] as 'begin_date', 
                    'subject' as 'type', 
                    '' AS 'subtype',
                    0 as 'subject_id',
                    st.MID as 'user_id'
                from subjects s
                inner join Students st on st.CID=s.subid
                where [begin] is not null and
                    s.period <> 1
            union all
                select 
                    s.session_id as 'id',
                    s.name as 'name',
                    [begin_date] as 'begin_date', 
                    'at_session' as 'type',
                    '' AS 'subtype', 
                    0 as 'subject_id',
                    su.user_id
                from at_sessions s
                inner join at_session_users su on  s.session_id=su.session_id
                where [begin_date] is not null
            );
QUERY;

        $this->execute($query);
    }
}
