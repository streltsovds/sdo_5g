<?php

use Phinx\Migration\AbstractMigration;

class InitViews extends AbstractMigration
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

  private function changeSqlServer()
  {
    $query = <<<QUERY
IF OBJECT_ID('specialists') IS NULL
BEGIN
    EXEC('CREATE VIEW specialists AS
  SELECT
    responsibilities.user_id, responsibilities.responsibility_id
  FROM
    responsibilities
  WHERE
    responsibilities.item_type = 1;')
END
ELSE
BEGIN
    EXEC('ALTER VIEW specialists AS
  SELECT
    responsibilities.user_id, responsibilities.responsibility_id
  FROM
    responsibilities
  WHERE
    responsibilities.item_type = 1;')
END;
QUERY;

    $this->execute($query);

    $query = <<<QUERY
DECLARE @sqlCommand varchar(5000)

DECLARE @enduser VARCHAR(50)
DECLARE @teacher VARCHAR(50)
DECLARE @moderator VARCHAR(50)
DECLARE @student VARCHAR(50)
DECLARE @participant VARCHAR(50)
DECLARE @admin VARCHAR(50)
DECLARE @simple_admin VARCHAR(50)
DECLARE @developer VARCHAR(50)
DECLARE @manager VARCHAR(50)
DECLARE @atmanager VARCHAR(50)
DECLARE @atmanager_local VARCHAR(50)
DECLARE @supervisor VARCHAR(50)
DECLARE @employee VARCHAR(50)
DECLARE @recruiter VARCHAR(50)
DECLARE @recruiter_local VARCHAR(50)
DECLARE @curator VARCHAR(50)
DECLARE @labor_safety VARCHAR(50)
DECLARE @labor_safety_local VARCHAR(50)
DECLARE @dean VARCHAR(50)
DECLARE @dean_local VARCHAR(50)

SET @enduser = '''enduser'''
SET @teacher = '''teacher'''
SET @moderator = '''moderator'''
SET @student = '''student'''
SET @participant = '''participant'''
SET @admin = '''admin'''
SET @simple_admin = '''simple_admin'''
SET @developer = '''developer'''
SET @manager = '''manager'''
SET @atmanager = '''atmanager'''
SET @atmanager_local = '''atmanager_local'''
SET @supervisor = '''supervisor'''
SET @employee = '''employee'''
SET @recruiter = '''recruiter'''
SET @recruiter_local = '''recruiter_local'''
SET @curator = '''curator'''
SET @labor_safety = '''labor_safety'''
SET @labor_safety_local = '''labor_safety_local'''
SET @dean = '''dean'''
SET @dean_local = '''dean_local'''

SET @sqlCommand = 'SELECT mid as mid, dbo.GROUP_CONCAT(role) as role FROM
(select People.MID AS mid, ' + @enduser + ' AS role from People
union select teachers.MID AS mid, ' + @teacher + ' AS role from Teachers
union select moderators.user_id AS mid, ' + @moderator + ' AS role from moderators
union select students.MID AS mid, ' + @student + ' AS role from Students
union select Participants.MID AS mid, ' + @participant + ' AS role from Participants
union select admins.MID AS mid, ' + @admin + ' AS role from admins
union select simple_admins.MID AS mid, ' + @simple_admin + ' AS role from simple_admins
union select developers.mid AS mid, ' + @developer + ' AS role from developers
union select managers.mid AS mid, ' + @manager + ' AS role from managers
union select at_managers.user_id AS mid, ' + @atmanager + ' AS role from at_managers LEFT JOIN specialists ON at_managers.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct at_managers.user_id AS mid, ' + @atmanager_local + ' AS role FROM at_managers INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
union select supervisors.user_id AS mid, ' + @supervisor + ' AS role from supervisors
union select employee.user_id AS mid, ' + @employee + ' AS role from employee
union select recruiters.user_id AS mid,' + @recruiter + ' AS role from recruiters LEFT JOIN specialists ON recruiters.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct recruiters.user_id AS mid, ' + @recruiter_local + ' AS role FROM recruiters INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
union select curators.MID AS mid, ' + @curator + ' AS role from curators
union select labor_safety_specs.user_id AS mid, ' + @labor_safety + ' AS role from labor_safety_specs LEFT JOIN specialists ON labor_safety_specs.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct labor_safety_specs.user_id AS mid, ' + @labor_safety_local + ' AS role from labor_safety_specs INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
union select deans.MID AS mid, ' + @dean + ' AS role from deans LEFT JOIN specialists ON deans.MID = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct deans.MID AS mid, ' + @dean_local + ' AS role FROM deans INNER JOIN responsibilities ON deans.MID = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
) q
GROUP BY mid'

IF OBJECT_ID('roles') IS NULL
BEGIN
    EXEC('CREATE VIEW roles AS ' + @sqlCommand)
END
ELSE
BEGIN
    EXEC('ALTER VIEW roles AS ' + @sqlCommand)
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
        DECLARE @sqlCommand varchar(5000)

DECLARE @enduser VARCHAR(50)
DECLARE @supervisor VARCHAR(50)
DECLARE @developer VARCHAR(50)
DECLARE @moderator VARCHAR(50)
DECLARE @labor_safety_local VARCHAR(50)
DECLARE @dean_local VARCHAR(50)
DECLARE @atmanager_local VARCHAR(50)
DECLARE @hr_local VARCHAR(50)
DECLARE @manager VARCHAR(50)
DECLARE @curator VARCHAR(50)
DECLARE @labor_safety VARCHAR(50)
DECLARE @dean VARCHAR(50)
DECLARE @atmanager VARCHAR(50)
DECLARE @hr VARCHAR(50)
DECLARE @simple_admin VARCHAR(50)
DECLARE @admin VARCHAR(50)

SET @enduser = '''enduser'''
SET @supervisor = '''supervisor'''
SET @developer = '''developer'''
SET @moderator = '''moderator'''
SET @labor_safety_local = '''labor_safety_local'''
SET @dean_local = '''dean_local'''
SET @atmanager_local = '''atmanager_local'''
SET @hr_local = '''hr_local'''
SET @manager = '''manager'''
SET @curator = '''curator'''
SET @labor_safety = '''labor_safety'''
SET @dean = '''dean'''
SET @atmanager = '''atmanager'''
SET @hr = '''hr'''
SET @simple_admin = '''simple_admin'''
SET @admin = '''admin'''

SET @sqlCommand = '(select MID AS user_id, ' + @enduser + ' AS role, 10 as level from People where blocked != 1) UNION /* рядовой */
(select user_id AS user_id, ' + @supervisor + ' AS role, 20 as level from supervisors) UNION /* супервайзер (назначается руководителям подразделений, но не обязательно) */

(select distinct mid AS user_id, ' + @developer + ' AS role, 30 as level from developers) UNION /* разработчик БЗ */
/*(select distinct moderators.user_id AS user_id, ' + @moderator + ' AS role, 40 as level from moderators) UNION  модератор проектов (=teacher) не используется */
(select distinct labor_safety_specs.user_id AS user_id, ' + @labor_safety_local + ' AS role, 50 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION /* специалист по ОТ */
(select distinct deans.MID AS user_id, ' + @dean_local + ' AS role, 60 as level FROM deans INNER JOIN responsibilities ON deans.MID = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1) UNION /* спец.по обучению */
(select distinct at_managers.user_id AS user_id, ' + @atmanager_local + ' AS role, 70 as level from at_managers INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* спец.по оценке */
(select distinct recruiters.user_id AS user_id, ' + @hr_local + ' AS role, 80 as level from recruiters INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* спец.по персоналу */

(select distinct mid AS user_id, ' + @manager + ' AS role, 130 as level from managers) UNION /* менеджер БЗ */
(select distinct curators.MID AS user_id, ' + @curator + ' AS role, 140 as level from curators) UNION /* менеджер проектов (=dean) */
(select distinct labor_safety_specs.user_id AS user_id, ' + @labor_safety + ' AS role, 150 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* менеджер по ОТ */
(select distinct deans.MID AS user_id, ' + @dean + ' AS role, 160 as level from deans LEFT JOIN specialists ON deans.MID = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по обучению */
(select distinct at_managers.user_id AS user_id, ' + @atmanager +' AS role, 170 as level from at_managers LEFT JOIN specialists ON at_managers.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по оценке */
(select distinct recruiters.user_id AS user_id, ' + @hr + ' AS role, 180 as level from recruiters LEFT JOIN specialists ON recruiters.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* менеджер по персоналу */

(select distinct MID AS user_id, ' + @simple_admin + ' AS role, 300 as level from simple_admins) UNION  /* ограниченый админ */
(select distinct MID AS user_id, ' + @admin + ' AS role, 310 as level from admins)'

IF OBJECT_ID('roles_source') IS NULL
BEGIN
    EXEC('CREATE VIEW roles_source AS ' + @sqlCommand)
END
ELSE
BEGIN
    EXEC('ALTER VIEW roles_source AS ' + @sqlCommand)
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
DECLARE @numbers_separator VARCHAR(50)
DECLARE @numbers_string_separator VARCHAR(50)

SET @numbers_separator = '''. '''
SET @numbers_string_separator = '''. '''

        IF OBJECT_ID('at_profile_programm_events') IS NULL
BEGIN
    EXEC('CREATE VIEW at_profile_programm_events AS
  SELECT 
        p.profile_id,  
        dbo.CONCAT(0, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 0
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(1, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 1
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(2, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 2
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(6, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 6
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id')
END
ELSE
BEGIN
    EXEC('ALTER VIEW at_profile_programm_events AS
  SELECT 
        p.profile_id,  
        dbo.CONCAT(0, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 0
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(1, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 1
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(2, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 2
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(6, dbo.CONCAT(' + @numbers_separator + ', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, ' + @numbers_string_separator + '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 6
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
DECLARE @sqlCommand varchar(5000)

DECLARE @teacher VARCHAR(50)
DECLARE @enduser VARCHAR(50)
DECLARE @dean VARCHAR(50)
DECLARE @supervisor VARCHAR(50)
DECLARE @curator VARCHAR(50)
DECLARE @manager VARCHAR(50)
DECLARE @moderator VARCHAR(50)
DECLARE @developer VARCHAR(50)
DECLARE @project VARCHAR(50)
DECLARE @course VARCHAR(50)
DECLARE @resource VARCHAR(50)
DECLARE @subject VARCHAR(50)

SET @teacher = '''teacher'''
SET @enduser = '''enduser'''
SET @dean = '''dean'''
SET @supervisor = '''supervisor'''
SET @curator = '''curator'''
SET @manager = '''manager'''
SET @moderator = '''moderator'''
SET @developer = '''developer'''
SET @project = '''project'''
SET @course = '''course'''
SET @resource = '''resource'''
SET @subject = '''subject'''

SET @sqlCommand = '(SELECT mid, dbo.GROUP_CONCAT(role) AS role, subject_name, subject_id  FROM (
(select MID AS mid, ' + @teacher + ' AS role, ' +  @subject + ' AS subject_name, CID AS subject_id from Teachers WHERE CID > 0)
union (select MID AS mid, ' + @enduser + ' AS role, ' +  @subject + ' AS subject_name, CID AS subject_id from Students WHERE CID > 0) 
union (select MID AS mid, ' + @enduser + ' AS role, ' +  @subject + ' AS subject_name, CID AS subject_id from graduated WHERE CID > 0) 
union (select MID AS mid, ' + @dean + ' AS role, ' +  @subject + ' AS subject_name, 0 AS subject_id from deans WHERE subject_id = 0)
union (select MID as mid, ' + @dean + ' AS role, ' +  @subject + ' AS subject_name, subject_id AS subject_id from deans WHERE subject_id > 0)
union (select user_id AS mid, ' + @supervisor + ' AS role, ' +  @subject + ' AS subject_name, 0 AS subject_id from supervisors)
union (select MID AS mid, ' + @enduser + ' AS role, ' + @project + ' AS subject_name, CID AS subject_id from Participants WHERE CID > 0) 
union (select MID as mid, ' + @curator + ' AS role, ' + @project + ' AS subject_name, 0 AS subject_id from curators WHERE project_id = 0)
union (select MID as mid, ' + @curator + ' AS role, ' + @project + ' AS subject_name, project_id AS subject_id from curators WHERE project_id > 0)
union (select mid, ' + @manager + ' AS role, ' + @course + ' AS subject_name, 0 AS subject_id from managers)
union (select mid, ' + @developer + ' AS role, ' + @course + ' AS subject_name, 0 AS subject_id from developers)
union (select mid, ' + @manager + ' AS role, ' + @resource + ' AS subject_name, 0 AS subject_id from managers)
union (select mid, ' + @developer + ' AS role, ' + @resource + ' AS subject_name, 0 AS subject_id from developers)
union (SELECT user_id AS MID, ' + @moderator + ' AS role, ' + @project + ' AS subject_name, project_id AS subject_id FROM moderators WHERE project_id > 0)
) q GROUP BY mid, subject_name, subject_id)'

IF OBJECT_ID('activities') IS NULL
BEGIN
    EXEC('CREATE VIEW activities AS ' + @sqlCommand)
END
ELSE
BEGIN
    EXEC('ALTER VIEW activities AS ' + @sqlCommand)
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
DECLARE @sqlCommand varchar(7000)

DECLARE @project VARCHAR(50)
DECLARE @subject VARCHAR(50)
DECLARE @empty_str VARCHAR(50)

SET @project = '''project'''
SET @subject = '''subject'''
SET @empty_str = ''''''

SET @sqlCommand = '(select 
    2 AS activity_type,
    forums_sections.section_id AS activity_id,
    forums_sections.title AS activity_name,
    subjects.subid AS subject_id,
    forums_sections.subject AS subject,
    subjects.name AS subject_name,
    count(forums_messages.message_id) AS volume,
    max(forums_messages.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((((forums_sections join forums_list on((forums_list.forum_id = forums_sections.forum_id))) join subjects on((forums_list.subject_id = subjects.subid))) left join forums_messages on((forums_list.forum_id = forums_messages.forum_id))) left join resources on(((resources.activity_id = forums_sections.section_id) and (resources.activity_type = 2)))) 
  where 
    forums_sections.subject=' + @subject + ' AND (forums_sections.lesson_id IS NULL OR forums_sections.lesson_id = 0)
  group by 
    forums_list.forum_id, forums_sections.section_id, forums_sections.title, subjects.subid, forums_sections.subject, subjects.name, resources.resource_id, resources.status
  union 
  select 
    2 AS activity_type,
    forums_sections.section_id AS activity_id,
    forums_sections.title AS activity_name,
    projects.projid AS subject_id,
    forums_sections.subject AS subject,
    projects.name AS subject_name,
    count(forums_messages.message_id) AS volume,
    max(forums_messages.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status
  from
    ((((forums_sections join forums_list on((forums_list.forum_id = forums_sections.forum_id))) join projects on((forums_list.subject_id = projects.projid))) left join forums_messages on((forums_list.forum_id = forums_messages.forum_id))) left join resources on(((resources.activity_id = forums_sections.section_id) and (resources.activity_type = 2))))
  where
    forums_sections.subject=' + @project + ' AND (forums_sections.lesson_id IS NULL OR forums_sections.lesson_id = 0)
  group by
    forums_list.forum_id, forums_sections.section_id, forums_sections.title, projects.projid, forums_sections.subject, projects.name, resources.resource_id, resources.status
  union
  select
    64 AS activity_type,
    subjects.subid AS activity_id,
    ' + @empty_str + ' AS activity_name,
    subjects.subid AS subject_id,
    ' + @subject + ' AS subject,
    subjects.name AS subject_name,
    count(blog.id) AS volume,
    max(blog.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((subjects join blog on(((blog.subject_name = ' + @subject + ') and (blog.subject_id = subjects.subid)))) left join resources on(((resources.activity_id = subjects.subid) and (resources.activity_type = 64)))) 
  group by 
    subjects.subid, subjects.name, resources.resource_id, resources.status
  union 
  select 
    512 AS activity_type,
    chat_channels.id AS activity_id,
    chat_channels.name AS activity_name,
    subjects.subid AS subject_id,
    ' + @subject + ' AS subject,
    subjects.name AS subject_name,
    count(chat_history.id) AS volume,
    max(chat_history.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    (((chat_channels join subjects on((chat_channels.subject_id = subjects.subid))) left join chat_history on((chat_channels.id = chat_history.channel_id))) left join resources on(((resources.activity_id = chat_channels.id) and (resources.activity_type = 512)))) 
  where 
    (chat_channels.lesson_id IS NULL OR chat_channels.lesson_id = 0) AND
    is_general != 1 AND
    show_history = 1
  group by 
    chat_channels.id, chat_channels.name, subjects.subid, subjects.name, resources.resource_id, resources.status
  union 
  select 
    128 AS activity_type,
    subjects.subid AS activity_id,
    ' + @empty_str + ' AS activity_name,
    subjects.subid AS subject_id,
    ' + @subject + ' AS subject,
    subjects.name AS subject_name,
    count(wiki_articles.id) AS volume,
    max(wiki_articles.changed) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((subjects join wiki_articles on(((wiki_articles.subject_name = ' + @subject + ') and (wiki_articles.subject_id = subjects.subid)))) left join resources on(((resources.activity_id = subjects.subid) and (resources.activity_type = 128)))) 
  where 
    wiki_articles.lesson_id IS NULL
  group by 
    subjects.subid, subjects.name, resources.resource_id, resources.status)'

IF OBJECT_ID('activity_resources') IS NULL
BEGIN
    EXEC('CREATE VIEW activity_resources AS ' + @sqlCommand)
END
ELSE
BEGIN
    EXEC('ALTER VIEW activity_resources AS ' + @sqlCommand)
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('lessons') IS NULL
BEGIN
    EXEC('CREATE VIEW lessons AS
  (SELECT SHEID, title, typeID, timetype, descript, CID, createID, vedomost, teacher, moderator, cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, isfree, [order],
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
FROM schedule)')
END
ELSE
BEGIN
    EXEC('ALTER VIEW lessons AS
  (SELECT SHEID, title, typeID, timetype, descript, CID, createID, vedomost, teacher, moderator, cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, isfree, [order],
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
FROM schedule)')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('hours24') IS NULL
BEGIN
    EXEC('CREATE VIEW hours24 AS
  SELECT 0 AS h UNION 
SELECT 1 AS h UNION 
SELECT 2 AS h UNION 
SELECT 3 AS h UNION 
SELECT 4 AS h UNION 
SELECT 5 AS h UNION 
SELECT 6 AS h UNION 
SELECT 7 AS h UNION 
SELECT 8 AS h UNION 
SELECT 9 AS h UNION 
SELECT 10 AS h UNION 
SELECT 11 AS h UNION 
SELECT 12 AS h UNION 
SELECT 13 AS h UNION 
SELECT 14 AS h UNION 
SELECT 15 AS h UNION 
SELECT 16 AS h UNION 
SELECT 17 AS h UNION 
SELECT 18 AS h UNION 
SELECT 19 AS h UNION 
SELECT 20 AS h UNION 
SELECT 21 AS h UNION 
SELECT 22 AS h UNION 
SELECT 23 AS h')
END
ELSE
BEGIN
    EXEC('ALTER VIEW hours24 AS
  SELECT 0 AS h UNION 
SELECT 1 AS h UNION 
SELECT 2 AS h UNION 
SELECT 3 AS h UNION 
SELECT 4 AS h UNION 
SELECT 5 AS h UNION 
SELECT 6 AS h UNION 
SELECT 7 AS h UNION 
SELECT 8 AS h UNION 
SELECT 9 AS h UNION 
SELECT 10 AS h UNION 
SELECT 11 AS h UNION 
SELECT 12 AS h UNION 
SELECT 13 AS h UNION 
SELECT 14 AS h UNION 
SELECT 15 AS h UNION 
SELECT 16 AS h UNION 
SELECT 17 AS h UNION 
SELECT 18 AS h UNION 
SELECT 19 AS h UNION 
SELECT 20 AS h UNION 
SELECT 21 AS h UNION 
SELECT 22 AS h UNION 
SELECT 23 AS h')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('subjects_users') IS NULL
BEGIN
    EXEC('CREATE VIEW subjects_users AS
  (SELECT MID as user_id, CID as subject_id, begin_personal AS [begin], NULL AS [end], 1 AS status FROM Students
UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 0 AS status FROM claimants WHERE [status] = 0
UNION SELECT MID as user_id, CID as subject_id, [begin] AS [begin], [end] AS [end], 2 AS status FROM graduated
UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 3 AS status FROM Teachers)')
END
ELSE
BEGIN
    EXEC('ALTER VIEW subjects_users AS
  (SELECT MID as user_id, CID as subject_id, begin_personal AS [begin], NULL AS [end], 1 AS status FROM Students
UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 0 AS status FROM claimants WHERE [status] = 0
UNION SELECT MID as user_id, CID as subject_id, [begin] AS [begin], [end] AS [end], 2 AS status FROM graduated
UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 3 AS status FROM Teachers)')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
DECLARE @sqlCommand varchar(7000)

DECLARE @course VARCHAR(50)
DECLARE @test VARCHAR(50)
DECLARE @poll VARCHAR(50)
DECLARE @task VARCHAR(50)
DECLARE @resource VARCHAR(50)

SET @course = '''course'''
SET @test = '''test'''
SET @poll = '''poll'''
SET @task = '''task'''
SET @resource = '''resource'''

SET @sqlCommand = '(SELECT DISTINCT
      r.resource_id as id,
      ' + @resource + ' as [type],
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
      ' + @course + ' as [type],
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
      ' + @test + ' as [type],
      null as subtype,
      null as filetype,
      q.name as title,
      q.subject_id,
      q.status,
      null as created
  FROM
    questionnaires q
  WHERE
    q.type = ' + @test + '
UNION
  SELECT DISTINCT
      q.quest_id as id,
      ' + @poll + ' as [type],
      null as subtype,
      null as filetype,
      q.name as title,
      q.subject_id,
      q.status,
      null as created
  FROM
    questionnaires q
  WHERE
    q.type = ' + @poll + '
UNION
  SELECT DISTINCT
      t.task_id as id,
      ' + @task + ' as [type],
      null as subtype,
      null as filetype,
      t.title,
      t.subject_id,
      t.status,
      t.created
  FROM tasks t
)'

IF OBJECT_ID('materials') IS NULL
BEGIN
    EXEC('CREATE VIEW materials AS ' + @sqlCommand)
END
ELSE
BEGIN
    EXEC('ALTER VIEW materials AS ' + @sqlCommand)
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('criteria') IS NULL
BEGIN
    EXEC('CREATE VIEW criteria AS
  (SELECT criterion_id AS criterion_id, 1 as criterion_type, name AS name FROM at_criteria UNION 
SELECT criterion_id AS criterion_id, 2 as criterion_type, name AS name FROM at_criteria_test UNION 
SELECT criterion_id AS criterion_id, 3 as criterion_type, name AS name FROM at_criteria_personal)')
END
ELSE
BEGIN
    EXEC('ALTER VIEW criteria AS
  (SELECT criterion_id AS criterion_id, 1 as criterion_type, name AS name FROM at_criteria UNION 
SELECT criterion_id AS criterion_id, 2 as criterion_type, name AS name FROM at_criteria_test UNION 
SELECT criterion_id AS criterion_id, 3 as criterion_type, name AS name FROM at_criteria_personal)')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('subjects_fulltime_rating_sub_select') IS NULL
BEGIN
    EXEC('CREATE VIEW subjects_fulltime_rating_sub_select AS
  SELECT s.subid, s.base_id, COUNT(gr.mid) AS graduated, AVG(gr.effectivity) AS effectivity, AVG(sv.value) AS feedback
FROM subjects AS s
INNER JOIN graduated AS gr ON gr.CID = s.subid
LEFT JOIN tc_feedbacks AS f ON gr.CID = f.subject_id AND gr.MID=f.user_id
LEFT JOIN scale_values AS sv ON sv.value_id = f.mark
GROUP BY s.subid, s.base_id')
END
ELSE
BEGIN
    EXEC('ALTER VIEW subjects_fulltime_rating_sub_select AS
  SELECT s.subid, s.base_id, COUNT(gr.mid) AS graduated, AVG(gr.effectivity) AS effectivity, AVG(sv.value) AS feedback
FROM subjects AS s
INNER JOIN graduated AS gr ON gr.CID = s.subid
LEFT JOIN tc_feedbacks AS f ON gr.CID = f.subject_id AND gr.MID=f.user_id
LEFT JOIN scale_values AS sv ON sv.value_id = f.mark
GROUP BY s.subid, s.base_id')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('subjects_fulltime_rating') IS NULL
BEGIN
    EXEC('CREATE VIEW subjects_fulltime_rating AS
  SELECT s.subid, SUM(rt.graduated) AS graduated,
AVG(rt.feedback) AS rating
FROM Subjects s
INNER JOIN subjects_fulltime_rating_sub_select AS rt ON rt.base_id=s.subid
GROUP BY s.subid')
END
ELSE
BEGIN
    EXEC('ALTER VIEW subjects_fulltime_rating AS
  SELECT s.subid, SUM(rt.graduated) AS graduated,
AVG(rt.feedback) AS rating
FROM Subjects s
INNER JOIN subjects_fulltime_rating_sub_select AS rt ON rt.base_id=s.subid
GROUP BY s.subid')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('kbase_items') IS NULL
BEGIN
    EXEC('CREATE VIEW kbase_items AS
  (SELECT Courses.Status AS status, 2 as [type], Courses.Title AS title, Courses.CID AS id, Courses.createdate AS cdate FROM Courses WHERE Courses.Status = 1 AND Courses.chain IS NULL OR Courses.chain = 0 UNION 
SELECT resources.Status AS status, 1 as [type], resources.title AS title, resources.resource_id AS id, resources.created AS cdate FROM resources  WHERE resources.location=1 AND resources.Status = 1 AND resources.parent_id = 0)')
END
ELSE
BEGIN
    EXEC('ALTER VIEW kbase_items AS
  (SELECT Courses.Status AS status, 2 as [type], Courses.Title AS title, Courses.CID AS id, Courses.createdate AS cdate FROM Courses WHERE Courses.Status = 1 AND Courses.chain IS NULL OR Courses.chain = 0 UNION 
SELECT resources.Status AS status, 1 as [type], resources.title AS title, resources.resource_id AS id, resources.created AS cdate FROM resources  WHERE resources.location=1 AND resources.Status = 1 AND resources.parent_id = 0)')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('study_groups_auto_users') IS NULL
BEGIN
    EXEC('CREATE VIEW study_groups_auto_users AS
  (SELECT ga.group_id AS group_id,sou.mid AS user_id 
FROM ((study_groups_auto ga 
join structure_of_organ sod on((sod.soid = ga.department_id)))
join structure_of_organ sou on(((sou.lft >= sod.lft) 
    and (sou.rgt <= sod.rgt) 
    and (sou.code = ga.position_code)
    )))
    )')
END
ELSE
BEGIN
    EXEC('ALTER VIEW study_groups_auto_users AS
  (SELECT ga.group_id AS group_id,sou.mid AS user_id 
FROM ((study_groups_auto ga 
join structure_of_organ sod on((sod.soid = ga.department_id)))
join structure_of_organ sou on(((sou.lft >= sod.lft) 
    and (sou.rgt <= sod.rgt) 
    and (sou.code = ga.position_code)
    )))
    )')
END;
QUERY;
    $this->execute($query);

    $query = <<<QUERY
    IF OBJECT_ID('study_groups_users') IS NULL
BEGIN
    EXEC('CREATE VIEW study_groups_users AS
  SELECT study_groups_custom.group_id AS group_id,study_groups_custom.user_id AS user_id,1 AS type 
FROM study_groups_custom 
UNION 
SELECT study_groups_auto_users.group_id AS group_id,study_groups_auto_users.user_id AS user_id,2 AS type 
FROM study_groups_auto_users')
END
ELSE
BEGIN
    EXEC('ALTER VIEW study_groups_users AS
  SELECT study_groups_custom.group_id AS group_id,study_groups_custom.user_id AS user_id,1 AS type 
FROM study_groups_custom 
UNION 
SELECT study_groups_auto_users.group_id AS group_id,study_groups_auto_users.user_id AS user_id,2 AS type 
FROM study_groups_auto_users')
END;
QUERY;

    $this->execute($query);
  }

  private function changeMysql()
  {
    $query = <<<QUERY
CREATE OR REPLACE VIEW specialists AS
  SELECT
    responsibilities.user_id, responsibilities.responsibility_id
  FROM
    responsibilities
  WHERE
    responsibilities.item_type = 1
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW roles_sub_select AS
select People.MID AS mid, 'enduser' AS role from People
union select Teachers.MID AS mid, 'teacher' AS role from Teachers
union select moderators.user_id AS mid, 'moderator' AS role from moderators
union select Students.MID AS mid, 'student' AS role from Students
union select Participants.MID AS mid, 'participant' AS role from Participants
union select admins.MID AS mid, 'admin' AS role from admins
union select simple_admins.MID AS mid, 'simple_admin' AS role from simple_admins
union select developers.mid AS mid, 'developer' AS role from developers
union select managers.mid AS mid, 'manager' AS role from managers
union select at_managers.user_id AS mid, 'atmanager' AS role from at_managers LEFT JOIN specialists ON at_managers.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct at_managers.user_id AS mid, 'atmanager_local' AS role FROM at_managers INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
union select supervisors.user_id AS mid, 'supervisor' AS role from supervisors
union select employee.user_id AS mid, 'employee' AS role from employee
union select recruiters.user_id AS mid,'recruiter' AS role from recruiters LEFT JOIN specialists ON recruiters.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct recruiters.user_id AS mid, 'recruiter_local' AS role FROM recruiters INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
union select curators.MID AS mid, 'curator' AS role from curators
union select labor_safety_specs.user_id AS mid, 'labor_safety' AS role from labor_safety_specs LEFT JOIN specialists ON labor_safety_specs.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct labor_safety_specs.user_id AS mid, 'labor_safety_local' AS role from labor_safety_specs INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
union select deans.MID AS mid, 'dean' AS role from deans LEFT JOIN specialists ON deans.MID = specialists.user_id WHERE specialists.responsibility_id IS NULL
union select distinct deans.MID AS mid, 'dean_local' AS role FROM deans INNER JOIN responsibilities ON deans.MID = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW roles AS
SELECT mid as mid, GROUP_CONCAT(role) as role FROM roles_sub_select q
GROUP BY mid
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW roles_source AS

(select MID AS user_id, 'enduser' AS role, 10 as level from People where blocked != 1) UNION /* рядовой */
(select user_id AS user_id, 'supervisor' AS role, 20 as level from supervisors) UNION /* супервайзер (назначается руководителям подразделений, но не обязательно) */

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
(select distinct MID AS user_id, 'admin' AS role, 310 as level from admins)
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW at_profile_programm_events AS
    SELECT 
        p.profile_id,  
        CONCAT(0, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name)))  as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 0
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        CONCAT(1, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name)))  as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 1
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        CONCAT(2, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name)))   as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 2
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        CONCAT(6, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name)))  as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 6
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW activities_sub_select AS 
(select MID AS mid, 'teacher' AS role, 'subject' AS subject_name, CID AS subject_id from Teachers WHERE CID > 0)
union (select MID AS mid, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id from Students WHERE CID > 0) 
union (select MID AS mid, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id from graduated WHERE CID > 0) 
union (select MID AS mid, 'dean' AS role, 'subject' AS subject_name, 0 AS subject_id from deans WHERE subject_id = 0)
union (select MID as mid, 'dean' AS role, 'subject' AS subject_name, subject_id AS subject_id from deans WHERE subject_id > 0)
union (select user_id AS mid, 'supervisor' AS role, 'subject' AS subject_name, 0 AS subject_id from supervisors)
union (select MID AS mid, 'enduser' AS role, 'project' AS subject_name, CID AS subject_id from Participants WHERE CID > 0) 
union (select MID as mid, 'curator' AS role, 'project' AS subject_name, 0 AS subject_id from curators WHERE project_id = 0)
union (select MID as mid, 'curator' AS role, 'project' AS subject_name, project_id AS subject_id from curators WHERE project_id > 0)
union (select mid, 'manager' AS role, 'course' AS subject_name, 0 AS subject_id from managers)
union (select mid, 'developer' AS role, 'course' AS subject_name, 0 AS subject_id from developers)
union (select mid, 'manager' AS role, 'resource' AS subject_name, 0 AS subject_id from managers)
union (select mid, 'developer' AS role, 'resource' AS subject_name, 0 AS subject_id from developers)
union (SELECT user_id AS MID, 'moderator' AS role, 'project' AS subject_name, project_id AS subject_id FROM moderators WHERE project_id > 0)
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW activities AS 
(SELECT mid, GROUP_CONCAT(role) AS role, subject_name, subject_id  FROM activities_sub_select q GROUP BY mid, subject_name, subject_id);
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW activity_resources AS 
select 
    2 AS activity_type,
    forums_sections.section_id AS activity_id,
    forums_sections.title AS activity_name,
    subjects.subid AS subject_id,
    forums_sections.subject AS subject,
    subjects.name AS subject_name,
    count(forums_messages.message_id) AS volume,
    max(forums_messages.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((((forums_sections join forums_list on((forums_list.forum_id = forums_sections.forum_id))) join subjects on((forums_list.subject_id = subjects.subid))) left join forums_messages on((forums_list.forum_id = forums_messages.forum_id))) left join resources on(((resources.activity_id = forums_sections.section_id) and (resources.activity_type = 2)))) 
  where 
    forums_sections.subject='subject' AND (forums_sections.lesson_id IS NULL OR forums_sections.lesson_id = 0)
  group by 
    forums_list.forum_id, forums_sections.section_id, forums_sections.title, subjects.subid, forums_sections.subject, subjects.name, resources.resource_id, resources.status
  union 
  select 
    2 AS activity_type,
    forums_sections.section_id AS activity_id,
    forums_sections.title AS activity_name,
    projects.projid AS subject_id,
    forums_sections.subject AS subject,
    projects.name AS subject_name,
    count(forums_messages.message_id) AS volume,
    max(forums_messages.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status
  from
    ((((forums_sections join forums_list on((forums_list.forum_id = forums_sections.forum_id))) join projects on((forums_list.subject_id = projects.projid))) left join forums_messages on((forums_list.forum_id = forums_messages.forum_id))) left join resources on(((resources.activity_id = forums_sections.section_id) and (resources.activity_type = 2))))
  where
    forums_sections.subject='project' AND (forums_sections.lesson_id IS NULL OR forums_sections.lesson_id = 0)
  group by
    forums_list.forum_id, forums_sections.section_id, forums_sections.title, projects.projid, forums_sections.subject, projects.name, resources.resource_id, resources.status
  union
  select
    64 AS activity_type,
    subjects.subid AS activity_id,
    '' AS activity_name,
    subjects.subid AS subject_id,
    'subject' AS subject,
    subjects.name AS subject_name,
    count(blog.id) AS volume,
    max(blog.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((subjects join blog on(((blog.subject_name = 'subject') and (blog.subject_id = subjects.subid)))) left join resources on(((resources.activity_id = subjects.subid) and (resources.activity_type = 64)))) 
  group by 
    subjects.subid, subjects.name, resources.resource_id, resources.status
  union 
  select 
    512 AS activity_type,
    chat_channels.id AS activity_id,
    chat_channels.name AS activity_name,
    subjects.subid AS subject_id,
    'subject' AS subject,
    subjects.name AS subject_name,
    count(chat_history.id) AS volume,
    max(chat_history.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    (((chat_channels join subjects on((chat_channels.subject_id = subjects.subid))) left join chat_history on((chat_channels.id = chat_history.channel_id))) left join resources on(((resources.activity_id = chat_channels.id) and (resources.activity_type = 512)))) 
  where 
    (chat_channels.lesson_id IS NULL OR chat_channels.lesson_id = 0) AND
    is_general != 1 AND
  show_history = 1
  group by 
    chat_channels.id, chat_channels.name, subjects.subid, subjects.name, resources.resource_id, resources.status
  union 
  select 
    128 AS activity_type,
    subjects.subid AS activity_id,
    '' AS activity_name,
    subjects.subid AS subject_id,
    'subject' AS subject,
    subjects.name AS subject_name,
    count(wiki_articles.id) AS volume,
    max(wiki_articles.changed) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((subjects join wiki_articles on(((wiki_articles.subject_name = 'subject') and (wiki_articles.subject_id = subjects.subid)))) left join resources on(((resources.activity_id = subjects.subid) and (resources.activity_type = 128)))) 
  where 
    wiki_articles.lesson_id IS NULL
  group by 
    subjects.subid, subjects.name, resources.resource_id, resources.status
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW lessons AS
(
SELECT SHEID, title, typeID, timetype, descript, CID, createID, vedomost, teacher, moderator, 
cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, isfree, `order`,
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
);
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW hours24 AS 
SELECT 0 AS h UNION 
SELECT 1 AS h UNION 
SELECT 2 AS h UNION 
SELECT 3 AS h UNION 
SELECT 4 AS h UNION 
SELECT 5 AS h UNION 
SELECT 6 AS h UNION 
SELECT 7 AS h UNION 
SELECT 8 AS h UNION 
SELECT 9 AS h UNION 
SELECT 10 AS h UNION 
SELECT 11 AS h UNION 
SELECT 12 AS h UNION 
SELECT 13 AS h UNION 
SELECT 14 AS h UNION 
SELECT 15 AS h UNION 
SELECT 16 AS h UNION 
SELECT 17 AS h UNION 
SELECT 18 AS h UNION 
SELECT 19 AS h UNION 
SELECT 20 AS h UNION 
SELECT 21 AS h UNION 
SELECT 22 AS h UNION 
SELECT 23 AS h;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW criteria AS 
SELECT criterion_id AS criterion_id, 1 as criterion_type, name AS name FROM at_criteria UNION 
SELECT criterion_id AS criterion_id, 2 as criterion_type, name AS name FROM at_criteria_test UNION 
SELECT criterion_id AS criterion_id, 3 as criterion_type, name AS name FROM at_criteria_personal
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW subjects_users AS
SELECT MID as user_id, CID as subject_id, begin_personal AS `begin`, NULL AS `end`, 1 AS status FROM Students
UNION SELECT MID as user_id, CID as subject_id, NULL AS `begin`, NULL AS `end`, 0 AS status FROM claimants WHERE `status` = 0
UNION SELECT MID as user_id, CID as subject_id, `begin` AS `begin`, `end` AS `end`, 2 AS status FROM graduated
UNION SELECT MID as user_id, CID as subject_id, NULL AS `begin`, NULL AS `end`, 3 AS status FROM Teachers
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW kbase_items AS
SELECT Courses.Status AS status, 2 as `type`, Courses.Title AS title, Courses.CID AS id, Courses.createdate AS cdate FROM Courses WHERE Courses.Status = 1 AND Courses.chain IS NULL OR Courses.chain = 0
UNION 
SELECT resources.Status AS status, 1 as `type`, resources.title AS title, resources.resource_id AS id, resources.created AS cdate FROM resources  WHERE resources.location=1 AND resources.Status = 1 AND resources.parent_id = 0
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW study_groups_auto_users AS 
(SELECT ga.group_id AS group_id,sou.mid AS user_id 
FROM ((study_groups_auto ga 
join structure_of_organ sod on((sod.soid = ga.department_id)))
join structure_of_organ sou on(((sou.lft >= sod.lft) 
  and (sou.rgt <= sod.rgt) 
  and (sou.code = ga.position_code)
  )))
  )
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW study_groups_users AS 
SELECT study_groups_custom.group_id AS group_id,study_groups_custom.user_id AS user_id,1 AS type 
FROM study_groups_custom 
UNION 
SELECT study_groups_auto_users.group_id AS group_id,study_groups_auto_users.user_id AS user_id,2 AS type 
FROM study_groups_auto_users
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW `materials` AS
  SELECT DISTINCT
    r.resource_id as id,
    2052 as `type`,
    r.type as subtype,
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
    c.type as subtype,
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
    t.title,
    t.subject_id,
    t.status,
    t.created
  FROM tasks t
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW subjects_fulltime_rating_sub_select AS 
SELECT s.subid, s.base_id, COUNT(gr.mid) AS graduated, AVG(gr.effectivity) AS effectivity, AVG(sv.value) AS feedback
FROM subjects AS s
INNER JOIN graduated AS gr ON gr.CID = s.subid
LEFT JOIN tc_feedbacks AS f ON gr.CID = f.subject_id AND gr.MID=f.user_id
LEFT JOIN scale_values AS sv ON sv.value_id = f.mark
GROUP BY s.subid, s.base_id
;
QUERY;
    $this->execute($query);
    $query = <<<QUERY
CREATE OR REPLACE VIEW `subjects_fulltime_rating` AS 
SELECT s.subid, 
SUM(rt.graduated) AS graduated,
AVG(rt.feedback) AS rating
FROM subjects s
INNER JOIN subjects_fulltime_rating_sub_select AS rt ON rt.base_id=s.subid
GROUP BY s.subid
;
QUERY;

    $this->execute($query);
  }
}
