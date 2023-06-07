<?php

use Phinx\Migration\AbstractMigration;

class AddFiletypeToMaterialsView extends AbstractMigration
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
          CREATE OR REPLACE VIEW materials AS        
              SELECT DISTINCT
                  r.resource_id as id,
                  2052 as `type`,
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
                  2050 as `type`,
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
                  2048 as `type`,
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
                  2053 as `type`,
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
                  2054 as `type`,
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

    public function changeSqlServer()
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
                  2052 as [type],
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
                  2050 as [type],
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
                  2048 as [type],
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
                  2053 as [type],
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
                  2054 as [type],
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
}
