<?php

use Phinx\Migration\AbstractMigration;

class ChangeRolesSourceView extends AbstractMigration
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
            CREATE OR REPLACE VIEW roles_source AS
            
            (select MID AS user_id, 'enduser' AS role, 10 as level from People where blocked != 1) UNION /* рядовой */
            (select user_id AS user_id, 'supervisor' AS role, 20 as level from supervisors) UNION /* супервайзер (назначается руководителям подразделений, но не обязательно) */
            (select distinct MID AS mid, 'teacher' AS role, 25 as level from Teachers) UNION
            (select distinct mid AS user_id, 'developer' AS role, 30 as level from developers) UNION /* разработчик БЗ */
            /*(select distinct moderators.user_id AS user_id, 'moderator' AS role, 40 as level from moderators) UNION  модератор проектов (=teacher) не используется */
            (select distinct labor_safety_specs.user_id AS user_id, 'labor_safety_local' AS role, 50 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION /* специалист по ОТ */
            (select distinct deans.MID AS user_id, 'dean_local' AS role, 60 as level FROM deans INNER JOIN responsibilities ON deans.MID = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1) UNION /* спец.по обучению */
            (select distinct at_managers.user_id AS user_id, 'atmanager_local' AS role, 70 as level from at_managers INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* спец.по оценке */
            (select distinct recruiters.user_id AS user_id, 'hr_local' AS role, 80 as level from recruiters INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* спец.по персоналу */
            
            (select distinct mid AS user_id, 'manager' AS role, 130 as level from managers) UNION /* менеджер БЗ */
            (select distinct curators.MID AS user_id, 'curator' AS role, 140 as level from curators) UNION /* менеджер проектов (=dean) */
            (select distinct labor_safety_specs.user_id AS user_id, 'labor_safety' AS role, 150 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* менеджер по ОТ */
            (select distinct deans.MID AS user_id, 'dean' AS role, 160 as level from deans LEFT JOIN specialists ON deans.MID = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по обучению */
            (select distinct at_managers.user_id AS user_id, 'atmanager' AS role, 170 as level from at_managers LEFT JOIN specialists ON at_managers.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по оценке */
            (select distinct recruiters.user_id AS user_id, 'hr' AS role, 180 as level from recruiters LEFT JOIN specialists ON recruiters.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по персоналу */
            
            (select distinct MID AS user_id, 'simple_admin' AS role, 300 as level from simple_admins) UNION  /* ограниченый админ */
            (select distinct MID AS user_id, 'admin' AS role, 310 as level from admins);
QUERY;
        $this->execute($query);
    }

    public function changeSqlServer()
    {
        $query = <<<QUERY
        IF OBJECT_ID('dbo.roles_source', 'V') IS NOT NULL
            DROP VIEW dbo.roles_source;
QUERY;
        $this->execute($query);


        $query = <<<QUERY
        CREATE VIEW roles_source AS
        
        (select MID AS user_id, 'enduser' AS role, 10 as level from People where blocked != 1) UNION /* рядовой */
        (select user_id AS user_id, 'supervisor' AS role, 20 as level from supervisors) UNION /* супервайзер (назначается руководителям подразделений, но не обязательно) */
        (select distinct MID AS mid, 'teacher' AS role, 25 as level from Teachers) UNION
        (select distinct mid AS user_id, 'developer' AS role, 30 as level from developers) UNION /* разработчик БЗ */
        /*(select distinct moderators.user_id AS user_id, 'moderator' AS role, 40 as level from moderators) UNION  модератор проектов (=teacher) не используется */
        (select distinct labor_safety_specs.user_id AS user_id, 'labor_safety_local' AS role, 50 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION /* специалист по ОТ */
        (select distinct deans.MID AS user_id, 'dean_local' AS role, 60 as level FROM deans INNER JOIN responsibilities ON deans.MID = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1) UNION /* спец.по обучению */
        (select distinct at_managers.user_id AS user_id, 'atmanager_local' AS role, 70 as level from at_managers INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* спец.по оценке */
        (select distinct recruiters.user_id AS user_id, 'hr_local' AS role, 80 as level from recruiters INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* спец.по персоналу */
        
        (select distinct mid AS user_id, 'manager' AS role, 130 as level from managers) UNION /* менеджер БЗ */
        (select distinct curators.MID AS user_id, 'curator' AS role, 140 as level from curators) UNION /* менеджер проектов (=dean) */
        (select distinct labor_safety_specs.user_id AS user_id, 'labor_safety' AS role, 150 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* менеджер по ОТ */
        (select distinct deans.MID AS user_id, 'dean' AS role, 160 as level from deans LEFT JOIN specialists ON deans.MID = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по обучению */
        (select distinct at_managers.user_id AS user_id, 'atmanager' AS role, 170 as level from at_managers LEFT JOIN specialists ON at_managers.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по оценке */
        (select distinct recruiters.user_id AS user_id, 'hr' AS role, 180 as level from recruiters LEFT JOIN specialists ON recruiters.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по персоналу */
        
        (select distinct MID AS user_id, 'simple_admin' AS role, 300 as level from simple_admins) UNION  /* ограниченый админ */
        (select distinct MID AS user_id, 'admin' AS role, 310 as level from admins);
        
QUERY;
        $this->execute($query);
    }
}
