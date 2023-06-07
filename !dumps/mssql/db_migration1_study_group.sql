USE [studium]
GO

-- study_groups
IF OBJECT_ID('dbo.study_groups') IS NOT NULL
    DROP TABLE dbo.study_groups
GO
CREATE TABLE dbo.study_groups
	(
	group_id [int] IDENTITY(1,1) NOT NULL,
	name varchar(255) NULL,
	type int NOT NULL
	)  ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups ADD CONSTRAINT
	PK_group_id PRIMARY KEY CLUSTERED 
	(
	group_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX IX_stgr_type ON dbo.study_groups
	(
	type
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups SET (LOCK_ESCALATION = TABLE)
GO

-- study_groups_auto
IF OBJECT_ID('dbo.study_groups_auto') IS NOT NULL
    DROP TABLE dbo.study_groups_auto
GO
CREATE TABLE dbo.study_groups_auto
	(
	group_id int NOT NULL,
	position_code varchar(100) NOT NULL,
	department_id int NOT NULL
	)  ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_auto ADD CONSTRAINT
	PK_study_groups_auto PRIMARY KEY CLUSTERED 
	(
	group_id,
	position_code,
	department_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_auto SET (LOCK_ESCALATION = TABLE)
GO

-- study_groups_custom
IF OBJECT_ID('dbo.study_groups_custom') IS NOT NULL
    DROP TABLE dbo.study_groups_custom
GO
CREATE TABLE dbo.study_groups_custom
	(
	group_id int NOT NULL,
	user_id int NOT NULL
	)  ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_custom ADD CONSTRAINT
	PK_study_groups_custom PRIMARY KEY CLUSTERED 
	(
	group_id,
	user_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_custom SET (LOCK_ESCALATION = TABLE)
GO


-- Added lft rgt for structure_of_organ
IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'structure_of_organ' 
           AND  COLUMN_NAME = 'lft')
BEGIN	
	ALTER TABLE dbo.structure_of_organ ADD
        lft int NULL
    ALTER TABLE dbo.structure_of_organ SET (LOCK_ESCALATION = TABLE)
END
IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'structure_of_organ' 
           AND  COLUMN_NAME = 'rgt')
BEGIN	
	ALTER TABLE dbo.structure_of_organ ADD
        rgt int NULL
    ALTER TABLE dbo.structure_of_organ SET (LOCK_ESCALATION = TABLE)
END
IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'structure_of_organ' 
        AND  COLUMN_NAME = 'level')
BEGIN	
	ALTER TABLE dbo.structure_of_organ ADD
        level int NULL
    ALTER TABLE dbo.structure_of_organ SET (LOCK_ESCALATION = TABLE)
END
IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'structure_of_organ' 
        AND  COLUMN_NAME = 'org_id')
BEGIN	
	ALTER TABLE dbo.structure_of_organ ADD
        org_id int NULL
    ALTER TABLE dbo.structure_of_organ SET (LOCK_ESCALATION = TABLE)
END

-- structure_organ_list
IF OBJECT_ID('dbo.structure_organ_list') IS NOT NULL
    DROP TABLE dbo.structure_organ_list
GO
CREATE TABLE dbo.structure_organ_list
	(
	org_id [int] IDENTITY(1,1) NOT NULL,
	name varchar(255) NULL,
	)  ON [PRIMARY]
GO
ALTER TABLE dbo.structure_organ_list ADD CONSTRAINT
	PK_structure_organ_list PRIMARY KEY CLUSTERED 
	(
	org_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]

GO
CREATE NONCLUSTERED INDEX IX_storglist_name ON dbo.structure_organ_list
	(
	name
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE dbo.structure_organ_list SET (LOCK_ESCALATION = TABLE)
GO


-- study_groups_courses
IF OBJECT_ID('dbo.study_groups_courses') IS NOT NULL
    DROP TABLE dbo.study_groups_courses
GO
CREATE TABLE dbo.study_groups_courses
	(
	id [int] IDENTITY(1,1) NOT NULL,
	group_id int NOT NULL,
	course_id int NOT NULL,
	lesson_id int NOT NULL
	)  ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_courses ADD CONSTRAINT
	DF_study_groups_courses_group_id DEFAULT 0 FOR group_id
GO
ALTER TABLE dbo.study_groups_courses ADD CONSTRAINT
	DF_study_groups_courses_course_id DEFAULT 0 FOR course_id
GO
ALTER TABLE dbo.study_groups_courses ADD CONSTRAINT
	DF_study_groups_courses_lesson_id DEFAULT 0 FOR lesson_id
GO
ALTER TABLE dbo.study_groups_courses ADD CONSTRAINT
	PK_study_groups_courses PRIMARY KEY CLUSTERED 
	(
	id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]

GO
CREATE NONCLUSTERED INDEX IX_group_id ON dbo.study_groups_courses
	(
	group_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX IX_course_id ON dbo.study_groups_courses
	(
	course_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX IX_lesson_id ON dbo.study_groups_courses
	(
	lesson_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_courses SET (LOCK_ESCALATION = TABLE)
GO

-- study_groups_programms
IF OBJECT_ID('dbo.study_groups_programms') IS NOT NULL
    DROP TABLE dbo.study_groups_programms
GO
CREATE TABLE dbo.study_groups_programms
	(
	id [int] IDENTITY(1,1) NOT NULL,
	group_id int NOT NULL,
	programm_id int NOT NULL
	)  ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_programms ADD CONSTRAINT
	DF_study_groups_programms_group_id DEFAULT 0 FOR group_id
GO
ALTER TABLE dbo.study_groups_programms ADD CONSTRAINT
	DF_study_groups_programms_course_id DEFAULT 0 FOR programm_id
GO
ALTER TABLE dbo.study_groups_programms ADD CONSTRAINT
	PK_study_groups_programms PRIMARY KEY CLUSTERED 
	(
	id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX IX_group_id ON dbo.study_groups_programms
	(
	group_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX IX_programm_id ON dbo.study_groups_programms
	(
	programm_id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE dbo.study_groups_programms SET (LOCK_ESCALATION = TABLE)
GO

IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'programm_events' 
        AND  COLUMN_NAME = 'isElective')
BEGIN	
	ALTER TABLE dbo.programm_events ADD
		isElective smallint NOT NULL CONSTRAINT DF_programm_events_isElective DEFAULT 0
	ALTER TABLE dbo.programm_events SET (LOCK_ESCALATION = TABLE)
END


-- Dumping structure for view study_groups_auto_users
IF OBJECT_ID('dbo.study_groups_auto_users') IS NOT NULL
    DROP VIEW dbo.study_groups_auto_users
GO

CREATE VIEW study_groups_auto_users AS 
(SELECT ga.group_id AS group_id,sou.mid AS user_id 
FROM ((study_groups_auto ga 
join structure_of_organ sod on((sod.soid = ga.department_id)))
join structure_of_organ sou on(((sou.lft >= sod.lft) 
	and (sou.rgt <= sod.rgt) 
	and (sou.code = ga.position_code)
	)))
	);
GO    
    
-- Dumping structure for view study_groups_users
IF OBJECT_ID('dbo.study_groups_users') IS NOT NULL
    DROP VIEW dbo.study_groups_users
GO

CREATE VIEW study_groups_users AS 
SELECT study_groups_custom.group_id AS group_id,study_groups_custom.user_id AS user_id,1 AS type 
FROM study_groups_custom 
UNION 
SELECT study_groups_auto_users.group_id AS group_id,study_groups_auto_users.user_id AS user_id,2 AS type 
FROM study_groups_auto_users;	
GO

IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'htmlpage_groups' 
        AND  COLUMN_NAME = 'ordr') 
BEGIN	
	ALTER TABLE [dbo].[htmlpage_groups] ADD ordr int NOT NULL CONSTRAINT DF_htmlpage_groups_ordr DEFAULT 10
	ALTER TABLE [dbo].[htmlpage_groups] SET (LOCK_ESCALATION = TABLE)
END

IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'htmlpage' 
        AND  COLUMN_NAME = 'ordr') 
BEGIN	
	ALTER TABLE [dbo].[htmlpage] ADD ordr int NOT NULL CONSTRAINT DF_htmlpage_ordr DEFAULT 10
	ALTER TABLE [dbo].[htmlpage] SET (LOCK_ESCALATION = TABLE)
END

IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'programm' 
            AND  COLUMN_NAME = 'programm_type')
BEGIN	
	ALTER TABLE dbo.programm ADD
        programm_type int NULL
    ALTER TABLE dbo.programm SET (LOCK_ESCALATION = TABLE)
END

IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'programm' 
            AND  COLUMN_NAME = 'description')
BEGIN	
	ALTER TABLE dbo.programm ADD
        description  [text] NOT NULL DEFAULT ('')
    ALTER TABLE dbo.programm SET (LOCK_ESCALATION = TABLE)
END

IF NOT EXISTS( SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'People' 
            AND  COLUMN_NAME = 'email_confirmed')
BEGIN	
	ALTER TABLE dbo.People ADD
        email_confirmed [int] NOT NULL DEFAULT 0
    ALTER TABLE dbo.People SET (LOCK_ESCALATION = TABLE)
END


BEGIN
	ALTER TABLE dbo.loguser ADD
	    mark [decimal] (12, 3) NOT NULL DEFAULT 0
END

BEGIN
	ALTER TABLE dbo.schedule ADD
	    max_mark [int] NOT NULL DEFAULT 0
END

BEGIN
	ALTER TABLE dbo.sessions ADD course_id [int] NOT NULL DEFAULT 0
	ALTER TABLE dbo.sessions ADD lesson_id [int] NOT NULL DEFAULT 0
	ALTER TABLE dbo.sessions ADD lesson_type [int] NOT NULL DEFAULT 0
END