CREATE VIEW specialists AS
  SELECT
    responsibilities.user_id, responsibilities.responsibility_id
  FROM
    responsibilities
  WHERE
    responsibilities.item_type = 1
;
GO

CREATE VIEW roles AS
SELECT mid as mid, dbo.GROUP_CONCAT(role) as role FROM
(select People.MID AS mid, 'enduser' AS role from People
union select teachers.MID AS mid, 'teacher' AS role from Teachers
union select moderators.user_id AS mid, 'moderator' AS role from moderators
union select students.MID AS mid, 'student' AS role from Students
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
) q
GROUP BY mid;
GO

IF OBJECT_ID('dbo.roles_source', 'V') IS NOT NULL
    DROP VIEW dbo.roles_source
GO
CREATE VIEW roles_source AS

(select MID AS user_id, 'enduser' AS role, 10 as level from People where blocked != 1) UNION /* ������� */
(select user_id AS user_id, 'supervisor' AS role, 20 as level from supervisors) UNION /* ����������� (����������� ������������� �������������, �� �� �����������) */
(select distinct MID AS mid, 'teacher' AS role, 25 as level from Teachers) UNION
(select distinct mid AS user_id, 'developer' AS role, 30 as level from developers) UNION /* ����������� �� */
/*(select distinct moderators.user_id AS user_id, 'moderator' AS role, 40 as level from moderators) UNION  ��������� �������� (=teacher) �� ������������ */
(select distinct labor_safety_specs.user_id AS user_id, 'labor_safety_local' AS role, 50 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION /* ���������� �� �� */
(select distinct deans.MID AS user_id, 'dean_local' AS role, 60 as level FROM deans INNER JOIN responsibilities ON deans.MID = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL AND responsibilities.item_type = 1) UNION /* ����.�� �������� */
(select distinct at_managers.user_id AS user_id, 'atmanager_local' AS role, 70 as level from at_managers INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* ����.�� ������ */
(select distinct recruiters.user_id AS user_id, 'hr_local' AS role, 80 as level from recruiters INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* ����.�� ��������� */

(select distinct mid AS user_id, 'manager' AS role, 130 as level from managers) UNION /* �������� �� */
(select distinct curators.MID AS user_id, 'curator' AS role, 140 as level from curators) UNION /* �������� �������� (=dean) */
(select distinct labor_safety_specs.user_id AS user_id, 'labor_safety' AS role, 150 as level from labor_safety_specs  INNER JOIN responsibilities ON labor_safety_specs.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL) UNION  /* �������� �� �� */
(select distinct deans.MID AS user_id, 'dean' AS role, 160 as level from deans LEFT JOIN specialists ON deans.MID = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* �������� �� �������� */
(select distinct at_managers.user_id AS user_id, 'atmanager' AS role, 170 as level from at_managers LEFT JOIN specialists ON at_managers.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* �������� �� ������ */
(select distinct recruiters.user_id AS user_id, 'hr' AS role, 180 as level from recruiters LEFT JOIN specialists ON recruiters.user_id = specialists.user_id WHERE specialists.responsibility_id IS NULL) UNION  /* �������� �� ��������� */

(select distinct MID AS user_id, 'simple_admin' AS role, 300 as level from simple_admins) UNION  /* ����������� ����� */
(select distinct MID AS user_id, 'admin' AS role, 310 as level from admins)
;
GO

CREATE VIEW at_profile_programm_events AS
    SELECT 
        p.profile_id,  
        dbo.CONCAT(0, dbo.CONCAT('-', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 0
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(1, dbo.CONCAT('-', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 1
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(2, dbo.CONCAT('-', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 2
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT 
        p.profile_id,  
        dbo.CONCAT(6, dbo.CONCAT('-', dbo.CONCAT(dbo.CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 6
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id;
GO

CREATE VIEW activities AS 
(SELECT mid, dbo.GROUP_CONCAT(role) AS role, subject_name, subject_id  FROM (
(select MID AS mid, 'teacher' AS role, 'subject' AS subject_name, CID AS subject_id from Teachers WHERE CID > 0)
UNION (select MID AS mid, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id from Students WHERE CID > 0) 
UNION (select MID AS mid, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id from graduated WHERE CID > 0) 
UNION (select MID AS mid, 'dean' AS role, 'subject' AS subject_name, 0 AS subject_id from deans WHERE subject_id = 0)
UNION (select MID as mid, 'dean' AS role, 'subject' AS subject_name, subject_id AS subject_id from deans WHERE subject_id > 0)
UNION (select user_id AS mid, 'supervisor' AS role, 'subject' AS subject_name, 0 AS subject_id from supervisors)
UNION (select MID AS mid, 'enduser' AS role, 'project' AS subject_name, CID AS subject_id from Participants WHERE CID > 0) 
UNION (select MID as mid, 'curator' AS role, 'project' AS subject_name, 0 AS subject_id from curators WHERE project_id = 0)
UNION (select MID as mid, 'curator' AS role, 'project' AS subject_name, project_id AS subject_id from curators WHERE project_id > 0)
UNION (select mid, 'manager' AS role, 'course' AS subject_name, 0 AS subject_id from managers)
UNION (select mid, 'developer' AS role, 'course' AS subject_name, 0 AS subject_id from developers)
UNION (select mid, 'manager' AS role, 'resource' AS subject_name, 0 AS subject_id from managers)
UNION (select mid, 'developer' AS role, 'resource' AS subject_name, 0 AS subject_id from developers)
UNION (SELECT user_id AS MID, 'moderator' AS role, 'project' AS subject_name, projid AS subject_id FROM moderators WHERE projid > 0)
)q GROUP BY mid, subject_name, subject_id);
GO

CREATE VIEW activity_resources AS (
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
  UNION 
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
  UNION
  select
    64 AS activity_type,
    subjects.subid AS activity_id,
    '' AS activity_name,
    subjects.subid AS subject_id,
    'subject' AS subject
    subjects.name AS subject_name,
    count(blog.id) AS volume,
    max(blog.created) AS updated,
    resources.resource_id AS resource_id,
    resources.status AS status 
  from 
    ((subjects join blog on(((blog.subject_name = 'subject') and (blog.subject_id = subjects.subid)))) left join resources on(((resources.activity_id = subjects.subid) and (resources.activity_type = 64)))) 
  group by 
    subjects.subid, subjects.name, resources.resource_id, resources.status
  UNION 
  select 
    512 AS activity_type,
    chat_channels.id AS activity_id,
    chat_channels.name AS activity_name,
    subjects.subid AS subject_id,
    'subject' AS subject
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
  UNION 
  select 
    128 AS activity_type,
    subjects.subid AS activity_id,
    '' AS activity_name,
    subjects.subid AS subject_id,
    'subject' AS subject
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
);
GO

CREATE VIEW lessons AS
(
SELECT SHEID, title, typeID, timetype, descript, CID, createID, vedomost, teacher, moderator, cond_sheid, cond_mark, cond_progress, cond_avgbal, cond_sumbal, isfree, [order],
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
GO

CREATE VIEW hours24 AS 
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
GO

CREATE VIEW subjects_users AS
(SELECT MID as user_id, CID as subject_id, begin_personal AS [begin], NULL AS [end], 1 AS status FROM Students
UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 0 AS status FROM claimants WHERE [status] = 0
UNION SELECT MID as user_id, CID as subject_id, [begin] AS [begin], [end] AS [end], 2 AS status FROM graduated
UNION SELECT MID as user_id, CID as subject_id, NULL AS [begin], NULL AS [end], 3 AS status FROM Teachers
);
GO

CREATE VIEW materials AS
(
	SELECT DISTINCT
		r.resource_id as id,
		2052 as [type],
		r.type as subtype,
		r.title,
		r.subject_id,
		r.Status as status,
		r.created
	FROM
	  resources r
	WHERE
		r.parent_id=0
UNION
	SELECT DISTINCT
		c.CID as id,
		2050 as [type],
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
		2048 as [type],
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
		2053 as [type],
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
		2054 as [type],
		null as subtype,
		t.title,
		t.subject_id,
		t.status,
		t.created
	FROM tasks t
);
GO

CREATE VIEW criteria AS 
(SELECT criterion_id AS criterion_id, 1 as criterion_type, name AS name FROM at_criteria UNION 
SELECT criterion_id AS criterion_id, 2 as criterion_type, name AS name FROM at_criteria_test UNION 
SELECT criterion_id AS criterion_id, 3 as criterion_type, name AS name FROM at_criteria_personal); 
GO

CREATE OR REPLACE VIEW subjects_fulltime_rating AS
SELECT s.subid, SUM(rt.graduated) AS graduated,
AVG(rt.feedback) AS rating
FROM Subjects s
INNER JOIN (SELECT s.subid, s.base_id, COUNT(gr.mid) AS graduated, AVG(gr.effectivity) AS effectivity, AVG(sv.value) AS feedback
        FROM subjects AS s
        INNER JOIN graduated AS gr ON gr.CID = s.subid
        LEFT JOIN tc_feedbacks AS f ON gr.CID = f.subject_id AND gr.MID=f.user_id
        LEFT JOIN scale_values AS sv ON sv.value_id = f.mark
        GROUP BY s.subid, s.base_id) AS rt ON rt.base_id=s.subid
GROUP BY s.subid

CREATE VIEW session_department_fact_price AS
SELECT
    tca.session_department_id,
    SUM(s.price) AS fact_price
FROM
    dbo.tc_applications AS tca
    LEFT OUTER JOIN dbo.subjects AS s ON tca.subject_id = s.subid
WHERE
    tca.session_department_id IS NOT NULL
    AND tca.category IN (2, 3)
    AND tca.status IN (1, 2)
GROUP BY
    tca.session_department_id
