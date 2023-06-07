CREATE VIEW specialists AS
  SELECT
    responsibilities.user_id, responsibilities.responsibility_id
  FROM
    responsibilities
  WHERE
    responsibilities.item_type = 1;



-- SELECT `People`.`MID` AS `mid`,_utf8'enduser' AS `role` FROM `People`
-- UNION SELECT `Teachers`.`MID` AS `mid`,_utf8'teacher' AS `role` FROM `Teachers`
-- UNION SELECT `moderators`.`user_id` AS `mid`, _utf8 'moderator' AS `role` FROM `moderators`
-- UNION SELECT `Participants`.`MID` AS `mid`,_utf8'participant' AS `role` FROM `Participants`
-- UNION SELECT `Students`.`MID` AS `mid`,_utf8'student' AS `role` FROM `Students`
-- UNION SELECT `admins`.`MID` AS `mid`,_utf8'admin' AS `role` FROM `admins`
-- UNION SELECT `developers`.`mid` AS `mid`,_utf8'developer' AS `role` FROM `developers`
-- UNION SELECT `managers`.`mid` AS `mid`,_utf8'manager' AS `role` FROM `managers`
-- UNION SELECT `at_managers`.`user_id` AS `mid`,_utf8'atmanager' AS `role` FROM `at_managers` LEFT JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NULL
-- UNION SELECT DISTINCT `at_managers`.`user_id` AS `mid`,_utf8'atmanager_local' AS `role` FROM `at_managers` INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL
-- UNION SELECT `supervisors`.user_id AS MID, _utf8'supervisor' AS `role` FROM `supervisors`
-- union select `employee`.`user_id` AS `MID`,_utf8'employee' AS `role` from `employee`
-- UNION SELECT `recruiters`.`user_id` AS `mid`,_utf8'recruiter' AS `role` from `recruiters` LEFT JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NULL
-- UNION SELECT DISTINCT `recruiters`.`user_id` AS `mid`,_utf8'recruiter_local' AS `role` FROM `recruiters` INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL
-- UNION SELECT `curators`.`MID` AS `mid`,_utf8'curator' AS `role` FROM `curators`
-- UNION SELECT `deans`.`MID` AS `mid`,_utf8'dean' AS `role` FROM `deans`;



-- copied from MS SQL
CREATE OR REPLACE VIEW roles_source AS

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

-- copy from MS SQL (anonymous subqueries not allowed)
CREATE VIEW roles_subquery AS
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

-- copy from MS SQL
CREATE VIEW roles AS
SELECT mid as mid, GROUP_CONCAT(role) as role FROM roles_subquery
GROUP BY mid;


-- CREATE OR REPLACE VIEW `roles` AS
-- SELECT MID, GROUP_CONCAT(role) AS role FROM roles_source GROUP BY MID;


CREATE VIEW at_profile_programm_events AS
    SELECT
        p.profile_id,
        CONCAT(0, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 0
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT
        p.profile_id,
        CONCAT(1, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 1
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT
        p.profile_id,
        CONCAT(2, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 2
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id
UNION
    SELECT
        p.profile_id,
        CONCAT(6, CONCAT('-', CONCAT(CONCAT(pre.ordr+1, '. '), pre.name))) as name
    FROM at_profiles AS p
    LEFT JOIN programm AS pr ON p.profile_id = pr.item_id AND pr.item_type = 1 AND pr.programm_type = 6
    LEFT JOIN programm_events AS pre ON pr.programm_id = pre.programm_id;

CREATE OR REPLACE VIEW activities_source AS
SELECT MID AS MID, 'teacher' AS role, 'subject' AS subject_name, CID AS subject_id FROM Teachers WHERE CID > 0 
UNION SELECT MID AS MID, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id FROM Students WHERE CID > 0 
UNION SELECT MID AS MID, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id FROM graduated WHERE CID > 0 
UNION SELECT MID AS MID, 'dean' AS role, 'subject' AS subject_name, 0 AS subject_id FROM deans WHERE subject_id = 0
UNION SELECT user_id AS MID, 'supervisor' AS role, 'subject' AS subject_name, 0 AS subject_id FROM supervisors
UNION SELECT MID AS MID, 'dean' AS role, 'subject' AS subject_name, subject_id AS subject_id FROM deans WHERE subject_id > 0
UNION SELECT MID AS MID, 'enduser' AS role, 'project' AS subject_name, CID AS subject_id FROM Participants WHERE CID > 0
UNION SELECT MID AS MID, 'curator' AS role, 'project' AS subject_name, 0 AS subject_id FROM curators WHERE project_id = 0
UNION SELECT MID AS MID, 'curator' AS role, 'project' AS subject_name, project_id AS subject_id FROM curators WHERE project_id > 0
UNION SELECT mid AS MID, 'manager' AS role, 'course' AS subject_name, 0 AS subject_id FROM managers
UNION SELECT mid AS MID, 'developer' AS role, 'course' AS subject_name, 0 AS subject_id FROM developers
UNION SELECT mid AS MID, 'manager' AS role, 'resource' AS subject_name, 0 AS subject_id FROM managers
UNION SELECT mid AS MID, 'developer' AS role, 'resource' AS subject_name, 0 AS subject_id FROM developers
UNION SELECT user_id AS MID, 'moderator' AS role, 'project' AS subject_name, project_id AS subject_id FROM moderators WHERE project_id > 0
;

CREATE OR REPLACE VIEW activities AS 
SELECT MID, GROUP_CONCAT(role) AS role, subject_name, subject_id  FROM activities_source
GROUP BY MID, subject_name, subject_id;

CREATE OR REPLACE VIEW activity_resources AS 
select 
    2 AS `activity_type`,
    `forums_sections`.`section_id` AS `activity_id`,
    `forums_sections`.`title` AS `activity_name`,
    `subjects`.`subid` AS `subject_id`,
    `forums_sections`.`subject` AS `subject`,
    `subjects`.`name` AS `subject_name`,
    count(`forums_messages`.`message_id`) AS `volume`,
    max(`forums_messages`.`created`) AS `updated`,
    `resources`.`resource_id` AS `resource_id`,
    `resources`.`status` AS `status` 
  from 
    ((((`forums_sections` join `forums_list` on((`forums_list`.`forum_id` = `forums_sections`.`forum_id`))) join `subjects` on((`forums_list`.`subject_id` = `subjects`.`subid`) AND (`forums_list`.`subject` = 'subject'))) left join `forums_messages` on((`forums_list`.`forum_id` = `forums_messages`.`forum_id`))) left join `resources` on(((`resources`.`activity_id` = `forums_sections`.`section_id`) and (`resources`.`activity_type` = 2)))) 
  where 
    `forums_sections`.`subject`='subject' AND (isnull(`forums_sections`.`lesson_id`) OR (`forums_sections`.`lesson_id` = 0))
  group by 
    `forums_list`.`forum_id` union 
  select 
    2 AS `activity_type`,
    `forums_sections`.`section_id` AS `activity_id`,
    `forums_sections`.`title` AS `activity_name`,
    `projects`.`projid` AS `subject_id`,
    `forums_sections`.`subject` AS `subject`,
    `projects`.`name` AS `subject_name`,
    count(`forums_messages`.`message_id`) AS `volume`,
    max(`forums_messages`.`created`) AS `updated`,
    `resources`.`resource_id` AS `resource_id`,
    `resources`.`status` AS `status`
  from
    ((((`forums_sections` join `forums_list` on((`forums_list`.`forum_id` = `forums_sections`.`forum_id`))) join `projects` on((`forums_list`.`subject_id` = `projects`.`projid`) AND (`forums_list`.`subject` = 'project'))) left join `forums_messages` on((`forums_list`.`forum_id` = `forums_messages`.`forum_id`))) left join `resources` on(((`resources`.`activity_id` = `forums_sections`.`section_id`) and (`resources`.`activity_type` = 2))))
  where
    `forums_sections`.`subject`='project' AND (isnull(`forums_sections`.`lesson_id`) OR (`forums_sections`.`lesson_id` = 0))
  group by
    `forums_list`.`forum_id` union
  select
    64 AS `activity_type`,
    `subjects`.`subid` AS `activity_id`,
    '' AS `activity_name`,
    `subjects`.`subid` AS `subject_id`,
    'subject' AS `subject`,
    `subjects`.`name` AS `subject_name`,
    count(`blog`.`id`) AS `volume`,
    max(`blog`.`created`) AS `updated`,
    `resources`.`resource_id` AS `resource_id`,
    `resources`.`status` AS `status` 
  from 
    ((`subjects` join `blog` on(((`blog`.`subject_name` = 'subject') and (`blog`.`subject_id` = `subjects`.`subid`)))) left join `resources` on(((`resources`.`activity_id` = `subjects`.`subid`) and (`resources`.`activity_type` = 64)))) 
  group by 
    `subjects`.`subid` union 
  select 
    512 AS `activity_type`,
    `chat_channels`.`id` AS `activity_id`,
    `chat_channels`.`name` AS `activity_name`,
    `subjects`.`subid` AS `subject_id`,
    'subject' AS `subject`,
    `subjects`.`name` AS `subject_name`,
    count(`chat_history`.`id`) AS `volume`,
    max(`chat_history`.`created`) AS `updated`,
    `resources`.`resource_id` AS `resource_id`,
    `resources`.`status` AS `status` 
  from 
    (((`chat_channels` join `subjects` on((`chat_channels`.`subject_id` = `subjects`.`subid`))) left join `chat_history` on((`chat_channels`.`id` = `chat_history`.`channel_id`))) left join `resources` on(((`resources`.`activity_id` = `chat_channels`.`id`) and (`resources`.`activity_type` = 512)))) 
  where 
    (isnull(`chat_channels`.`lesson_id`) OR `chat_channels`.`lesson_id` = 0) AND
    !is_general AND
	show_history = 1
  group by 
    `chat_channels`.`id` union 
  select 
    128 AS `activity_type`,
    `subjects`.`subid` AS `activity_id`,
    '' AS `activity_name`,
    `subjects`.`subid` AS `subject_id`,
    'subject' AS `subject`,
    `subjects`.`name` AS `subject_name`,
    count(`wiki_articles`.`id`) AS `volume`,
    max(`wiki_articles`.`changed`) AS `updated`,
    `resources`.`resource_id` AS `resource_id`,
    `resources`.`status` AS `status` 
  from 
    ((`subjects` join `wiki_articles` on(((`wiki_articles`.`subject_name` = 'subject') and (`wiki_articles`.`subject_id` = `subjects`.`subid`)))) left join `resources` on(((`resources`.`activity_id` = `subjects`.`subid`) and (`resources`.`activity_type` = 128)))) 
  where 
    isnull(`wiki_articles`.`lesson_id`) 
  group by 
    `subjects`.`subid`;

CREATE OR REPLACE VIEW lessons AS
(
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
);

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

CREATE OR REPLACE VIEW subjects_users AS
SELECT MID as user_id, CID as subject_id, time_registered AS `begin`, NULL AS `end`, 1 AS status FROM Students
UNION SELECT MID as user_id, CID as subject_id, NULL AS `begin`, NULL AS `end`, 0 AS status FROM claimants WHERE `status` = 0
UNION SELECT MID as user_id, CID as subject_id, begin AS `begin`, `end` AS `end`, 2 AS status FROM graduated;

CREATE OR REPLACE VIEW kbase_items AS
SELECT Courses.Status AS status, 2 as `type`, Courses.Title AS title, Courses.CID AS id, Courses.createdate AS cdate FROM Courses WHERE Courses.Status = 1 AND Courses.chain IS NULL OR Courses.chain = 0 UNION
SELECT resources.Status AS status, 1 as `type`, resources.title AS title, resources.resource_id AS id, resources.created AS cdate FROM resources  WHERE resources.location=1 AND resources.Status = 1 AND resources.parent_id = 0;

CREATE OR REPLACE VIEW criteria AS
SELECT criterion_id AS criterion_id, 1 as `criterion_type`, name AS name FROM at_criteria UNION
SELECT criterion_id AS criterion_id, 2 as `criterion_type`, name AS name FROM at_criteria_test UNION
SELECT criterion_id AS criterion_id, 3 as `criterion_type`, name AS name FROM at_criteria_personal;
CREATE OR REPLACE VIEW `study_groups_auto_users` AS
SELECT `ga`.`group_id` AS `group_id`,`sou`.`mid` AS `user_id`
FROM ((`study_groups_auto` `ga` join `structure_of_organ` `sod` on((`sod`.`soid` = `ga`.`department_id`))) join `structure_of_organ` `sou` on(((`sou`.`lft` >= `sod`.`lft`) and (`sou`.`rgt` <= `sod`.`rgt`) and (`sou`.`code` = `ga`.`position_code`))));

CREATE OR REPLACE VIEW `study_groups_users` AS 
SELECT `study_groups_custom`.`group_id` AS `group_id`,`study_groups_custom`.`user_id` AS `user_id`,1 AS `type` 
FROM `study_groups_custom` 
UNION 
SELECT `study_groups_auto_users`.`group_id` AS `group_id`,`study_groups_auto_users`.`user_id` AS `user_id`,2 AS `type` 
FROM `study_groups_auto_users`;
