<?php

use Phinx\Migration\AbstractMigration;

class AddRejectedClaimantsToSubjectsUsersTable extends AbstractMigration
{
    public function change()
    {
        $adapterType = $this->getAdapter()->getAdapterType();

        if ('mysql' === $adapterType) {
            $this->changeMysql();
        } elseif ('sqlsrv' === $adapterType) {
            $this->changeSqlServer();
        }
    }

    private function changeMysql()
    {
        $query = <<<QUERY
CREATE OR REPLACE VIEW subjects_users AS
    SELECT MID as user_id, CID as subject_id, begin_personal AS `begin`, NULL AS `end`, 1 AS status FROM Students
    UNION SELECT MID as user_id, CID as subject_id, NULL AS `begin`, NULL AS `end`, 0 AS status FROM claimants WHERE `status` = 0
    UNION SELECT MID as user_id, CID as subject_id, `begin` AS `begin`, `end` AS `end`, 2 AS status FROM graduated
    UNION SELECT MID as user_id, CID as subject_id, NULL AS `begin`, NULL AS `end`, 3 AS status FROM Teachers
    UNION SELECT MID as user_id, CID as subject_id, NULL AS `begin`, NULL AS `end`, 4 AS status FROM claimants WHERE `status` = 1
;
QUERY;
        $this->execute($query);
    }

    private function changeSqlServer()
    {
        $query = <<<QUERY
        IF OBJECT_ID('dbo.subjects_users', 'V') IS NOT NULL
            DROP VIEW dbo.subjects_users;
QUERY;
        $this->execute($query);

        $query = <<<QUERY
CREATE VIEW subjects_users AS
(
    SELECT MID as user_id, CID as subject_id, begin_personal AS [begin], NULL AS [end], 1 AS status FROM Students
    UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 0 AS status FROM claimants WHERE [status] = 0
    UNION SELECT MID as user_id, CID as subject_id, [begin] AS [begin], [end] AS [end], 2 AS status FROM graduated
    UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 3 AS status FROM Teachers
    UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 4 AS status FROM claimants WHERE [status] = 1
)
;
QUERY;
        $this->execute($query);
    }
}
