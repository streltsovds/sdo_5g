<?php

use Phinx\Migration\AbstractMigration;

class ChangeEventTypeConstants extends AbstractMigration
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

        if ($this->table('schedule')->hasIndex(['typeID'])) {
            $this->table('schedule')
                ->removeIndex(['typeID'])
                ->update();
        }

        $this->table('schedule')
            ->changeColumn('typeID', 'string', [
                'null' => false,
                'default' => '',
                'limit' => 249,
            ])
            ->addIndex(['typeID'], [
                'name' => 'typeID_idx',
                'unique' => false,
            ])
            ->update();


        if ($this->table('meetings')->hasIndex(['typeID'])) {
            $this->table('meetings')
                ->removeIndex(['typeID'])
                ->update();
        }

        $this->table('meetings')
            ->changeColumn('typeID', 'string', [
                'null' => false,
                'default' => '',
                'limit' => 249,
            ])
            ->changeColumn('tool', 'string', [
                'null' => false,
                'default' => '',
                'limit' => 249,
            ])
            ->addIndex(['tool'], [
                'name' => 'tool_idx',
                'unique' => false,
            ])
            ->addIndex(['typeID'], [
                'name' => 'typeID_idx',
                'unique' => false,
            ])
            ->update();


        $this->execute("update schedule set tool='olympox_selfstudy' where tool='1'");
        $this->execute("update schedule set tool='olympox_exam' where tool='2'");
        $this->execute("update schedule set tool='olympox_intro' where tool='3'");
        $this->execute("update schedule set tool='olympox_selfstudy_days' where tool='7'");
        $this->execute("update schedule set tool='empty' where tool='1000'");
        $this->execute("update schedule set tool='lecture' where tool='1024'");
        $this->execute("update schedule set tool='test' where tool='2048'");
        $this->execute("update schedule set tool='exercise' where tool='2049'");
        $this->execute("update schedule set tool='course' where tool='2050'");
        $this->execute("update schedule set tool='webinar' where tool='2051'");
        $this->execute("update schedule set tool='resource' where tool='2052'");
        $this->execute("update schedule set tool='poll' where tool='2053'");
        $this->execute("update schedule set tool='task' where tool='2054'");
        $this->execute("update schedule set tool='eclass' where tool='2055'");
        $this->execute("update schedule set tool='curator_poll_for_participant' where tool='2056'");
        $this->execute("update schedule set tool='curator_poll_for_leader' where tool='2057'");
        $this->execute("update schedule set tool='curator_poll_for_moderator' where tool='2061'");

        $this->execute("update schedule set typeID='olympox_selfstudy' where typeID='1'");
        $this->execute("update schedule set typeID='olympox_exam' where typeID='2'");
        $this->execute("update schedule set typeID='olympox_intro' where typeID='3'");
        $this->execute("update schedule set typeID='olympox_selfstudy_days' where typeID='7'");
        $this->execute("update schedule set typeID='empty' where typeID='1000'");
        $this->execute("update schedule set typeID='lecture' where typeID='1024'");
        $this->execute("update schedule set typeID='test' where typeID='2048'");
        $this->execute("update schedule set typeID='exercise' where typeID='2049'");
        $this->execute("update schedule set typeID='course' where typeID='2050'");
        $this->execute("update schedule set typeID='webinar' where typeID='2051'");
        $this->execute("update schedule set typeID='resource' where typeID='2052'");
        $this->execute("update schedule set typeID='poll' where typeID='2053'");
        $this->execute("update schedule set typeID='task' where typeID='2054'");
        $this->execute("update schedule set typeID='eclass' where typeID='2055'");
        $this->execute("update schedule set typeID='curator_poll_for_participant' where typeID='2056'");
        $this->execute("update schedule set typeID='curator_poll_for_leader' where typeID='2057'");
        $this->execute("update schedule set typeID='curator_poll_for_moderator' where typeID='2061'");


        $this->execute("update meetings set tool='olympox_selfstudy' where tool='1'");
        $this->execute("update meetings set tool='olympox_exam' where tool='2'");
        $this->execute("update meetings set tool='olympox_intro' where tool='3'");
        $this->execute("update meetings set tool='olympox_selfstudy_days' where tool='7'");
        $this->execute("update meetings set tool='empty' where tool='1000'");
        $this->execute("update meetings set tool='lecture' where tool='1024'");
        $this->execute("update meetings set tool='test' where tool='2048'");
        $this->execute("update meetings set tool='exercise' where tool='2049'");
        $this->execute("update meetings set tool='course' where tool='2050'");
        $this->execute("update meetings set tool='webinar' where tool='2051'");
        $this->execute("update meetings set tool='resource' where tool='2052'");
        $this->execute("update meetings set tool='poll' where tool='2053'");
        $this->execute("update meetings set tool='task' where tool='2054'");
        $this->execute("update meetings set tool='eclass' where tool='2055'");
        $this->execute("update meetings set tool='curator_poll_for_participant' where tool='2056'");
        $this->execute("update meetings set tool='curator_poll_for_leader' where tool='2057'");
        $this->execute("update meetings set tool='curator_poll_for_moderator' where tool='2061'");

        $this->execute("update meetings set typeID='olympox_selfstudy' where typeID='1'");
        $this->execute("update meetings set typeID='olympox_exam' where typeID='2'");
        $this->execute("update meetings set typeID='olympox_intro' where typeID='3'");
        $this->execute("update meetings set typeID='olympox_selfstudy_days' where typeID='7'");
        $this->execute("update meetings set typeID='empty' where typeID='1000'");
        $this->execute("update meetings set typeID='lecture' where typeID='1024'");
        $this->execute("update meetings set typeID='test' where typeID='2048'");
        $this->execute("update meetings set typeID='exercise' where typeID='2049'");
        $this->execute("update meetings set typeID='course' where typeID='2050'");
        $this->execute("update meetings set typeID='webinar' where typeID='2051'");
        $this->execute("update meetings set typeID='resource' where typeID='2052'");
        $this->execute("update meetings set typeID='poll' where typeID='2053'");
        $this->execute("update meetings set typeID='task' where typeID='2054'");
        $this->execute("update meetings set typeID='eclass' where typeID='2055'");
        $this->execute("update meetings set typeID='curator_poll_for_participant' where typeID='2056'");
        $this->execute("update meetings set typeID='curator_poll_for_leader' where typeID='2057'");
        $this->execute("update meetings set typeID='curator_poll_for_moderator' where typeID='2061'");


        $this->execute("update events set tool='olympox_selfstudy' where tool='1'");
        $this->execute("update events set tool='olympox_exam' where tool='2'");
        $this->execute("update events set tool='olympox_intro' where tool='3'");
        $this->execute("update events set tool='olympox_selfstudy_days' where tool='7'");
        $this->execute("update events set tool='empty' where tool='1000'");
        $this->execute("update events set tool='lecture' where tool='1024'");
        $this->execute("update events set tool='test' where tool='2048'");
        $this->execute("update events set tool='exercise' where tool='2049'");
        $this->execute("update events set tool='course' where tool='2050'");
        $this->execute("update events set tool='webinar' where tool='2051'");
        $this->execute("update events set tool='resource' where tool='2052'");
        $this->execute("update events set tool='poll' where tool='2053'");
        $this->execute("update events set tool='task' where tool='2054'");
        $this->execute("update events set tool='eclass' where tool='2055'");
        $this->execute("update events set tool='curator_poll_for_participant' where tool='2056'");
        $this->execute("update events set tool='curator_poll_for_leader' where tool='2057'");
        $this->execute("update events set tool='curator_poll_for_moderator' where tool='2061'");

        $adapterType = $this->getAdapter()->getAdapterType();

        if ('mysql' === $adapterType) {
            $this->updateMaterialsMySql();
        } elseif ('sqlsrv' === $adapterType) {
            $this->updateMaterialsSqlServer();
        }
    }

    public function updateMaterialsSqlServer()
    {
        $query = <<<QUERY
          IF OBJECT_ID('dbo.materials', 'V') IS NOT NULL
              DROP VIEW dbo.materials;
QUERY;
        $this->execute($query);

        $query = <<<QUERY
          CREATE VIEW materials AS
          (
              SELECT DISTINCT
                  r.resource_id as id,
                  'resource' as [type],
                  r.type as subtype,
                  r.filetype as filetype,
                  r.title,
                  r.subject_id,
                  r.Status as status,
                  r.created
              FROM
                resources r
          UNION
              SELECT DISTINCT
                  c.CID as id,
                  'course' as [type],
                  c.format as subtype,
                  null as filetype,
                  c.Title as title,
                  c.subject_id,
                  c.Status as status,
                  c.createdate AS created
              FROM
                Courses c
          UNION
              SELECT DISTINCT
                  q.quest_id as id,
                  'test' as [type],
                  null as subtype,
                  null as filetype,
                  q.name as title,
                  q.subject_id,
                  q.status,
                  null as created
              FROM
                questionnaires q
              WHERE
                q.type = 'test'
          UNION
              SELECT DISTINCT
                  q.quest_id as id,
                  'poll' as [type],
                  null as subtype,
                  null as filetype,
                  q.name as title,
                  q.subject_id,
                  q.status,
                  null as created
              FROM
                questionnaires q
              WHERE
                q.type = 'poll'
          UNION
              SELECT DISTINCT
                  t.task_id as id,
                  'task' as [type],
                  null as subtype,
                  null as filetype,
                  t.title,
                  t.subject_id,
                  t.status,
                  t.created
              FROM tasks t
          );
QUERY;
        $this->execute($query);
    }

    public function updateMaterialsMySql()
    {
        $query = <<<QUERY
          CREATE OR REPLACE VIEW materials AS        
              SELECT DISTINCT
                  r.resource_id as id,
                  'resource' as `type`,
                  r.type as subtype,
                  r.filetype as filetype,
                  r.title,
                  r.subject_id,
                  r.Status as status,
                  r.created
              FROM
                resources r
          UNION
              SELECT DISTINCT
                  c.CID as id,
                  'course' as `type`,
                  c.format as subtype,
                  null as filetype,
                  c.Title as title,
                  c.subject_id,
                  c.Status as status,
                  c.createdate AS created
              FROM
                Courses c
          UNION
              SELECT DISTINCT
                  q.quest_id as id,
                  'test' as `type`,
                  null as subtype,
                  null as filetype,
                  q.name as title,
                  q.subject_id,
                  q.status,
                  null as created
              FROM
                questionnaires q
              WHERE
                q.type = 'test'
          UNION
              SELECT DISTINCT
                  q.quest_id as id,
                  'poll' as `type`,
                  null as subtype,
                  null as filetype,
                  q.name as title,
                  q.subject_id,
                  q.status,
                  null as created
              FROM
                questionnaires q
              WHERE
                q.type = 'poll'
          UNION
              SELECT DISTINCT
                  t.task_id as id,
                  'task' as `type`,
                  null as subtype,
                  null as filetype,
                  t.title,
                  t.subject_id,
                  t.status,
                  t.created
              FROM tasks t
QUERY;

        $this->execute($query);
    }
}
