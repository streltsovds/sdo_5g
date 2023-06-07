CREATE TABLE idea (
	idea_id INT IDENTITY(1,1) NOT NULL,
	name VARCHAR(1024) NOT NULL,
	description VARCHAR(8000) NULL,
	status [int] NOT NULL,
	anonymous [int] NULL,
	date_created [datetime] NOT NULL,
	PRIMARY KEY (idea_id)
)
/****** Object:  Table [dbo].[OPTIONS]    Script Date: 04/20/2010 17:50:55 ******/
CREATE TABLE idea_url (
	idea_url_id INT IDENTITY(1,1) NOT NULL,
	idea_id [int] NOT NULL,
	url VARCHAR(4096) NOT NULL,
	PRIMARY KEY (idea_url_id)
)

CREATE TABLE idea_chat (
	idea_chat_id INT IDENTITY(1,1) NOT NULL,
	user_id [int] NOT NULL,
	message VARCHAR(8000) NULL,
	date_created [datetime] NOT NULL,
	parent_idea_chat_id [int] NULL,
	PRIMARY KEY (idea_chat_id)
)

CREATE TABLE idea_like (
	idea_like_id INT IDENTITY(1,1) NOT NULL,
	idea_id [int] NOT NULL,
	user_id [int] NOT NULL,
	value [int] NOT NULL,,
	date_created [datetime] NOT NULL,
	PRIMARY KEY (idea_like_id)
)

GO

CREATE TABLE [dbo].[absence](
	[absence_id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[user_external_id] [varchar](255) NULL,
	[type] [int] NOT NULL,
	[absence_begin] [datetime] NULL,
	[absence_end] [datetime] NULL,
 CONSTRAINT [PK_absence] PRIMARY KEY CLUSTERED
(
	[absence_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE at_ps_standard (
	standard_id INT IDENTITY(1,1) NOT NULL,
	number VARCHAR(50) NOT NULL,
	code VARCHAR(50) NOT NULL,
	name VARCHAR(1024) NOT NULL,
	area VARCHAR(1024) NOT NULL,
	vid VARCHAR(1024) NOT NULL,
	prikaz_number VARCHAR(50) NOT NULL,
	prikaz_date DATE NOT NULL,
	minjust_number VARCHAR(50) NOT NULL,
	minjust_date DATE NOT NULL,
	sovet VARCHAR(1024) NOT NULL,
	url VARCHAR(1024) NOT NULL,
	PRIMARY KEY (standard_id)
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE at_ps_function (
	function_id INT IDENTITY(1,1) NOT NULL,
	standard_id INT NOT NULL,
	name VARCHAR(1024) NOT NULL,
	PRIMARY KEY (function_id)
)
GO

CREATE INDEX [at_ps_function_standard_id] ON [at_ps_function] ([standard_id]);
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE at_ps_requirement (
	requirement_id INT IDENTITY(1,1) NOT NULL,
	function_id INT NULL DEFAULT NULL,
	type INT NULL DEFAULT NULL,
	name VARCHAR(1024) NULL DEFAULT NULL,
	PRIMARY KEY (requirement_id)
)
GO

CREATE INDEX [at_ps_requirement_function_id] ON [at_ps_requirement] ([function_id]);
GO

CREATE TABLE [dbo].[at_hh_regions](
	[id] [int] NOT NULL,
	[parent] [int] NOT NULL,
	[name] [varchar](255) NOT NULL,
 CONSTRAINT [PK_at_hh_regions] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
CREATE TABLE [dbo].[at_criteria] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [description] varchar(4096) NULL,
  [cluster_id] int NULL,
  [category_id] int NULL,
  [type] int NOT NULL default (0),
  [order] int NOT NULL DEFAULT (0),
  [status] int NOT NULL DEFAULT 1,
  [doubt] int NULL default (0),
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_criteria_cluster_id] ON [at_criteria] ([cluster_id]);
CREATE INDEX [at_criteria_category_id] ON [at_criteria] ([category_id]);
GO

CREATE TABLE [dbo].[at_criteria_kpi] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [description] varchar(4000) NULL,
  [order] int NOT NULL DEFAULT (0),
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_test] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [lft] int NOT NULL DEFAULT (0),
  [rgt] int NOT NULL DEFAULT (0),
  [level] int NOT NULL DEFAULT (0),
  [name] varchar(255) NULL,
  [quest_id] int NOT NULL DEFAULT (0),
  [subject_id] int NOT NULL DEFAULT (0),
  [description] varchar(1024) null,
  [required] int NULL,
  [validity] int NULL,
  [employee_type] int NULL,
  [status] int NULL,
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_criteria_test_quest_id] ON [at_criteria_test] ([quest_id]);
CREATE INDEX [at_criteria_test_subject_id] ON [at_criteria_test] ([subject_id]);
GO

CREATE TABLE [dbo].[at_criteria_personal] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255),
  [quest_id] int,
  [description] text,
  PRIMARY KEY CLUSTERED ([criterion_id])
)
GO

CREATE INDEX [at_criteria_personal_quest_id] ON [at_criteria_personal] ([quest_id]);
GO

CREATE TABLE [dbo].[at_kpi_clusters] (
  [kpi_cluster_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  PRIMARY KEY CLUSTERED ([kpi_cluster_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_attempt_clusters] (
  [quest_attempt_cluster_id] [int] IDENTITY(1,1) NOT NULL,
  [quest_attempt_id] int,
  [cluster_id] int,
  [score_percented] float,
  PRIMARY KEY CLUSTERED([quest_attempt_cluster_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_kpi_units] (
  [kpi_unit_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  PRIMARY KEY CLUSTERED ([kpi_unit_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_kpis] (
  [kpi_id] int IDENTITY(1, 1) NOT NULL,
  [kpi_cluster_id] int NOT NULL default (0),
  [kpi_unit_id] int NOT NULL default (0),
  [name] varchar(1024) NULL,
  [is_typical] int NOT NULL default (0),
  PRIMARY KEY CLUSTERED ([kpi_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_kpis_kpi_cluster_id] ON [at_kpis] ([kpi_cluster_id]);
CREATE INDEX [at_kpis_kpi_unit_id] ON [at_kpis] ([kpi_unit_id]);
GO

CREATE TABLE [dbo].[at_user_kpis] (
  [user_kpi_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NOT NULL default (0),
  [cycle_id] int NOT NULL default (0),
  [kpi_id] int NULL,
  [weight] float NULL,
  [value_plan] varchar(32) NULL,
  [value_fact] varchar(32) NULL, /*DEPRECATED!*/
  [comments] text NULL, /*DEPRECATED!*/
  [begin_date] date NULL,
  [end_date] date NULL,
  [value_type] int NULL,
  PRIMARY KEY CLUSTERED ([user_kpi_id])
)
ON [PRIMARY]

GO

CREATE INDEX [at_user_kpis_user_id] ON [at_user_kpis] ([user_id]);
CREATE INDEX [at_user_kpis_cycle_id] ON [at_user_kpis] ([cycle_id]);
CREATE INDEX [at_user_kpis_kpi_id] ON [at_user_kpis] ([kpi_id]);
GO

CREATE TABLE [dbo].[at_user_kpi_results] (
  [user_kpi_result_id] int IDENTITY(1, 1) NOT NULL,
  [user_kpi_id] int NOT NULL,
  [user_id] int NULL,
  [respondent_id] int NULL,
  [relation_type] int NULL,
  [value_fact] varchar(32) NULL,
  [comments] text NULL,
  [change_date] date NULL,
  PRIMARY KEY CLUSTERED ([user_kpi_result_id])
)
ON [PRIMARY]

GO

CREATE INDEX [at_user_kpi_results_user_kpi_id] ON [at_user_kpi_results] ([user_kpi_id]);
CREATE INDEX [at_user_kpi_results_user_id] ON [at_user_kpi_results] ([user_id]);
CREATE INDEX [at_user_kpi_results_respondent_id] ON [at_user_kpi_results] ([respondent_id]);
GO


CREATE TABLE [dbo].[estaff_spot](
	[spot_id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[start_date] [datetime] NULL,
	[state_date] [datetime] NULL,
	[state_id] [varchar](255) NOT NULL,
	[vacancy_name] [varchar](255) NOT NULL,
	[resume_text] [text] NULL,
 CONSTRAINT [PK_estaff_spot] PRIMARY KEY CLUSTERED
(
	[spot_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [estaff_spot_user_id] ON [estaff_spot] ([user_id]);
CREATE INDEX [estaff_spot_state_id] ON [estaff_spot] ([state_id]);
GO


/****** Object:  Table [dbo].[OPTIONS]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OPTIONS](
    [OptionID] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__OPTIONS__name__634EBE90]  DEFAULT (''),
    [value] [text] NULL,
 CONSTRAINT [PK_OPTIONS] PRIMARY KEY CLUSTERED
(
    [OptionID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

/****** Object:  Table [dbo].[password_history]    ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[password_history](
    [user_id] [int] NOT NULL,
    [password] [varchar](255) NOT NULL CONSTRAINT [DF__password__7DEDA635]  DEFAULT (('')),
    [change_date] [datetime]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[simple_auth]    ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[simple_auth](
	[auth_key] [char](32) NOT NULL,
	[user_id] [int] NOT NULL,
	[link] [varchar](255) NOT NULL,
	[valid_before] [smalldatetime] NOT NULL,
 CONSTRAINT [PK_simple_auth] PRIMARY KEY CLUSTERED
(
	[auth_key] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[People]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[People](
    [MID] [int] IDENTITY(1,1) NOT NULL,
    [mid_external] [varchar](255) NOT NULL CONSTRAINT [DF__People__mid_exte__6FB49575]  DEFAULT (''),
    [LastName] [varchar](255) NOT NULL CONSTRAINT [DF__People__LastName__70A8B9AE]  DEFAULT (''),
    [FirstName] [varchar](255) NOT NULL CONSTRAINT [DF__People__FirstNam__719CDDE7]  DEFAULT (''),
    [LastNameLat] [varchar](255) NOT NULL CONSTRAINT [DF__People__LastNameLat__70A8B9AE]  DEFAULT (''),
    [FirstNameLat] [varchar](255) NOT NULL CONSTRAINT [DF__People__FirstNamLat__719CDDE7]  DEFAULT (''),
    [Patronymic] [varchar](255) NOT NULL CONSTRAINT [DF__People__Patronym__72910220]  DEFAULT (''),
    [Registered] [datetime],
    [Course] [int] NOT NULL CONSTRAINT [DF__People__Course__74794A92]  DEFAULT ((0)),
    [EMail] [varchar](255) NOT NULL CONSTRAINT [DF__People__EMail__756D6ECB]  DEFAULT (''),
	[email_confirmed] [int] NOT NULL CONSTRAINT [DF__People__need_edit__0880333F]  DEFAULT ((0)),
    [Phone] [varchar](255) NOT NULL CONSTRAINT [DF__People__Phone__76619304]  DEFAULT (''),
    [Information] [text] NOT NULL CONSTRAINT [DF__People__Informat__060DEAE8]  DEFAULT (''),
    [Address] [text] NULL CONSTRAINT [DF__People__Address__07020F21]  DEFAULT (''),
    [Fax] [varchar](255) NOT NULL CONSTRAINT [DF__People__Fax__7755B73D]  DEFAULT (''),
    [Login] [varchar](255) NOT NULL CONSTRAINT [DF__People__Login__7849DB76]  DEFAULT (''),
    [Domain] [varchar](255) NOT NULL CONSTRAINT [DF__People__Domain__7849DB76]  DEFAULT (''),
    [Password] [varchar](255) NOT NULL CONSTRAINT [DF__People__Password__793DFFAF]  DEFAULT (''),
    [javapassword] [varchar](20) NOT NULL CONSTRAINT [DF__People__javapass__7A3223E8]  DEFAULT (''),
    [city] [varchar](255) NULL ,
    [BirthDate] [datetime] NOT NULL CONSTRAINT [DF__People__BirthDat__7B264821]  DEFAULT ((0)),
	[Age] int,
    [CellularNumber] [varchar](255) NOT NULL CONSTRAINT [DF__People__Cellular__7C1A6C5A]  DEFAULT (''),
    [ICQNumber] [int] NOT NULL CONSTRAINT [DF__People__ICQNumbe__7D0E9093]  DEFAULT ((0)),
    [Gender] [int] NOT NULL CONSTRAINT [DF__People__Age__7E02B4CC]  DEFAULT ((0)),
    [last] [bigint] NOT NULL CONSTRAINT [DF__People__last__7EF6D905]  DEFAULT ((0)),
    [countlogin] [int] NOT NULL CONSTRAINT [DF__People__countlog__7FEAFD3E]  DEFAULT ((0)),
    [rnid] [int] NOT NULL CONSTRAINT [DF__People__rnid__00DF2177]  DEFAULT ((0)),
    [Position] [varchar](128) NOT NULL CONSTRAINT [DF__People__Position__01D345B0]  DEFAULT (''),
    [PositionDate] [date],
    [PositionPrev] [varchar](128) NOT NULL CONSTRAINT [DF__People__Position__03BB8E22]  DEFAULT (''),
    [invalid_login] [int] NOT NULL CONSTRAINT [DF__People__invalid___04AFB25B]  DEFAULT ((0)),
    [isAD] [int] NULL CONSTRAINT [DF__People__isAD__05A3D694]  DEFAULT ((0)),
    [polls] [image] NULL,
    [Access_Level] [int] NOT NULL CONSTRAINT [DF__People__Access_L__0697FACD]  DEFAULT ('1'),
    [rang] [int] NOT NULL CONSTRAINT [DF__People__rang__078C1F06]  DEFAULT ((0)),
    [preferred_lang] [int] NOT NULL CONSTRAINT [DF__People__preferre__0880433F]  DEFAULT ((0)),
    [blocked] [int] NOT NULL CONSTRAINT [DF__People__blocked__0880433F]  DEFAULT ((0)),
    [block_message] [text] NULL,
    [head_mid] [int] NULL CONSTRAINT [DF__People__mid_head__05A3D694]  DEFAULT ((0)),
    [force_password] [int] NULL CONSTRAINT [DF__People__force_password__05A3D694]  DEFAULT ((0)),
    [lang] [varchar](3) NOT NULL CONSTRAINT [DF_People_lang_05A3D694] DEFAULT ('rus'),
    [need_edit] [int] NOT NULL CONSTRAINT [DF__People__need_edit__0880433F]  DEFAULT ((0)),
	[email_backup] varchar(255) CONSTRAINT [DF__People__email_ba__25E688F4] DEFAULT '' NULL,
    [data_agreement] [int] NOT NULL CONSTRAINT [DF__People__need_edit__0880433E]  DEFAULT ((0)),
	[dublicate] [int] NULL CONSTRAINT [DF__People__dublicate__06A2D791]  DEFAULT ((0)),
	[duplicate_of] [int] NULL DEFAULT ((0)),
    [push_token] [varchar](128) NULL DEFAULT (''),

 CONSTRAINT [PK_People] PRIMARY KEY CLUSTERED
(
    [MID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


/****** Object:  Table [dbo].[processes]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[processes](
    [process_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__processes__name__7908F585]  DEFAULT (''),
	[chain] [text] NOT NULL CONSTRAINT [DF__processes__chain__07020F21]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__processes__type__7720AD13]  DEFAULT ((0)),
	[programm_id] int,
 CONSTRAINT [PK_processes] PRIMARY KEY CLUSTERED
(
    [process_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[process_steps_data](
  [process_step_id] int IDENTITY(1,1) NOT NULL,
  [process_type] int default NULL,
  [item_id] int default NULL,
  [step] [varchar](255) default NULL,
  [date_begin] datetime default NULL,
  [date_end] datetime default '0000-00-00 00:00:00'
  CONSTRAINT [PK_process_steps_data] PRIMARY KEY CLUSTERED
(
    [process_step_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm](
    [programm_id] [int] IDENTITY(1,1) NOT NULL,
    [programm_type] [int] NOT NULL CONSTRAINT [DF__programm__type__7720AD13]  DEFAULT ((0)),
	[item_id] [int],
	[item_type] [int],
	[mode_strict] int DEFAULT 1 NOT NULL,
	[mode_finalize] int DEFAULT 0 NOT NULL,
    [name] [varchar](max) NOT NULL CONSTRAINT [DF__programm__name__7908F585]  DEFAULT (''),
    [description] [text] NULL,
 CONSTRAINT [PK_programm] PRIMARY KEY CLUSTERED
(
    [programm_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_categories] (
  [category_id] int IDENTITY(1,1) NOT NULL,
  [category_id_external] varchar(255),
  [name] varchar(max),
  [description] varchar(255),
  PRIMARY KEY CLUSTERED ([category_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_profile_education_requirement] (
    [education_id] int NOT NULL DEFAULT 0,
    [profile_id] int NOT NULL DEFAULT 0,
    [education_type] int NOT NULL DEFAULT 0,
    PRIMARY KEY CLUSTERED ([education_id],[profile_id],[education_type])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_hh_regions](
	[id] [int] NOT NULL,
	[parent] [int] NOT NULL,
	[name] [varchar](255) NOT NULL,
 CONSTRAINT [PK_at_hh_regions] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [dbo].[at_profile_function] (
    [profile_function_id] int IDENTITY(1,1) NOT NULL,
    [profile_id] int NOT NULL,
    [function_id] int NOT NULL,
  PRIMARY KEY CLUSTERED ([profile_function_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_profile_function_profile_id] ON [at_profile_function] ([profile_id]);
CREATE INDEX [at_profile_function_function_id] ON [at_profile_function] ([function_id]);
GO

GO
CREATE TABLE [dbo].[at_criteria] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [cluster_id] int NULL,
  [category_id] int NULL,
  [type] int NOT NULL default (0),
  [order] int NOT NULL DEFAULT (0),
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_kpi] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [order] int NOT NULL DEFAULT (0),
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_test] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [lft] int NOT NULL DEFAULT (0),
  [rgt] int NOT NULL DEFAULT (0),
  [level] int NOT NULL DEFAULT (0),
  [name] varchar(255) NULL,
  [quest_id] int NOT NULL DEFAULT (0),
  [subject_id] int NOT NULL DEFAULT (0),
  [description] varchar(1024) null,
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_clusters] (
  [cluster_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [order] int CONSTRAINT [DF__at_criter__order__00EA0E6F] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([cluster_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_scale_values] (
  [criterion_value_id] int IDENTITY(1, 1) NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_criter__crite__7C255952] DEFAULT 0 NOT NULL,
  [value_id] int NULL,
  [description] varchar(4000) NULL
  PRIMARY KEY CLUSTERED ([criterion_value_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_criteria_scale_values_criterion_id] ON [at_criteria_scale_values] ([criterion_id]);
CREATE INDEX [at_criteria_scale_values_value_id] ON [at_criteria_scale_values] ([value_id]);
GO


CREATE TABLE [dbo].[at_criteria_indicator_scale_values] (
  [criterion_indicator_value_id] int IDENTITY(1, 1) NOT NULL,
  [indicator_id] int DEFAULT 0 NOT NULL,
  [value_id] int NULL,
  [description] varchar(4000) NULL,
  [description_questionnaire] varchar(4000) NULL
  PRIMARY KEY CLUSTERED ([criterion_indicator_value_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_criteria_indicator_scale_values_indicator_id] ON [at_criteria_indicator_scale_values] ([indicator_id]);
CREATE INDEX [at_criteria_indicator_scale_values_value_id] ON [at_criteria_indicator_scale_values] ([value_id]);
GO

CREATE TABLE [dbo].[at_evaluation_criteria] (
  [evaluation_type_id] int CONSTRAINT [DF__at_evalua__evalu__2F6FF32E] DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_evalua__crite__30641767] DEFAULT 0 NOT NULL,
  [quest_id] int CONSTRAINT [DF__at_evalua__que__30641767] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([evaluation_type_id],[criterion_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_evaluation_criteria_quest_id] ON [at_evaluation_criteria] ([quest_id]);
GO

CREATE TABLE [dbo].[at_evaluation_memo_results] (
  [evaluation_memo_result_id] int IDENTITY(1, 1) NOT NULL,
  [evaluation_memo_id] int CONSTRAINT [DF__at_evalua__evalu__6CE315C2] DEFAULT 0 NOT NULL,
  [value] text NULL,
  [session_event_id] int CONSTRAINT [DF__at_evalua__sessi__6DD739FB] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([evaluation_memo_result_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [at_evaluation_memo_results_evaluation_memo_id] ON [at_evaluation_memo_results] ([evaluation_memo_id]);
CREATE INDEX [at_evaluation_memo_results_session_event_id] ON [at_evaluation_memo_results] ([session_event_id]);
GO

CREATE TABLE [dbo].[at_evaluation_memos] (
  [evaluation_memo_id] int IDENTITY(1, 1) NOT NULL,
  [evaluation_type_id] int CONSTRAINT [DF__at_evalua__evalu__729BEF18] DEFAULT 0 NOT NULL,
  [name] varchar(255) NULL
  PRIMARY KEY CLUSTERED ([evaluation_memo_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_evaluation_memos_evaluation_type_id] ON [at_evaluation_memos] ([evaluation_type_id]);
GO

CREATE TABLE [dbo].[at_evaluation_results] (
  [result_id] int IDENTITY(1, 1) NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_evalua__crite__01A9287E] DEFAULT 0 NOT NULL,
  [session_event_id] int CONSTRAINT [DF__at_evalua__sessi__029D4CB7] DEFAULT 0 NOT NULL,
  [session_user_id] int CONSTRAINT [DF__at_evalua__sessi__039170F0] DEFAULT 0 NOT NULL,
  [relation_type] int NULL,
  [position_id] int CONSTRAINT [DF__at_evalua__posit__04859529] DEFAULT 0 NOT NULL,
  [value_id] int CONSTRAINT [DF__at_evalua__value__0579B962] DEFAULT 0 NOT NULL,
  [value_weight] float NULL,
  [indicators_status] int NULL,
  [custom_criterion_name] varchar(255) NULL,
  [custom_criterion_parent_id] int NULL
  PRIMARY KEY CLUSTERED ([result_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_evaluation_results_criterion_id] ON [at_evaluation_results] ([criterion_id]);
CREATE INDEX [at_evaluation_results_session_event_id] ON [at_evaluation_results] ([session_event_id]);
CREATE INDEX [at_evaluation_results_session_user_id] ON [at_evaluation_results] ([session_user_id]);
CREATE INDEX [at_evaluation_results_position_id] ON [at_evaluation_results] ([position_id]);
CREATE INDEX [at_evaluation_results_value_id] ON [at_evaluation_results] ([value_id]);
CREATE INDEX [at_evaluation_results_custom_criterion_parent_id] ON [at_evaluation_results] ([custom_criterion_parent_id]);
GO

CREATE TABLE [dbo].[at_evaluation_results_indicators] (
  [indicator_result_id] int IDENTITY(1, 1) NOT NULL,
  [indicator_id] int CONSTRAINT [DF__at_evalua__indic__14F1071C] DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_evalua__crite__15E52B55] DEFAULT 0 NOT NULL,
  [session_event_id] int CONSTRAINT [DF__at_evalua__sessi__16D94F8E] DEFAULT 0 NOT NULL,
  [session_user_id] int CONSTRAINT [DF__at_evalua__sessi__17CD73C7] DEFAULT 0 NOT NULL,
  [relation_type] int NULL,
  [position_id] int CONSTRAINT [DF__at_evalua__posit__18C19800] DEFAULT 0 NOT NULL,
  [value_id] int CONSTRAINT [DF__at_evalua__value__19B5BC39] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([indicator_result_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_evaluation_results_indicators_indicator_id] ON [at_evaluation_results_indicators] ([indicator_id]);
CREATE INDEX [at_evaluation_results_indicators_criterion_id] ON [at_evaluation_results_indicators] ([criterion_id]);
CREATE INDEX [at_evaluation_results_indicators_session_event_id] ON [at_evaluation_results_indicators] ([session_event_id]);
CREATE INDEX [at_evaluation_results_indicators_session_user_id] ON [at_evaluation_results_indicators] ([session_user_id]);
CREATE INDEX [at_evaluation_results_indicators_position_id] ON [at_evaluation_results_indicators] ([position_id]);
CREATE INDEX [at_evaluation_results_indicators_value_id] ON [at_evaluation_results_indicators] ([value_id]);
GO

CREATE TABLE [dbo].[at_evaluation_type] (
  [evaluation_type_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [comment] text NULL,
  [scale_id] int NULL, /*deprecated!!!*/
  [category_id] int NULL,
  [profile_id] int NULL,
  [vacancy_id] int NULL,
  [newcomer_id] int NULL,
  [reserve_id] int NULL,
  [method] varchar(255) NULL,
  [submethod] varchar(255) NULL,
  [methodData] text NULL, /*deprecated, move data to submethod!*/
  [relation_type] int NULL,
  [programm_type] int DEFAULT 0 NOT NULL,
  PRIMARY KEY CLUSTERED ([evaluation_type_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [at_evaluation_type_scale_id] ON [at_evaluation_type] ([scale_id]);
CREATE INDEX [at_evaluation_type_category_id] ON [at_evaluation_type] ([category_id]);
CREATE INDEX [at_evaluation_type_profile_id] ON [at_evaluation_type] ([profile_id]);
CREATE INDEX [at_evaluation_type_vacancy_id] ON [at_evaluation_type] ([vacancy_id]);
CREATE INDEX [at_evaluation_type_newcomer_id] ON [at_evaluation_type] ([newcomer_id]);
GO

CREATE TABLE [dbo].[at_criteria_indicators] (
  [indicator_id] int IDENTITY(1, 1) NOT NULL,
  [criterion_id] int NULL,
  [name] varchar(255) NULL,
  [name_questionnaire] varchar(255) NULL,
  [description_positive] varchar(4000) NULL,
  [description_negative] varchar(4000) NULL,
  [reverse] int CONSTRAINT [DF__at_criter__rever__05AEC38C] DEFAULT 0 NOT NULL,
  [order] int CONSTRAINT [DF__at_criter__order__06A2E7C5] DEFAULT 0 NOT NULL,
  [doubt] int NULL default (0)
  PRIMARY KEY CLUSTERED ([indicator_id])
)
ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_at_crit_ind_critid] ON [dbo].[at_criteria_indicators]
(
    [criterion_id] ASC
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[timesheets] (
  [timesheet_id] int IDENTITY(1, 1) NOT NULL,
  [user_id]     INT NOT NULL,
  [action_type] INT NOT NULL,
  [description] VARCHAR(MAX) NULL,
  [action_date] DATE NULL,
  [begin_time]  TIME NULL,
  [end_time] TIME NULL,
  PRIMARY KEY CLUSTERED ([timesheet_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[hold_mail] (
  [hold_mail_id] int IDENTITY(1, 1) NOT NULL,
  [receiver_MID] INT NOT NULL,
  [serialized_message] TEXT NOT NULL DEFAULT(''),
  PRIMARY KEY CLUSTERED ([hold_mail_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[recruiters] (
  [recruiter_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NULL,
  [hh_auth_data] VARCHAR(MAX) NULL
  PRIMARY KEY CLUSTERED ([recruiter_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[report_templates] (
  [rtid] int IDENTITY(1, 1) NOT NULL,
  [template_name] varchar(255) NULL,
  [report_name] varchar(255) NULL,
  [created] int CONSTRAINT [DF__report_te__creat__1798699D] DEFAULT 0 NOT NULL,
  [creator] int NULL,
  [edited] int NULL,
  [editor] int NULL,
  [template] text NULL
  PRIMARY KEY CLUSTERED ([rtid])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[scale_values] (
  [value_id] int IDENTITY(1, 1) NOT NULL,
  [scale_id] int CONSTRAINT [DF__scale_val__scale__6418C597] DEFAULT 1 NOT NULL,
  [value] int CONSTRAINT [DF__scale_val__value__650CE9D0] DEFAULT 0 NOT NULL,
  [text] varchar(255) NULL,
  [description] text NULL
  PRIMARY KEY CLUSTERED ([value_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO


CREATE TABLE [dbo].[scales] (
  [scale_id] [int] IDENTITY(1, 1) NOT NULL,
  [name] [varchar](255) CONSTRAINT [DF__scales__name__5E5FEC41] DEFAULT '' NOT NULL,
  [description] [varchar](4000) NULL,
  [type] [int] CONSTRAINT [DF__scales__type__5F54107A] DEFAULT 0 NOT NULL,
  [mode] [int] CONSTRAINT [DF__scales__mode__5F54107B] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([scale_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[specializations] (
  [spid] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) CONSTRAINT [DF__specializa__name__230A1C49] DEFAULT '' NOT NULL,
  [discription] text NULL
  PRIMARY KEY CLUSTERED ([spid])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[states] (
  [scope] varchar(20) NULL,
  [scope_id] int NULL,
  [state] int NULL,
  [title] varchar(64) NULL
)
ON [PRIMARY]
GO
/****** Object:  Table [dbo].[programm_events]    Script Date: 04/20/2010 17:50:56 ******/
CREATE NONCLUSTERED INDEX [IX_states_scope] ON [dbo].[states]
(
    [scope] ASC
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_states_scopeid] ON [dbo].[states]
(
    [scope_id] ASC
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_states_state] ON [dbo].[states]
(
    [state] ASC
) ON [PRIMARY]
GO




/****** Object:  Table [dbo].[programm_events]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_events](
    [programm_event_id] [int] IDENTITY(1,1) NOT NULL,

	[programm_id] [int] NOT NULL CONSTRAINT [DF__programm_events_programm_id__7814D14C]  DEFAULT ('0'),
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__programm_events__name__7908F585]  DEFAULT (''),
	[type] [int] NOT NULL CONSTRAINT [DF__programm_events_type__7814D14C]  DEFAULT ('0'),
	[item_id] [int] NOT NULL CONSTRAINT [DF__programm_events_item_id__7814D14C]  DEFAULT ('0'),
	[day_begin] int DEFAULT ('1'),
	[day_end] int DEFAULT ('1'),
	[ordr] int,
  [hidden] tinyint DEFAULT 0,
  [isElective] [int] NOT NULL DEFAULT ('0'),
 CONSTRAINT [PK_programm_events] PRIMARY KEY CLUSTERED
(
    [programm_event_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO



/****** Object:  Table [dbo].[programm_events_users]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_events_users](
    [programm_event_user_id] [int] IDENTITY(1,1) NOT NULL,
    [programm_event_id] [int] NOT NULL CONSTRAINT [DF__programm_events_users_event_id__7814D14C]  DEFAULT ('0'),
	[programm_id] [int],
	[user_id] [int] NOT NULL CONSTRAINT [DF__programm_events_user_id__7814D14C]  DEFAULT ('0'),
	[begin_date] [datetime] NULL,
	[end_date] [datetime] NULL,
	[status] [int] NOT NULL CONSTRAINT [DF__programm_events_status__7814D14C]  DEFAULT ('0'),
 CONSTRAINT [PK_programm_events_users] PRIMARY KEY CLUSTERED
(
    [programm_event_user_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[programm_users]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_users](
    [programm_user_id] [int] IDENTITY(1,1) NOT NULL,
    [programm_id] [int] NOT NULL CONSTRAINT [DF__programm_users_programm_id__7814D14C]  DEFAULT ('0'),
	[user_id] [int] NOT NULL CONSTRAINT [DF__programm_users_user_id_7814D14C]  DEFAULT ('0'),
    [assign_date] [datetime] NULL,
 CONSTRAINT [PK_programm_users] PRIMARY KEY CLUSTERED
(
    [programm_user_id] ASC
) ON [PRIMARY]


) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[Participants]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Participants](
  [participant_id] [int] IDENTITY(1,1) NOT NULL,
  [MID] [int] NOT NULL CONSTRAINT [DF__Participants__MID__753864A1]  DEFAULT ((0)),
  [CID] [int] NOT NULL CONSTRAINT [DF__Participants__CID__762C88DA]  DEFAULT ((0)),
  [cgid] [int] NOT NULL CONSTRAINT [DF__Participants__cgid__7720AD13]  DEFAULT ((0)),
  [Registered] [int] NOT NULL CONSTRAINT [DF__Participants__Regist__7814D14C]  DEFAULT ('1'),
  [begin_personal] [datetime] NULL,
  [end_personal] [datetime] NULL,
  [time_registered] [datetime] NULL,
  [time_ended] [datetime] NULL,
  [project_role] [int] NOT NULL DEFAULT ((0)),
  [offline_course_path] [varchar](255) NOT NULL CONSTRAINT [DF__Participants__offlin__7908F585]  DEFAULT (''),

  -- DEPRECATED!!!
  [time_ended_planned] [datetime] NULL,
 CONSTRAINT [PK_Participants] PRIMARY KEY CLUSTERED
(
    [participant_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_Participants] ON [dbo].[Participants]
(
    [MID] ASC,
    [CID] ASC
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[hrs] (
  [hr_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NOT NULL,
  PRIMARY KEY CLUSTERED ([hr_id],[user_id])
)
ON [PRIMARY]
GO


/****** Object:  Table [dbo].[Students]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Students](
    [SID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__Students__MID__753864A1]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__Students__CID__762C88DA]  DEFAULT ((0)),
    [cgid] [int] NOT NULL CONSTRAINT [DF__Students__cgid__7720AD13]  DEFAULT ((0)),
    [Registered] [int] NOT NULL CONSTRAINT [DF__Students__Regist__7814D14C]  DEFAULT ('1'),
	[begin_personal] [datetime] NULL,
	[end_personal] [datetime] NULL,
    [time_registered] [datetime] NULL,
    [time_ended] [datetime] NULL,
    [newcomer_id] [int] NOT NULL DEFAULT ((0)),
    [reserve_id] [int] NOT NULL DEFAULT ((0)),
    [programm_event_user_id] [int] NOT NULL DEFAULT ((0)),
    [application_id] [int] NULL,
    [notified] [int] NULL DEFAULT ((0)),
    [comment] varchar(255) NOT NULL DEFAULT (''),
    [offline_course_path] [varchar](255) NOT NULL CONSTRAINT [DF__Students__offlin__7908F585]  DEFAULT (''),

	-- DEPRECATED!!!
	[time_ended_planned] [datetime] NULL,

 CONSTRAINT [PK_Students] PRIMARY KEY CLUSTERED
(
    [SID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_Students] ON [dbo].[Students]
(
    [MID] ASC,
    [CID] ASC
) ON [PRIMARY]
GO





CREATE TABLE [dbo].[study_groups] (
  [group_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NOT NULL,
  [type] tinyint NOT NULL,
  PRIMARY KEY CLUSTERED ([group_id])
)
ON [PRIMARY]
GO


CREATE TABLE [dbo].[study_groups_auto] (
  [group_id] int NOT NULL,
  [position_code] varchar(100) NOT NULL,
  [department_id] int  NOT NULL,
  PRIMARY KEY CLUSTERED ([group_id],[position_code],[department_id])
)
ON [PRIMARY]
GO



CREATE TABLE [dbo].[study_groups_courses] (
  [id] int IDENTITY(1, 1) NOT NULL,
  [group_id] int NOT NULL DEFAULT '0',
  [course_id] int NOT NULL DEFAULT '0',
  [lesson_id] int NOT NULL DEFAULT '0',
  PRIMARY KEY CLUSTERED ([id])
)
ON [PRIMARY]
GO



CREATE TABLE [dbo].[study_groups_custom] (
  [group_id] int NOT NULL,
  [user_id] int NOT NULL,
  PRIMARY KEY CLUSTERED ([group_id],[user_id])
)
ON [PRIMARY]
GO



CREATE TABLE [dbo].[study_groups_programms] (
  [id] int NOT NULL IDENTITY(1, 1),
  [group_id] int NOT NULL DEFAULT '0',
  [programm_id] int NOT NULL DEFAULT '0',
  PRIMARY KEY CLUSTERED ([id])
)
ON [PRIMARY]
GO



/****** Object:  Table [dbo].[state_of_process]    Script Date: 04/20/2010 17:50:56 ******/


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[state_of_process](
    [state_of_process_id] [int] IDENTITY(1,1) NOT NULL,
    [item_id] [int] NOT NULL CONSTRAINT [DF__state_of_process__item_id__753864A1]  DEFAULT ((0)),
    [process_id] [int] NOT NULL CONSTRAINT [DF__state_of_process__process_id__762C88DA]  DEFAULT ((0)),
    [process_type] [int] NOT NULL CONSTRAINT [DF__state_of_process__process_type__7720AD13]  DEFAULT ((0)),
    [current_state] [varchar](255) NOT NULL CONSTRAINT [DF__state_of_process__current_state__7908F585]  DEFAULT (''),
    [last_passed_state] [varchar](255) NOT NULL CONSTRAINT [DF__state_of_process__last_passed_state__7908F585]  DEFAULT (''),
    [passed_states] [varchar](max) NULL,
	[status] [int] NOT NULL CONSTRAINT [DF__state_of_process__status__7720AD13]  DEFAULT ((0)),
	[params] [text] NOT NULL CONSTRAINT [DF__state_of_process__params__07020F21]  DEFAULT (''),
 CONSTRAINT [PK_state_of_process] PRIMARY KEY CLUSTERED
(
    [state_of_process_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


CREATE TABLE [dbo].[state_of_process_data](
  [state_of_process_data_id] [int] IDENTITY(1,1) NOT NULL,
  [state_of_process_id] [int] NOT NULL,
  [programm_event_user_id] [int] NULL,
  [state] [varchar](255) NOT NULL,
  [begin_date_planned] [datetime] NOT NULL,
  [begin_date] [datetime] NOT NULL,
  [begin_by_user_id] [int] NULL,
  [begin_auto] [tinyint] NOT NULL DEFAULT ((0)),
  [end_date_planned] [datetime] NULL,
  [end_date] [datetime] NULL,
  [end_by_user_id] [int] NULL,
  [end_auto] [tinyint] NULL,
  [status] [int] NULL,
  [comment] [varchar](4000) NULL,
  [comment_date] [datetime] NULL,
  [comment_user_id] [int] NULL,
  CONSTRAINT [PK_state_of_process_data] PRIMARY KEY CLUSTERED
(
  [state_of_process_data_id] ASC
)
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[webinar_chat]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[webinar_chat](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [pointId] [int] NOT NULL CONSTRAINT [DF__webinar_c__point__7DEDA633]  DEFAULT ((0)),
    [message] [varchar](255) NULL CONSTRAINT [DF__webinar_c__messa__7EE1CA6C]  DEFAULT (''),
    [datetime] [datetime] NULL,
    [userId] [int] NOT NULL CONSTRAINT [contraint_userid]  DEFAULT ((0)),
PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[webinars]    ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[webinars](
    [webinar_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__webinars__name__7DEDA635]  DEFAULT (('')),
    [create_date] [datetime] NULL,
    [subject_id] [int] NOT NULL CONSTRAINT [DF__webinars__subject_id]  DEFAULT ((0)),
    [subject] varchar(50) NOT NULL DEFAULT ('subject'),

PRIMARY KEY CLUSTERED
(
    [webinar_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[Teachers]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Teachers](
    [PID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__Teachers__MID__7AF13DF7]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__Teachers__CID__7BE56230]  DEFAULT ((0)),
 CONSTRAINT [PK_Teachers] PRIMARY KEY CLUSTERED
(
    [PID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[admins]    Script Date: 04/20/2010 17:50:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[admins](
    [AID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__admins__MID__7E6CC920]  DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_admins] ON [dbo].[admins]
(
    [AID] ASC
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_admins_1] ON [dbo].[admins]
(
    [MID] ASC
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[admins]    Script Date: 04/20/2010 17:50:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[simple_admins](
  [AID] [int] IDENTITY(1,1) NOT NULL,
  [MID] [int] NOT NULL CONSTRAINT [DF__simple_admins__MID__7E6CC920]  DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_simple_admins] ON [dbo].[simple_admins]
(
  [AID] ASC
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_simple_admins_1] ON [dbo].[simple_admins]
(
  [MID] ASC
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[blog]
(
  [id]         INT IDENTITY(1,1) NOT NULL,
  [title]      [VARCHAR](255),
  [body]       [TEXT] NOT NULL,
  [created]    [DATETIME] NOT NULL,
  [created_by] [INT] NOT NULL,
  [subject_name] [VARCHAR](255),
  [subject_id] [INT] NOT NULL,
  CONSTRAINT blog_pk PRIMARY KEY (id)
)
GO

/****** Object:  Table [dbo].[at_managers]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[at_managers](
    [atmanager_id] INT IDENTITY(1,1) NOT NULL,
    [user_id] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_at_managers] PRIMARY KEY CLUSTERED
(
    [atmanager_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE INDEX [at_managers_user_id] ON [at_managers] ([user_id]);
GO

CREATE TABLE [dbo].[at_profile_criterion_values] (
  [profile_criterion_value_id] int IDENTITY(1, 1) NOT NULL,
  [profile_id] int CONSTRAINT [DF__at_profil__profi__4F1DA8B1] DEFAULT 0 NOT NULL,
  [criterion_type] int DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_profil__crite__5011CCEA] DEFAULT 0 NOT NULL,
  [value_id] int NULL,
  [value] int NULL,
  [method] varchar(255),
  [importance] int NULL,
  [value_backup] int NULL,
  PRIMARY KEY CLUSTERED ([profile_criterion_value_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_profile_criterion_values_profile_id] ON [at_profile_criterion_values] ([profile_id]);
CREATE INDEX [at_profile_criterion_values_criterion_id] ON [at_profile_criterion_values] ([criterion_id]);
CREATE INDEX [at_profile_criterion_values_value_id] ON [at_profile_criterion_values] ([value_id]);
GO

CREATE TABLE [dbo].[at_profile_skills] (
  [profile_skill_id] int IDENTITY(1, 1) NOT NULL,
  [profile_id] int,
  [type] int,
  [skill] varchar(255),
  PRIMARY KEY CLUSTERED([profile_skill_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_profile_skills_profile_id] ON [at_profile_skills] ([profile_id]);
GO

CREATE TABLE [dbo].[at_profiles] (
  [profile_id] int IDENTITY(1, 1) NOT NULL,
  [profile_id_external] varchar(MAX) NULL,
  [position_id_external] varchar(MAX) NULL,
  [department_id] int NULL,
  [department_path] varchar(MAX) NULL,
  [category_id] int NULL,
  [programm_id] int NULL,
  [user_id] int NULL,
  [name] varchar(MAX) NULL,
  [shortname] varchar(MAX) NULL,
  [description] varchar(MAX) NULL,
  [requirements] text NULL,
  [age_min] int CONSTRAINT [DF__at_profiles_age_min_224B023A] DEFAULT 0 NOT NULL,
  [age_max] int CONSTRAINT [DF__at_profiles_age_max_224B023A] DEFAULT 0 NOT NULL,
  [gender] int CONSTRAINT [DF__at_profiles_gender_224B023A] DEFAULT 0 NOT NULL,
  [education] int DEFAULT 0 NOT NULL,
  [additional_education] text NULL,
  [academic_degree] int CONSTRAINT [DF__at_profiles_academic_degree_224B023A] DEFAULT 0 NOT NULL,
  [trips] int DEFAULT 0 NOT NULL,
  [trips_duration] varchar(255) NULL,
  [mobility] int DEFAULT 0 NOT NULL,
  [experience] text NULL,
  [comments] text NULL,
  [progress] int NULL,
  [double_time] TINYINT NOT NULL DEFAULT ((0)),
  [blocked] TINYINT NOT NULL DEFAULT ((0)),
  [psk] varchar(16) NULL,
  [base_id] int NULL,
  [is_manager] int DEFAULT 0 NOT NULL,
  PRIMARY KEY CLUSTERED ([profile_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [at_profiles_category_id] ON [at_profiles] ([category_id]);
CREATE INDEX [at_profiles_programm_id] ON [at_profiles] ([programm_id]);
CREATE INDEX [at_profiles_user_id] ON [at_profiles] ([user_id]);
CREATE INDEX [at_profiles_base_id] ON [at_profiles] ([base_id]);
GO

CREATE TABLE [dbo].[at_session_event_attempts] (
  [attempt_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NULL,
  [session_event_id] int NULL,
  [method] varchar(255) CONSTRAINT [DF__at_sessio__metho__2FA4FD58] DEFAULT '' NULL,
  [date_begin] datetime NULL,
  [date_end] datetime NULL
  PRIMARY KEY CLUSTERED ([attempt_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_session_event_attempts_user_id] ON [at_session_event_attempts] ([user_id]);
CREATE INDEX [at_session_event_attempts_session_event_id] ON [at_session_event_attempts] ([session_event_id]);
GO

CREATE TABLE [dbo].[at_session_events] (
  [session_event_id] int IDENTITY(1, 1) NOT NULL,
  [session_id] int CONSTRAINT [DF__at_sessio__sessi__224B023A] DEFAULT 0 NOT NULL,
  [evaluation_id] int CONSTRAINT [DF__at_sessio__evalu__233F2673] DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_sessio__crit__233F2673] DEFAULT 0 NOT NULL,
  [criterion_type] int CONSTRAINT [DF__at_sessio__crittyp__233F2673] DEFAULT 0 NOT NULL,
  [position_id] int CONSTRAINT [DF__at_sessio__posit__24334AAC] DEFAULT 0 NOT NULL,
  [session_user_id] int CONSTRAINT [DF__at_sessio__sessi__25276EE5] DEFAULT 0 NOT NULL,
  [session_respondent_id] int CONSTRAINT [DF__at_sessio__sessi__261B931E] DEFAULT 0 NOT NULL,
  [programm_event_user_id] int DEFAULT 0 NOT NULL,
  [quest_id] int DEFAULT 0 NOT NULL,
  [user_id] int CONSTRAINT [DF__at_sessio__user___270FB757] DEFAULT 0 NOT NULL,
  [respondent_id] int CONSTRAINT [DF__at_sessio__respo__2803DB90] DEFAULT 0 NOT NULL,
  [method] varchar(255) CONSTRAINT [DF__at_sessio__metho__28F7FFC9] DEFAULT '' NULL,
  [name] varchar(255) CONSTRAINT [DF__at_session__name__29EC2402] DEFAULT '' NULL,
  [description] text NULL,
  [status] int CONSTRAINT [DF__at_sessio__statu__2AE0483B] DEFAULT 0 NOT NULL,
  [date_begin] datetime NULL,
  [date_end] datetime NULL,
  [date_filled] datetime NULL,
  [is_empty_quest] INT NULL
  PRIMARY KEY CLUSTERED ([session_event_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [at_session_events_session_id] ON [at_session_events] ([session_id]);
CREATE INDEX [at_session_events_evaluation_id] ON [at_session_events] ([evaluation_id]);
CREATE INDEX [at_session_events_criterion_id] ON [at_session_events] ([criterion_id]);
CREATE INDEX [at_session_events_position_id] ON [at_session_events] ([position_id]);
CREATE INDEX [at_session_events_session_user_id] ON [at_session_events] ([session_user_id]);
CREATE INDEX [at_session_events_session_respondent_id] ON [at_session_events] ([session_respondent_id]);
CREATE INDEX [at_session_events_user_id] ON [at_session_events] ([user_id]);
CREATE INDEX [at_session_events_respondent_id] ON [at_session_events] ([respondent_id]);
CREATE INDEX [at_session_events_programm_event_user_id] ON [at_session_events] ([programm_event_user_id]);
CREATE INDEX [at_session_events_quest_id] ON [at_session_events] ([quest_id]);
GO

CREATE TABLE [dbo].[at_session_pairs] (
  [session_pair_id] int IDENTITY(1, 1) NOT NULL,
  [session_event_id] int CONSTRAINT [DF__at_sessiopa__sessi__224B023A] DEFAULT 0 NOT NULL,
  [first_user_id] int CONSTRAINT [DF__at_sessiopa__evalu__233F2673] DEFAULT 0 NOT NULL,
  [second_user_id] int CONSTRAINT [DF__at_sessiopa__posit__24334AAC] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([session_pair_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_session_pairs_session_event_id] ON [at_session_pairs] ([session_event_id]);
CREATE INDEX [at_session_pairs_first_user_id] ON [at_session_pairs] ([first_user_id]);
CREATE INDEX [at_session_pairs_second_user_id] ON [at_session_pairs] ([second_user_id]);
GO

CREATE TABLE [dbo].[at_session_pair_results] (
  [session_pair_id] int CONSTRAINT [DF__at_sessio__pair__224B023A] DEFAULT 0 NOT NULL,
  [session_event_id] int CONSTRAINT [DF__at_sessio__event__224B023A] DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_sessio__criterion__233F2673] DEFAULT 0 NOT NULL,
  [user_id] int CONSTRAINT [DF__at_sessiopar__user__24334AAC] DEFAULT 0 NOT NULL,
  [parent_soid] int CONSTRAINT [DF__at_sessio__user__24334AAC] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([session_pair_id], [criterion_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_session_pair_results_session_event_id] ON [at_session_pair_results] ([session_event_id]);
CREATE INDEX [at_session_pair_results_user_id] ON [at_session_pair_results] ([user_id]);
GO

CREATE TABLE [dbo].[at_session_pair_ratings] (
  [session_id] int CONSTRAINT [DF__at_sessiopara__sessi__224B023A] DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_sessio__criterio__224B023A] DEFAULT 0 NOT NULL,
  [session_user_id] int CONSTRAINT [DF__at_sessiopara__user__233F2673] DEFAULT 0 NOT NULL,
  [user_id] int CONSTRAINT [DF__at_sessiopara__evalu__233F2673] DEFAULT 0 NOT NULL,
  [rating] int DEFAULT 0 NOT NULL,
  [ratio] int DEFAULT 0 NOT NULL,
  [parent_soid] int DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([session_id], [criterion_id], [user_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_session_pair_ratings_session_user_id] ON [at_session_pair_ratings] ([session_user_id]);
GO

CREATE TABLE [dbo].[at_session_event_lessons] (
  [session_event_id] int CONSTRAINT [DF__session_event_id__2F6FF32E] DEFAULT 0 NOT NULL,
  [lesson_id] int CONSTRAINT [DF__lesson_id__30641767] DEFAULT 0 NOT NULL,
  [criteria] text NULL
  PRIMARY KEY CLUSTERED ([session_event_id],[lesson_id])
)
ON [PRIMARY]
GO


CREATE TABLE [dbo].[at_relations] (
  [relation_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NULL,
  [respondents] text NULL,
  [relation_type] varchar(255) CONSTRAINT [DF__at_relations__29EC2402] DEFAULT '' NULL
  PRIMARY KEY CLUSTERED ([relation_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_relations_user_id] ON [at_relations] ([user_id]);
GO

CREATE TABLE [dbo].[at_session_respondents] (
  [session_respondent_id] int IDENTITY(1, 1) NOT NULL,
  [session_id] int NULL,
  [position_id] int NULL,
  [user_id] int NULL,
  [progress] int CONSTRAINT [DF__at_sessio__progr__3469B275] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([session_respondent_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_session_respondents_session_id] ON [at_session_respondents] ([session_id]);
CREATE INDEX [at_session_respondents_position_id] ON [at_session_respondents] ([position_id]);
CREATE INDEX [at_session_respondents_user_id] ON [at_session_respondents] ([user_id]);
GO

CREATE TABLE [dbo].[at_session_user_criterion_values] (
  [session_user_id] int CONSTRAINT [DF__at_sessio__sessi__0F382DC6] DEFAULT 0 NOT NULL,
  [criterion_id] int CONSTRAINT [DF__at_sessio__crite__102C51FF] DEFAULT 0 NOT NULL,
  [criterion_type] int CONSTRAINT [DF__at_sessio__critetyp__102C51FF] DEFAULT 1 NOT NULL,
  [value] float NULL
  PRIMARY KEY CLUSTERED ([session_user_id],[criterion_id],[criterion_type])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_session_users] (
  [session_user_id] int IDENTITY(1, 1) NOT NULL,
  [session_id] int NULL,
  [position_id] int NULL,
  [user_id] int NULL,
  [profile_id] int NULL,
  [process_id] int NULL,
  [vacancy_candidate_id] int NULL,
  [newcomer_id] int NULL,
  [reserve_id] int NULL,
  [status] int CONSTRAINT [DF__at_sessio__statu__7760A435] DEFAULT 0 NOT NULL,
  [total_competence] float NULL,
  [total_kpi] float NULL,
  [result_category] int NULL,
  PRIMARY KEY CLUSTERED ([session_user_id])
)
ON [PRIMARY]
GO

CREATE INDEX [at_session_users_session_id] ON [at_session_users] ([session_id]);
CREATE INDEX [at_session_users_position_id] ON [at_session_users] ([position_id]);
CREATE INDEX [at_session_users_user_id] ON [at_session_users] ([user_id]);
CREATE INDEX [at_session_users_profile_id] ON [at_session_users] ([profile_id]);
CREATE INDEX [at_session_users_process_id] ON [at_session_users] ([process_id]);
CREATE INDEX [at_session_users_vacancy_candidate_id] ON [at_session_users] ([vacancy_candidate_id]);
CREATE INDEX [at_session_users_newcomer_id] ON [at_session_users] ([newcomer_id]);
CREATE INDEX [at_session_users_reserve_id] ON [at_session_users] ([reserve_id]);
GO

CREATE TABLE [dbo].[at_sessions] (
  [session_id] int IDENTITY(1, 1) NOT NULL,
  [programm_type] int,
  [name] varchar(MAX) NULL,
  [shortname] varchar(MAX) NULL,
  [description] text NULL,
  [report_comment] text NULL,
  [cycle_id] int NULL,
  [begin_date] datetime NULL,
  [end_date] datetime NULL,
  [initiator_id] int NULL,
  [checked_soids] text NULL,
  [base_color] varchar(32) NULL,
  [state] int CONSTRAINT [DF__at_sessio__state__392E6792] DEFAULT 0 NOT NULL,
  [goal] varchar(255) NULL
  PRIMARY KEY CLUSTERED ([session_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [at_sessions_cycle_id] ON [at_sessions] ([cycle_id]);
CREATE INDEX [at_sessions_initiator_id] ON [at_sessions] ([initiator_id]);
GO

CREATE TABLE [dbo].[at_kpi_units] (
  [kpi_unit_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  PRIMARY KEY CLUSTERED ([kpi_unit_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_kpis] (
  [kpi_id] int IDENTITY(1, 1) NOT NULL,
  [kpi_cluster_id] int NOT NULL default (0),
  [kpi_unit_id] int NOT NULL default (0),
  [name] varchar(1024) NULL,
  [is_typical] int NOT NULL default (0),
  PRIMARY KEY CLUSTERED ([kpi_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_user_kpis] (
  [user_kpi_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NOT NULL default (0),
  [cycle_id] int NOT NULL default (0),
  [kpi_id] int NULL,
  [weight] float NULL,
  [value_plan] varchar(32) NULL,
  [value_fact] varchar(32) NULL,
  [comments] text NULL,
  PRIMARY KEY CLUSTERED ([user_kpi_id])
)
ON [PRIMARY]

GO

CREATE TABLE [dbo].[at_profile_kpis] (
  [profile_kpi_id] int IDENTITY(1, 1) NOT NULL,
  [profile_id] int NOT NULL default (0),
  [kpi_id] int NULL,
  [weight] float NULL,
  [value_plan] varchar(32) NULL,
  PRIMARY KEY CLUSTERED ([profile_kpi_id])
)
ON [PRIMARY]
GO


CREATE TABLE [dbo].[recruit_vacancy_hh_resume_ignore] (
	[vacancy_hh_resume_ignore_id] int IDENTITY(1, 1) NOT NULL,
	[vacancy_id] int NOT NULL DEFAULT ((0)),
	[hh_resume_id] int NOT NULL DEFAULT ((0)),
	[date] DATETIME NOT NULL,
	[create_user_id] int NOT NULL,
	PRIMARY KEY CLUSTERED ([vacancy_hh_resume_ignore_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_vacancy_hh_resume_ignore_hh_resume_id] ON [recruit_vacancy_hh_resume_ignore] ([vacancy_id],[hh_resume_id]);
CREATE INDEX [recruit_vacancy_hh_resume_ignore_create_user_id] ON [recruit_vacancy_hh_resume_ignore] ([create_user_id]);
GO

CREATE TABLE [dbo].[recruit_vacancies] (
  [vacancy_id] [int] IDENTITY(1, 1) NOT NULL,
  [vacancy_external_id] varchar(255) NULL,
  [name] varchar(255) NULL,
  [position_id] int NULL,
  [user_id] int NULL,
  [parent_position_id] int NULL,
  [parent_top_position_id] int NULL,
  [department_path] varchar(MAX) NULL,
  [created_by] int NULL,
  [profile_id] int NULL,
  [reason] int NULL,
  [create_date] datetime NULL,
  [open_date] datetime NULL,
  [close_date] datetime NULL,
  [complete_date] datetime default NULL,
  [complete_year] int default NULL,
  [complete_month] int default NULL,
  [work_place] text NULL,
  [work_mode] int NULL,
  [trip_mode] int NULL,
  [salary] varchar(255) NULL,
  [bonus] varchar(255) NULL,
  [subordinates] int NULL,
  [subordinates_count] int NULL,
  [subordinates_categories] text NULL,
  [tasks] text NULL,
  [status] int DEFAULT 0 NOT NULL,
  [age_min] varchar(255) NULL,
  [age_max] varchar(255) NULL,
  [gender] varchar(255) NULL,
  [education] text NULL,
  [requirements] text NULL,
  [search_channels_corporate_site] int NULL,
  [search_channels_recruit_sites] int NULL,
  [search_channels_papers] int NULL,
  [search_channels_papers_list] text NULL,
  [search_channels_universities] int NULL,
  [search_channels_universities_list] text NULL,
  [search_channels_workplace] int NULL,
  [search_channels_email] int NULL,
  [search_channels_inner] int NULL,
  [search_channels_outer] int NULL,
  [experience] text NULL,
  [experience_other] text NULL,
  [experience_companies] text NULL,
  [workflow] text NULL,
  [session_id] int NULL,
  [hh_vacancy_id] int NULL,
	[superjob_vacancy_id] INT NULL,
	[recruit_application_id] INT NULL,
	[deleted] INT NULL,
  PRIMARY KEY CLUSTERED ([vacancy_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE INDEX [recruit_vacancies_position_id] ON [recruit_vacancies] ([position_id]);
CREATE INDEX [recruit_vacancies_parent_position_id] ON [recruit_vacancies] ([parent_position_id]);
CREATE INDEX [recruit_vacancies_parent_top_position_id] ON [recruit_vacancies] ([parent_top_position_id]);
CREATE INDEX [recruit_vacancies_profile_id] ON [recruit_vacancies] ([profile_id]);
CREATE INDEX [recruit_vacancies_session_id] ON [recruit_vacancies] ([session_id]);
CREATE INDEX [recruit_vacancies_hh_vacancy_id] ON [recruit_vacancies] ([hh_vacancy_id]);
CREATE INDEX [recruit_vacancies_superjob_vacancy_id] ON [recruit_vacancies] ([superjob_vacancy_id]);
CREATE INDEX [recruit_vacancies_recruit_application_id] ON [recruit_vacancies] ([recruit_application_id]);
GO

CREATE TABLE [dbo].[recruit_vacancies_data_fields] (
  [data_field_id] [int] IDENTITY(1, 1) NOT NULL,
  [item_type] int NULL,
  [item_id] int NULL,
  [create_date] datetime NOT NULL,
  [last_update_date] datetime NOT NULL,
  [soid] int NULL, /*DEPRECATED*/
  [user_id] int NULL, /*DEPRECATED*/
  [vacancy_name] varchar(255) NOT NULL,
  [who_obeys] int NULL, /*DEPRECATED*/
  [subordinates_count] int NULL,
  [work_mode] int NULL,
  [type_contract] int NULL,
  [work_place] varchar(255) NULL,
  [probationary_period] varchar(255) NULL,
  [salary] varchar(50) NULL,
  [career_prospects] varchar(1024) NULL,
  [reason] int NULL,
  [tasks] varchar(1024) NULL,
  [education] varchar(1024) NULL,
  [skills] varchar(1024) NULL,
  [additional_education] varchar(1024) NULL,
  [knowledge_of_computer_programs] varchar(1024) NULL,
  [knowledge_of_foreign_languages] varchar(1024) NULL,
  [work_experience] varchar(1024) NULL,
  [experience_other] varchar(1024) NULL,
  [personal_qualities] varchar(1024) NULL,
  [other_requirements] varchar(1024) NULL,
  [number_of_vacancies] varchar(1024) NULL,

  PRIMARY KEY CLUSTERED ([data_field_id])
)
GO

CREATE INDEX [recruit_vacancies_data_fields_item_id] ON [recruit_vacancies_data_fields] ([item_id]);
CREATE INDEX [recruit_vacancies_data_fields_user_id] ON [recruit_vacancies_data_fields] ([user_id]);
GO

CREATE TABLE [dbo].[recruit_candidates] (
  [candidate_id] [int] IDENTITY(1, 1) NOT NULL,
  [candidate_external_id] varchar(255) NULL DEFAULT NULL,
  [user_id] int NULL,
  [source] int NULL,
  [file_id] int NULL,
  [resume_external_url] VARCHAR(255) NULL,
  [resume_external_id] VARCHAR(255) NULL DEFAULT NULL,
  [resume_json] TEXT NULL, /* varchar может не хватить */
  [resume_html] TEXT NULL,
  [resume_date] DATE NULL,
  [hh_area] varchar(255) NULL,
  [hh_metro] varchar(255) NULL,
  [hh_salary] varchar(255) NULL,
  [hh_total_experience] varchar(255) NULL,
  [hh_education] varchar(255) NULL,
  [hh_citizenship] varchar(255) NULL,
  [hh_age] INT NULL,
  [hh_gender] varchar(255) NULL,
  [hh_negotiation_id] varchar(255) NULL,
  PRIMARY KEY CLUSTERED ([candidate_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_candidates_candidate_external_id] ON [recruit_candidates] ([candidate_external_id]);
CREATE INDEX [recruit_candidates_user_id] ON [recruit_candidates] ([user_id]);
CREATE INDEX [recruit_candidates_file_id] ON [recruit_candidates] ([file_id]);
CREATE INDEX [recruit_candidates_resume_external_id] ON [recruit_candidates] ([resume_external_id]);
CREATE INDEX [recruit_candidates_hh_negotiation_id] ON [recruit_candidates] ([hh_negotiation_id]);
GO

CREATE TABLE [dbo].[recruit_reservists] (
  [reservist_id] [int] IDENTITY(1, 1) NOT NULL,
  [company] varchar(4000) NULL,
  [department] varchar(4000) NULL,
  [brigade] varchar(4000) NULL,
  [position] varchar(4000) NULL,
  [fio] varchar(4000) NULL,
  [gender] varchar(4000) NULL,
  [snils] varchar(4000) NULL,
  [birthday] DATE NULL,
  [age] int NULL,
  [region] varchar(4000) NULL,
  [citizenship] varchar(4000) NULL,
  [phone] varchar(4000) NULL,
  [phone_family] varchar(4000) NULL,
  [email] varchar(4000) NULL,
  [position_experience] varchar(4000) NULL,
  [sgc_experience] varchar(4000) NULL,
  [education] varchar(4000) NULL,
  [retraining] varchar(4000) NULL,
  [training] varchar(4000) NULL,
  [qualification_result] varchar(4000) NULL,
  [rewards] varchar(4000) NULL,
  [violations] varchar(4000) NULL,
  [comments_dkz_pk] varchar(4000) NULL,
  [relocation_readiness] varchar(4000) NULL,
  [evaluation_degree] varchar(4000) NULL,
  [leadership] varchar(4000) NULL,
  [productivity] varchar(4000) NULL,
  [quality_information] varchar(4000) NULL,
  [salary] varchar(4000) NULL,
  [hourly_rate] varchar(4000) NULL,
  [annual_income_rks] varchar(4000) NULL,
  [annual_income_no_rks] varchar(4000) NULL,
  [monthly_income_rks] varchar(4000) NULL,
  [monthly_income_no_rks] varchar(4000) NULL,
  [import_date] DATE NULL,
  [importer_id] INT NOT NULL DEFAULT ((0)),

  PRIMARY KEY CLUSTERED ([reservist_id])

)
ON [PRIMARY]
GO

CREATE UNIQUE NONCLUSTERED INDEX [recruit_reservists_snils] ON [dbo].[recruit_reservists]
(
    [snils] ASC
) ON [PRIMARY]
GO


CREATE TABLE [dbo].[recruit_candidate_hh_specializations] (
  [specialization_id] varchar(255) NOT NULL,
  [candidate_id] INT NOT NULL
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[recruit_vacancy_candidates] (
  [vacancy_candidate_id] int IDENTITY(1, 1) NOT NULL,
  [vacancy_id] int NULL,
  [candidate_id] int NULL,
  [process_id] int NULL,
  [user_id] int NULL,
  [status] int NULL,
  [result] INT NULL ,
  [reserve_position_id] INT NULL ,
  [external_status] VARCHAR(255) NULL ,

  PRIMARY KEY CLUSTERED ([vacancy_candidate_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_vacancy_candidates_vacancy_id] ON [recruit_vacancy_candidates] ([vacancy_id]);
CREATE INDEX [recruit_vacancy_candidates_candidate_id] ON [recruit_vacancy_candidates] ([candidate_id]);
CREATE INDEX [recruit_vacancy_candidates_user_id] ON [recruit_vacancy_candidates] ([user_id]);
CREATE INDEX [recruit_vacancy_candidates_process_id] ON [recruit_vacancy_candidates] ([process_id]);
GO

CREATE TABLE [dbo].[recruit_vacancy_recruiters](
	[vacancy_recruiter_id] [int] IDENTITY(1,1) NOT NULL,
	[vacancy_id] [int] NOT NULL,
	[recruiter_id] [int] NOT NULL,
 CONSTRAINT [PK_at_vacancy_recruiters] PRIMARY KEY CLUSTERED
(
	[vacancy_recruiter_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE INDEX [recruit_vacancy_recruiters_vacancy_id] ON [recruit_vacancy_recruiters] ([vacancy_id]);
CREATE INDEX [recruit_vacancy_recruiters_recruiter_id] ON [recruit_vacancy_recruiters] ([recruiter_id]);
GO

CREATE TABLE [dbo].[recruit_application](
	[recruit_application_id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] default NULL,
	[soid] [int] default NULL,
	[department_path] varchar(MAX) NULL,
	[created] datetime,
	[created_by] INT default NULL,
	[vacancy_name] [varchar](255) NULL,
	[vacancy_description] [text] NULL,
	[programm_name] [varchar](255) NULL,
  [status] INT default NULL,
  [saved_status] INT default NULL,
  [recruiter_user_id] INT NULL,
	[vacancy_id] INT DEFAULT NULL,
  PRIMARY KEY CLUSTERED ([recruit_application_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_application_user_id] ON [recruit_application] ([user_id]);
CREATE INDEX [recruit_application_recruiter_user_id] ON [recruit_application] ([recruiter_user_id]);
CREATE INDEX [recruit_application_vacancy_id] ON [recruit_application] ([vacancy_id]);
GO

CREATE TABLE [dbo].[recruit_newcomers] (
  [newcomer_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) null,
  [user_id] INT NULL ,
  [state] INT NULL,
  [state_change_date] DATETIME DEFAULT NULL,
  [vacancy_candidate_id] INT NULL ,
  [profile_id] INT NULL ,
  [position_id] INT NULL ,
  [department_path] varchar(MAX) NULL,
  [manager_id] INT NULL ,
  [process_id] INT NULL ,
  [session_id] INT NULL ,
  [created] date,
  [result] INT NULL,
  [status] INT NOT NULL default 0,
  [evaluation_user_id] INT NULL ,
  [evaluation_date] DATETIME NULL ,
  [evaluation_start_send] INT NOT NULL DEFAULT 0,
  [extended_to] DATE DEFAULT NULL,
  [final_comment] varchar(MAX) null,
  [welcome_training] INT NOT NULL DEFAULT 0,
  PRIMARY KEY CLUSTERED ([newcomer_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_newcomers_user_id] ON [recruit_newcomers] ([user_id]);
CREATE INDEX [recruit_newcomers_vacancy_candidate_id] ON [recruit_newcomers] ([vacancy_candidate_id]);
CREATE INDEX [recruit_newcomers_profile_id] ON [recruit_newcomers] ([profile_id]);
CREATE INDEX [recruit_newcomers_position_id] ON [recruit_newcomers] ([position_id]);
CREATE INDEX [recruit_newcomers_process_id] ON [recruit_newcomers] ([process_id]);
CREATE INDEX [recruit_newcomers_session_id] ON [recruit_newcomers] ([session_id]);
CREATE INDEX [recruit_newcomers_evaluation_user_id] ON [recruit_newcomers] ([evaluation_user_id]);
GO

CREATE TABLE [dbo].[recruit_newcomer_file] (
  [newcomer_file_id] [int] IDENTITY(1,1) NOT NULL,
	[newcomer_id] [int] NULL,
	[file_id] [int] NULL,
	[state_type] [int] NULL,
  PRIMARY KEY CLUSTERED ([newcomer_file_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_newcomer_file_newcomer_id] ON [recruit_newcomer_file] ([newcomer_id]);
CREATE INDEX [recruit_newcomer_file_file_id] ON [recruit_newcomer_file] ([file_id]);
GO

CREATE TABLE [dbo].[recruit_newcomer_recruiters] (
  [newcomer_recruiter_id] int IDENTITY(1, 1) NOT NULL,
  [newcomer_id] INT NULL,
  [recruiter_id] INT NULL,
  PRIMARY KEY CLUSTERED ([newcomer_recruiter_id])
)
ON [PRIMARY]
GO

-- CREATE TABLE [dbo].[recruit_newcomer_recruiters] (
--   [newcomer_file_id] int IDENTITY(1, 1) NOT NULL,
--   [newcomer_id] INT NULL,
--   [file_id] INT NULL,
--   [state_type] INT NULL,
--   PRIMARY KEY CLUSTERED ([newcomer_file_id])
-- )
-- ON [PRIMARY]
-- GO

CREATE INDEX [recruit_newcomer_recruiters_newcomer_id] ON [recruit_newcomer_recruiters] ([newcomer_id]);
CREATE INDEX [recruit_newcomer_recruiters_recruiter_id] ON [recruit_newcomer_recruiters] ([recruiter_id]);
GO

CREATE TABLE [dbo].[recruit_providers] (
  [provider_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) null,
  [status] INT NOT NULL DEFAULT '1',
  [locked] INT NOT NULL DEFAULT '0',
  [userform] INT NOT NULL DEFAULT '1',
  [cost] INT NOT NULL DEFAULT '1',
  PRIMARY KEY CLUSTERED ([provider_id])
)
ON [PRIMARY]
GO


CREATE TABLE [dbo].[recruit_actual_costs] (
    [actual_cost_id] int IDENTITY(1, 1) NOT NULL,
    [month] int null,
    [year] int null,
    [provider_id] int null,
    [cycle_id] int null,
    [document_number] varchar(255) null,
    [pay_date_document] date null,
    [pay_date_actual] date null,
    [pay_amount] float(20) null,
    [payment_type] varchar(255) null,
    PRIMARY KEY CLUSTERED ([actual_cost_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[subjects_actual_costs] (
    [actual_cost_id] int IDENTITY(1, 1) NOT NULL,
    [month] int null,
    [year] int null,
    [provider_id] int null,
    [document_number] varchar(255) null,
    [pay_date_document] date null,
    [pay_date_actual] date null,
    [pay_amount] float(20) null,
    [payment_type] varchar(255) null,
    [cycle_id] int null,
    [subject_id] int null,
    PRIMARY KEY CLUSTERED ([actual_cost_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_actual_costs_provider_id] ON [recruit_actual_costs] ([provider_id]);
GO

CREATE TABLE [dbo].[recruit_planned_costs] (
    [planned_cost_id] int IDENTITY(1, 1) NOT NULL,
    [month] int null,
    [year] int null,
    [provider_id] int null,
    [base_sum] float(20),
    [corrected_sum] float(20),
    [status] VARCHAR(20) NOT NULL default 'new',
    PRIMARY KEY CLUSTERED ([planned_cost_id])
)
ON [PRIMARY]
GO

CREATE INDEX [recruit_planned_costs_provider_id] ON [recruit_planned_costs] ([provider_id]);
GO

CREATE TABLE [dbo].[hr_reserve_positions] (
  [reserve_position_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(max) null,
  [position_id] INT NULL,
  [requirements] varchar(max) null,
  [formation_source] varchar(max) null,
  [description] varchar(255) null,
  [in_slider] int NOT NULL default 0,
  [app_gather_end_date] datetime null,
  [custom_respondents] [text] NULL,
  [recruiters] [text] NULL,
  PRIMARY KEY CLUSTERED ([reserve_position_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[hr_reserve_requests] (
  [reserve_request_id] INT IDENTITY(1, 1) NOT NULL,
  [user_id] INT NOT NULL,
  [position_id] INT NOT NULL,
  [reserve_id] INT NULL,
  [request_date] DATETIME NOT NULL,
  [status] TINYINT NOT NULL default 0,
  PRIMARY KEY CLUSTERED ([reserve_request_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[hr_reserves] (
  [reserve_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) null,
  [user_id] INT NULL ,
  [state_id] INT NULL,
  [state_change_date] DATETIME DEFAULT NULL,
  [profile_id] INT NULL ,
  [position_id] INT NULL ,
  [reserve_position_id] INT NOT NULL DEFAULT 0,
  [manager_id] INT NULL ,
  [process_id] INT NULL ,
  [session_id] INT NULL ,
  [created] date,
  [result] INT NULL,
  [status] INT NOT NULL default 0,
  [evaluation_user_id] INT NULL ,
  [evaluation_date] DATETIME NULL ,
  [evaluation_start_send] INT NOT NULL DEFAULT 0,
  [report_notification_sent] [tinyint] NOT NULL DEFAULT ((0)),
  [extended_to] DATE DEFAULT NULL,
  [final_comment] varchar(MAX) null,
  [cycle_id] int NULL,
  PRIMARY KEY CLUSTERED ([reserve_id])
)
ON [PRIMARY]
GO

CREATE INDEX [hr_reserves_user_id] ON [hr_reserves] ([user_id]);
CREATE INDEX [hr_reserves_profile_id] ON [hr_reserves] ([profile_id]);
CREATE INDEX [hr_reserves_position_id] ON [hr_reserves] ([position_id]);
CREATE INDEX [hr_reserves_process_id] ON [hr_reserves] ([process_id]);
CREATE INDEX [hr_reserves_session_id] ON [hr_reserves] ([session_id]);
CREATE INDEX [hr_reserves_evaluation_user_id] ON [hr_reserves] ([evaluation_user_id]);
GO

CREATE TABLE [dbo].[hr_rotation_recruiters] (
  [rotation_recruiter_id] int IDENTITY(1, 1) NOT NULL,
  [rotation_id] INT NULL,
  [recruiter_id] INT NULL,
  PRIMARY KEY CLUSTERED ([rotation_recruiter_id])
)
ON [PRIMARY]
GO

CREATE INDEX [hr_rotation_recruiters_rotation_id] ON [hr_rotation_recruiters] ([rotation_id]);
CREATE INDEX [hr_rotation_recruiters_recruiter_id] ON [hr_rotation_recruiters] ([recruiter_id]);
GO

CREATE TABLE [dbo].[hr_reserve_recruiters] (
  [reserve_recruiter_id] int IDENTITY(1, 1) NOT NULL,
  [reserve_id] INT NULL,
  [recruiter_id] INT NULL,
  PRIMARY KEY CLUSTERED ([reserve_recruiter_id])
)
ON [PRIMARY]
GO

CREATE INDEX [hr_reserve_recruiters_reserve_id] ON [hr_reserve_recruiters] ([reserve_id]);
CREATE INDEX [hr_reserve_recruiters_recruiter_id] ON [hr_reserve_recruiters] ([recruiter_id]);
GO

CREATE TABLE [dbo].[hr_reserve_files] (
  [reserve_file_id] [int] IDENTITY(1,1) NOT NULL,
  [reserve_id] [int] NULL,
  [file_id] [int] NULL,
  [state_type] [int] NULL,
  PRIMARY KEY CLUSTERED ([reserve_file_id])
)
ON [PRIMARY]
GO

CREATE INDEX [hr_reserve_files_reserve_id] ON [hr_reserve_files] ([reserve_id]);
CREATE INDEX [hr_reserve_files_file_id] ON [hr_reserve_files] ([file_id]);
GO

CREATE TABLE [dbo].[hr_rotations] (
  [rotation_id] int IDENTITY(1, 1) NOT NULL,
  [name] VARCHAR(255) NULL,
  [user_id] INT NOT NULL,
  [position_id] INT NULL,
  [begin_date] DATE NULL,
  [end_date] DATE NULL,
  [state_change_date] DATE NULL,
  [state_id] INT NOT NULL,
  [status] INT NOT NULL DEFAULT ((0)),
  [result] INT NULL,
  [report_notification_sent] [tinyint] NOT NULL DEFAULT ((0)),
  PRIMARY KEY CLUSTERED ([rotation_id])
)
ON [PRIMARY]
GO

CREATE INDEX [hr_rotations_user_id] ON [hr_rotations] ([user_id]);
CREATE INDEX [hr_rotations_position_id] ON [hr_rotations] ([position_id]);
GO

CREATE TABLE [dbo].[hr_rotation_files] (
  [rotation_file_id] [int] IDENTITY(1,1) NOT NULL,
	[rotation_id] [int] NULL,
	[file_id] [int] NULL,
	[state_type] [int] NULL,
  PRIMARY KEY CLUSTERED ([rotation_file_id])
)
ON [PRIMARY]
GO

CREATE INDEX [hr_rotation_files_rotation_id] ON [hr_rotation_files] ([rotation_id]);
CREATE INDEX [hr_rotation_files_file_id] ON [hr_rotation_files] ([file_id]);
GO


CREATE TABLE [dbo].[recruiters] (
  [recruiter_id] int IDENTITY(1, 1) NOT NULL,
  [user_id] int NULL,
  [hh_auth_data] VARCHAR(MAX) NULL
  PRIMARY KEY CLUSTERED ([recruiter_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[report_templates] (
  [rtid] int IDENTITY(1, 1) NOT NULL,
  [template_name] varchar(255) NULL,
  [report_name] varchar(255) NULL,
  [created] int CONSTRAINT [DF__report_te__creat__1798699D] DEFAULT 0 NOT NULL,
  [creator] int NULL,
  [edited] int NULL,
  [editor] int NULL,
  [template] text NULL
  PRIMARY KEY CLUSTERED ([rtid])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[scale_values] (
  [value_id] int IDENTITY(1, 1) NOT NULL,
  [scale_id] int CONSTRAINT [DF__scale_val__scale__6418C597] DEFAULT 1 NOT NULL,
  [value] int CONSTRAINT [DF__scale_val__value__650CE9D0] DEFAULT 0 NOT NULL,
  [text] varchar(255) NULL,
  [description] text NULL
  PRIMARY KEY CLUSTERED ([value_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO


CREATE TABLE [dbo].[scales] (
  [scale_id] [int] IDENTITY(1, 1) NOT NULL,
  [name] [varchar](255) CONSTRAINT [DF__scales__name__5E5FEC41] DEFAULT '' NOT NULL,
  [description] [varchar](4000) NULL,
  [type] [int] CONSTRAINT [DF__scales__type__5F54107A] DEFAULT 0 NOT NULL,
  [mode] [int] CONSTRAINT [DF__scales__mode__5F54107B] DEFAULT 0 NOT NULL
  PRIMARY KEY CLUSTERED ([scale_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[specializations] (
  [spid] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) CONSTRAINT [DF__specializa__name__230A1C49] DEFAULT '' NOT NULL,
  [discription] text NULL
  PRIMARY KEY CLUSTERED ([spid])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[states] (
  [scope] varchar(20) NULL,
  [scope_id] int NULL,
  [state] int NULL,
  [title] varchar(64) NULL
)
ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_states_scope] ON [dbo].[states]
(
    [scope] ASC
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_states_scopeid] ON [dbo].[states]
(
    [scope_id] ASC
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_states_state] ON [dbo].[states]
(
    [state] ASC
) ON [PRIMARY]
GO




/****** Object:  Table [dbo].[programm_events]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_events](
    [programm_event_id] [int] IDENTITY(1,1) NOT NULL,

	[programm_id] [int] NOT NULL CONSTRAINT [DF__programm_events_programm_id__7814D14C]  DEFAULT ('0'),
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__programm_events__name__7908F585]  DEFAULT (''),
	[type] [int] NOT NULL CONSTRAINT [DF__programm_events_type__7814D14C]  DEFAULT ('0'),
	[item_id] [int] NOT NULL CONSTRAINT [DF__programm_events_item_id__7814D14C]  DEFAULT ('0'),
	[day_begin] int DEFAULT ('1'),
	[day_end] int DEFAULT ('1'),
	[ordr] int,
 CONSTRAINT [PK_programm_events] PRIMARY KEY CLUSTERED
(
    [programm_event_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO



/****** Object:  Table [dbo].[programm_events_users]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_events_users](
    [programm_event_user_id] [int] IDENTITY(1,1) NOT NULL,
    [programm_event_id] [int] NOT NULL CONSTRAINT [DF__programm_events_users_event_id__7814D14C]  DEFAULT ('0'),
	[programm_id] [int],
	[user_id] [int] NOT NULL CONSTRAINT [DF__programm_events_user_id__7814D14C]  DEFAULT ('0'),
	[begin_date] [datetime] NULL,
	[end_date] [datetime] NULL,
	[status] [int] NOT NULL CONSTRAINT [DF__programm_events_status__7814D14C]  DEFAULT ('0'),
 CONSTRAINT [PK_programm_events_users] PRIMARY KEY CLUSTERED
(
    [programm_event_user_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[programm_users]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_users](
    [programm_user_id] [int] IDENTITY(1,1) NOT NULL,
    [programm_id] [int] NOT NULL CONSTRAINT [DF__programm_users_programm_id__7814D14C]  DEFAULT ('0'),
	[user_id] [int] NOT NULL CONSTRAINT [DF__programm_users_user_id_7814D14C]  DEFAULT ('0'),
    [assign_date] [datetime] NULL,
 CONSTRAINT [PK_programm_users] PRIMARY KEY CLUSTERED
(
    [programm_user_id] ASC
) ON [PRIMARY]


) ON [PRIMARY]
GO



/****** Object:  Table [dbo].[Students]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Students](
    [SID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__Students__MID__753864A1]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__Students__CID__762C88DA]  DEFAULT ((0)),
    [cgid] [int] NOT NULL CONSTRAINT [DF__Students__cgid__7720AD13]  DEFAULT ((0)),
    [Registered] [int] NOT NULL CONSTRAINT [DF__Students__Regist__7814D14C]  DEFAULT ('1'),
    [time_registered] [datetime] NULL,
    [offline_course_path] [varchar](255) NOT NULL CONSTRAINT [DF__Students__offlin__7908F585]  DEFAULT (''),
    [time_ended] [datetime] NULL,
	[time_ended_planned] [datetime] NULL,
 CONSTRAINT [PK_Students] PRIMARY KEY CLUSTERED
(
    [SID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_Students] ON [dbo].[Students]
(
    [MID] ASC,
    [CID] ASC
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[state_of_process]    Script Date: 04/20/2010 17:50:56 ******/


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[state_of_process](
    [state_of_process_id] [int] IDENTITY(1,1) NOT NULL,
    [item_id] [int] NOT NULL CONSTRAINT [DF__state_of_process__item_id__753864A1]  DEFAULT ((0)),
    [process_id] [int] NOT NULL CONSTRAINT [DF__state_of_process__process_id__762C88DA]  DEFAULT ((0)),
    [process_type] [int] NOT NULL CONSTRAINT [DF__state_of_process__process_type__7720AD13]  DEFAULT ((0)),
    [current_state] [varchar](255) NOT NULL CONSTRAINT [DF__state_of_process__current_state__7908F585]  DEFAULT (''),
    [passed_states] [text],
	[status] [int] NOT NULL CONSTRAINT [DF__state_of_process__status__7720AD13]  DEFAULT ((0)),
	[params] [text] NOT NULL CONSTRAINT [DF__state_of_process__params__07020F21]  DEFAULT (''),
 CONSTRAINT [PK_state_of_process] PRIMARY KEY CLUSTERED
(
    [state_of_process_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[webinar_chat]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[webinar_chat](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [pointId] [int] NOT NULL CONSTRAINT [DF__webinar_c__point__7DEDA633]  DEFAULT ((0)),
    [message] [varchar](255) NULL CONSTRAINT [DF__webinar_c__messa__7EE1CA6C]  DEFAULT (''),
    [datetime] [datetime] NULL,
    [userId] [int] NOT NULL CONSTRAINT [contraint_userid]  DEFAULT ((0)),
PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[webinars]    ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[webinars](
    [webinar_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__webinars__name__7DEDA635]  DEFAULT (('')),
    [create_date] [datetime] NULL,
    [subject_id] [int] NOT NULL CONSTRAINT [DF__webinars__subject_id]  DEFAULT ((0)),
PRIMARY KEY CLUSTERED
(
    [webinar_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[Teachers]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Teachers](
    [PID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__Teachers__MID__7AF13DF7]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__Teachers__CID__7BE56230]  DEFAULT ((0)),
 CONSTRAINT [PK_Teachers] PRIMARY KEY CLUSTERED
(
    [PID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[admins]    Script Date: 04/20/2010 17:50:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[admins](
    [AID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__admins__MID__7E6CC920]  DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_admins] ON [dbo].[admins]
(
    [AID] ASC
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_admins_1] ON [dbo].[admins]
(
    [MID] ASC
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[blog]
(
  [id]         INT IDENTITY(1,1) NOT NULL,
  [title]      [VARCHAR](255),
  [body]       [TEXT] NOT NULL,
  [created]    [DATETIME] NOT NULL,
  [created_by] [INT] NOT NULL,
  [subject_name] [VARCHAR](255),
  [subject_id] [INT] NOT NULL,
  CONSTRAINT blog_pk PRIMARY KEY (id)
)
GO

/****** Object:  Table [dbo].[courses_marks]    Script Date: 04/20/2010 17:50:55 ******/
/****** Object:  Table [dbo].[courses_marks]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[courses_marks](
    [cid] [int] NOT NULL CONSTRAINT [DF__courses_mar__cid__1D114BD1]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__courses_mar__mid__1E05700A]  DEFAULT ((0)),
    [mark] [int] NOT NULL CONSTRAINT [DF__courses_ma__mark__1EF99443]  DEFAULT ('-1'),
    [alias] [varchar](255) NOT NULL CONSTRAINT [DF__courses_m__alias__1FEDB87C]  DEFAULT (''),
	[confirmed] [bit] NOT NULL CONSTRAINT [DF_courses_m]  DEFAULT ((0)),
    [comments] [ntext] NOT NULL CONSTRAINT [DF__courses__mark_comment___2B3F6F97]  DEFAULT (''),
    [date] [datetime] NULL CONSTRAINT [DF__courses__mark_date___2B3F6F97]  DEFAULT (NULL),
    [certificate_validity_period] int,
 CONSTRAINT [PK_courses_marks] PRIMARY KEY CLUSTERED
(
    [cid] ASC,
    [mid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[deans]    Script Date: 04/20/2010 17:50:55 ******/
/****** Object:  Table [dbo].[cycles]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON

/****** Object:  Table [dbo].[curators]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cycles](
  [cycle_id]  [int] IDENTITY(1,1) NOT NULL,
  [name] [varchar](255) NULL CONSTRAINT [DF__cycles__name__47DBAE44]  DEFAULT (NULL),
  [begin_date] [date] NOT NULL,
  [end_date] [date] NOT NULL,
  [newcomer_id] [int] NULL,
  [reserve_id] [int] NULL,
  [type] [varchar](32) NULL DEFAULT (NULL),
  [year] [int] NULL,
  [quarter] [int] NULL,
  [status] [int] NULL,
  [created_by] [int] NULL, /*???*/
 CONSTRAINT [PK_cycles] PRIMARY KEY CLUSTERED
(
    [cycle_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[curators](
    [curator_id] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__curators__MID__45F365D3]  DEFAULT ((0)),
    [project_id] [int] NOT NULL DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_curators] ON [dbo].[curators]
(
    [curator_id] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_curators_MID] ON [dbo].[curators]
(
    [MID] ASC
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[curators_options]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[curators_options](
    [user_id] [int] NOT NULL,
    [unlimited_projects] [int] NOT NULL DEFAULT ((1)),
    [unlimited_classifiers] [int] NOT NULL DEFAULT ((1)),
    [assign_new_projects] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_curators_options] PRIMARY KEY CLUSTERED
(
    [user_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[curators_responsibilities]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[curators_responsibilities](
    [user_id] [int] NOT NULL,
    [classifier_id] [int] NOT NULL,
 CONSTRAINT [PK_curators_responsibilities] PRIMARY KEY CLUSTERED
(
    [user_id] ASC,
    [classifier_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[deans]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[deans](
    [DID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__deans__MID__45F365D3]  DEFAULT ((0)),
    [subject_id] [int] NOT NULL DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_deans] ON [dbo].[deans]
(
    [DID] ASC
) ON [PRIMARY]
GO



/****** Object:  Table [dbo].[deans_options]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[deans_options](
    [user_id] [int] NOT NULL,
    [unlimited_subjects] [int] NOT NULL DEFAULT ((1)),
    [unlimited_classifiers] [int] NOT NULL DEFAULT ((1)),
    [assign_new_subjects] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_deans_options] PRIMARY KEY CLUSTERED
(
    [user_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[dean_poll_users]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[dean_poll_users](
    [lesson_id] [int] NOT NULL DEFAULT ((0)),
    [head_mid] [int] NOT NULL DEFAULT ((0)),
    [student_mid] [int] NOT NULL DEFAULT ((0)),
)
GO

/****** Object:  Table [dbo].[deans_responsibilities]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[deans_responsibilities](
    [user_id] [int] NOT NULL,
    [classifier_id] [int] NOT NULL,
 CONSTRAINT [PK_deans_responsibilities] PRIMARY KEY CLUSTERED
(
    [user_id] ASC,
    [classifier_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[faq](
    [faq_id] [int] IDENTITY(1,1) NOT NULL,
    [question] [varchar](8000) NOT NULL CONSTRAINT [DF__faq__question__33F4B129]  DEFAULT (''),
    [answer] [varchar](8000) NOT NULL CONSTRAINT [DF__faq___answer__34E8D562]  DEFAULT (''),
    [roles] [varchar](255) NOT NULL CONSTRAINT [DF__faq__roles__35DCF99B]  DEFAULT (''),
    [published] [int] NOT NULL CONSTRAINT [DF__faq__published__35DCF99B] DEFAULT ((0)),
 CONSTRAINT [PK_faq] PRIMARY KEY CLUSTERED
(
    [faq_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[file]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[file](
    [kod] [varchar](100) NOT NULL CONSTRAINT [DF__file__kod__5629CD9C]  DEFAULT (''),
    [fnum] [int] NOT NULL CONSTRAINT [DF__file__fnum__571DF1D5]  DEFAULT ((0)),
    [ftype] [int] NOT NULL CONSTRAINT [DF__file__ftype__5812160E]  DEFAULT ((0)),
    [fname] [varchar](100) NOT NULL CONSTRAINT [DF__file__fname__59063A47]  DEFAULT (''),
    [fdata] [image] NOT NULL,
    [fdate] [int] NOT NULL CONSTRAINT [DF__file__fdate__59FA5E80]  DEFAULT ((0)),
    [fx] [int] NOT NULL CONSTRAINT [DF__file__fx__5AEE82B9]  DEFAULT ((0)),
    [fy] [int] NOT NULL CONSTRAINT [DF__file__fy__5BE2A6F2]  DEFAULT ((0)),
 CONSTRAINT [PK_file] PRIMARY KEY CLUSTERED
(
    [kod] ASC,
    [fnum] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

/****** Object:  Table [dbo].[files]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[files](
    [file_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__files__name]  DEFAULT (''),
    [path] [varchar](255) NOT NULL CONSTRAINT [DF__files__path]  DEFAULT (''),
    [file_size] [int] NOT NULL CONSTRAINT [DF__files__fy]  DEFAULT ((0)),
    [item_type] [int] NULL,
    [item_id] [int] NULL,
    [created_by] INT NULL,
    [created] DATETIME NULL,
 CONSTRAINT [PK_files] PRIMARY KEY CLUSTERED
(
    [file_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[videoblock]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[videoblock](
	[videoblock_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__videoblock__name]  DEFAULT (''),
    [file_id] [int] NOT NULL CONSTRAINT [DF__videoblock__file]  DEFAULT 0,
    [is_default] [int] NOT NULL DEFAULT 0,
    [embedded_code] [text],
 CONSTRAINT [PK_videoblock] PRIMARY KEY CLUSTERED
(
    [videoblock_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[formula]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[formula](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__formula__name__6754599E]  DEFAULT (''),
    [formula] [text] NOT NULL CONSTRAINT [DF__formula__formula__2B3F6F97]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__formula__type__68487DD7]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__formula__CID__693CA210]  DEFAULT ((0)),
 CONSTRAINT [PK_formula] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Tables for the forum service ******/

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_list](
    [forum_id] int NOT NULL IDENTITY PRIMARY KEY,
    [subject_id] int NOT NULL DEFAULT(0),
	  [subject] varchar(50) NOT NULL DEFAULT ('subject'),
    [user_id] int NOT NULL,
    [user_name] varchar(255) NOT NULL DEFAULT (''),
    [user_ip] varchar(16) NOT NULL DEFAULT ('127.0.0.1'),
    [title] varchar(255) NOT NULL,
    [created] datetime NOT NULL DEFAULT(0),
    [updated] datetime NOT NULL DEFAULT(0),
    [flags] int NOT NULL DEFAULT(0)
);
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_sections](
    [section_id] int NOT NULL IDENTITY PRIMARY KEY,
    [lesson_id] int NOT NULL DEFAULT(0),
    [subject] varchar(50) NOT NULL DEFAULT ('subject'),
    [forum_id] int NOT NULL,
    [user_id] int NOT NULL,
    [user_name] varchar(255) NOT NULL DEFAULT (''),
    [user_ip] varchar(16) NOT NULL DEFAULT ('127.0.0.1'),
    [parent_id] int NOT NULL DEFAULT(0),
    [title] varchar(255) NOT NULL,
    [text] text NOT NULL,
    [created] datetime NOT NULL DEFAULT(0),
    [updated] datetime NOT NULL DEFAULT(0),
    [last_msg] datetime NOT NULL DEFAULT(0),
    [count_msg] int NOT NULL DEFAULT(0),
    [order] int NOT NULL DEFAULT(0),
    [flags] int NOT NULL DEFAULT(0),
    [is_hidden] int NOT NULL DEFAULT(0),
    [deleted_by] int NOT NULL DEFAULT(0),
    [deleted] datetime NOT NULL DEFAULT(0),
    [edited_by] int NOT NULL DEFAULT(0),
    [edited] datetime NOT NULL DEFAULT(0)
);
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_messages](
    [message_id] int NOT NULL IDENTITY PRIMARY KEY,
    [forum_id] int NOT NULL,
    [section_id] int NOT NULL DEFAULT(0),
    [user_id] int NOT NULL,
    [user_name] varchar(255) NOT NULL DEFAULT (''),
    [user_ip] varchar(16) NOT NULL DEFAULT ('127.0.0.1'),
    [level] int NOT NULL DEFAULT(0),
    [answer_to] int NOT NULL DEFAULT(0),
    [title] varchar(255) NOT NULL,
    [text] text NOT NULL,
    [text_preview] varchar(255) NOT NULL,
    [text_size] int NOT NULL DEFAULT(0),
    [created] datetime NOT NULL DEFAULT(0),
    [updated] datetime NOT NULL DEFAULT(0),
    [delete_date] datetime NOT NULL DEFAULT(0),
    [deleted_by] int NOT NULL DEFAULT(0),
    [rating] int NOT NULL DEFAULT(0),
    [flags] int NOT NULL DEFAULT(0),
    [is_hidden] int NOT NULL DEFAULT(0)
);

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_messages_showed](
    [user_id] int NOT NULL,
    [message_id] int NOT NULL,
    [created] datetime2 NOT NULL,
    PRIMARY KEY([user_id], [message_id])
);
GO

/****** Object:  Table [dbo].[graduated]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[graduated](
    [SID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__graduated__MID__00200768]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__graduated__CID__01142BA1]  DEFAULT ((0)),
    [begin] [datetime] NULL,
    [end] [datetime] NULL,
    [certificate_id] [int] NOT NULL CONSTRAINT [DF__graduated__certificate__00200768]  DEFAULT ((0)),
    [created] [datetime] NULL,
    [status] INT NULL,
    [score] [varchar](200) NULL,
    [progress] [int] NOT NULL CONSTRAINT [DF__graduated__progress__00200768]  DEFAULT ((0)),
    [is_lookable] INT NULL   DEFAULT ((0)),
    [effectivity] FLOAT NULL,
    [application_id] INT NULL,
 CONSTRAINT [PK_graduated] PRIMARY KEY CLUSTERED
(
    [SID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[certificates]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[certificates](
    [certificate_id] [int] IDENTITY(1,1) NOT NULL,
    [user_id] [int] NOT NULL CONSTRAINT [DF__certificates__user_id__00200769]  DEFAULT ((0)),
    [subject_id] [int] NOT NULL CONSTRAINT [DF__certificates__subject_id__01142BA2]  DEFAULT ((0)),
    [created] [datetime] NULL,
    [name] [varchar](50) NULL,
    [description] [varchar](max) NULL,
    [organization] [varchar](50) NULL,
    [startdate] [date] NULL,
    [enddate] [date] NULL,
    [filename] [varchar](50) NULL,
    [type] [int] NOT NULL DEFAULT ((0)),
    [number] [varchar](255) NULL,
 CONSTRAINT [PK_certificates] PRIMARY KEY CLUSTERED
(
    [certificate_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[groupname]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[groupname](
    [gid] [int] IDENTITY(1,1) NOT NULL,
    [cid] [int] NOT NULL CONSTRAINT [DF__groupname__cid__02FC7413]  DEFAULT ((0)),
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__groupname__name__03F0984C]  DEFAULT (''),
    [owner_gid] [int] NULL,
 CONSTRAINT [PK_groupname] PRIMARY KEY CLUSTERED
(
    [gid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[groupuser]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[groupuser](
    [mid] [int] NOT NULL CONSTRAINT [DF__groupuser__mid__05D8E0BE]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__groupuser__cid__06CD04F7]  DEFAULT ((0)),
    [gid] [int] NOT NULL CONSTRAINT [DF__groupuser__gid__07C12930]  DEFAULT ((0)),
 CONSTRAINT [PK_groupuser] PRIMARY KEY CLUSTERED
(
    [mid] ASC,
    [gid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[hacp_debug]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[hacp_debug](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [message] [text] NOT NULL CONSTRAINT [DF__hacp_debu__messa__34C8D9D1]  DEFAULT (''),
    [date] [datetime] NOT NULL CONSTRAINT [DF__hacp_debug__date__4242D080]  DEFAULT ((0)),
    [direction] [int] NOT NULL CONSTRAINT [DF__hacp_debu__direc__4336F4B9]  DEFAULT ((0)),
 CONSTRAINT [PK_hacp_debug] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[help]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[help](
    [help_id] [int] IDENTITY(1,1) NOT NULL,
    [role] [varchar](255),
	[app_module] [varchar](25),
    [module] [varchar](255),
    [controller] [varchar](255),
    [action] [varchar](255),
    [link_subject] [int] NOT NULL CONSTRAINT [DF__help_link_subject__4336F4B9]  DEFAULT ((0)),
    [link] [varchar](255),
    [title] [varchar](255),
    [text] [text] NOT NULL CONSTRAINT [DF__help_text__34C8D9D1]  DEFAULT (''),
    [lang] [varchar](3) NOT NULL CONSTRAINT [DF_help_lang] DEFAULT(''),
	[moderated] int CONSTRAINT [DF__help__moderated__26DAAD2D] DEFAULT 0 NOT NULL,
	[is_active_version] int NULL,
 CONSTRAINT [PK_help] PRIMARY KEY CLUSTERED
(
    [help_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


/****** Object:  Table [dbo].[htmlpage]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[htmlpage](
    [page_id] [int] IDENTITY(1,1) NOT NULL,
    [group_id] [int],
    [name] [varchar](255),
	[ordr] [int] NOT NULL CONSTRAINT [DF__htmlpage__ordr__17F790F9]  DEFAULT ((10)),
    [text] [text] NOT NULL CONSTRAINT [DF__htmlpage_text__34C8D9D1]  DEFAULT (''),
	[url] [varchar](255) NOT NULL DEFAULT (('')),
	[description] [varchar(5000)],
	[icon_url] [varchar](255) NULL,
    [visible] [bit] NULL DEFAULT ((0)),
    [in_slider] [bit] NULL DEFAULT ((0)),
 CONSTRAINT [PK_htmlpage] PRIMARY KEY CLUSTERED
(
    [page_id] ASC
) ON [PRIMARY]
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[holidays](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255),
    [date] [datetime] NOT NULL CONSTRAINT [DF__holidays__date__4242D080]  DEFAULT ((0)),
    [type] [int] NULL CONSTRAINT [DF__holidays__type__0BC6C43E]  DEFAULT ((0)),
    [user_id] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_holidays] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
)
GO


/****** Object:  Table [dbo].[htmlpage_groups]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[htmlpage_groups](
    [group_id] [int] IDENTITY(1,1) NOT NULL,
    [lft] [int],
    [rgt] [int],
    [level] [int],
    [name] [varchar](255),
	[ordr] [int] NOT NULL CONSTRAINT [DF__htmlpagegr__ordr__17F790F9]  DEFAULT ((10)),
    [role] [varchar](255),
    [is_single_page] [tinyint](1) NULL
 CONSTRAINT [PK_htmlpage_groups] PRIMARY KEY CLUSTERED
(
    [group_id] ASC
) ON [PRIMARY]
)
GO

/****** Object:  Table [dbo].[library]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[library](
    [bid] [int] IDENTITY(1,1) NOT NULL,
    [parent] [int] NOT NULL CONSTRAINT [DF__library__parent__689D8392]  DEFAULT ((0)),
    [cats] [text] NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__library__mid__6991A7CB]  DEFAULT ((0)),
    [uid] [varchar](255) NOT NULL CONSTRAINT [DF__library__uid__6A85CC04]  DEFAULT (''),
    [title] [varchar](255) NOT NULL CONSTRAINT [DF__library__title__6B79F03D]  DEFAULT (''),
    [author] [varchar](255) NOT NULL CONSTRAINT [DF__library__author__6C6E1476]  DEFAULT (''),
    [publisher] [varchar](255) NOT NULL CONSTRAINT [DF__library__publish__6D6238AF]  DEFAULT (''),
    [publish_date] [varchar](4) NOT NULL CONSTRAINT [DF__library__publish__6E565CE8]  DEFAULT (''),
    [description] [text] NOT NULL CONSTRAINT [DF__library__descrip__3D5E1FD2]  DEFAULT (''),
    [keywords] [text] NOT NULL CONSTRAINT [DF__library__keyword__3E52440B]  DEFAULT (''),
    [filename] [varchar](255) NOT NULL CONSTRAINT [DF__library__filenam__6F4A8121]  DEFAULT (''),
    [location] [varchar](255) NOT NULL CONSTRAINT [DF__library__locatio__703EA55A]  DEFAULT (''),
    [metadata] [text] NOT NULL CONSTRAINT [DF__library__metadata__75F77EB0]  DEFAULT (''),
    [need_access_level] [int] NOT NULL CONSTRAINT [DF__library__need_ac__7132C993]  DEFAULT ('5'),
    [upload_date] [datetime] NOT NULL CONSTRAINT [DF__library__upload___7226EDCC]  DEFAULT ((0)),
    [is_active_version] [int] NOT NULL CONSTRAINT [DF__library__is_acti__731B1205]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__library__type__740F363E]  DEFAULT ((0)),
    [is_package] [int] NOT NULL CONSTRAINT [DF__library__is_pack__75035A77]  DEFAULT ((0)),
    [quantity] [int] NOT NULL CONSTRAINT [DF__library__quantit__75F77EB0]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__library__cid__75F77EB0]  DEFAULT ((0)),
    [content] [varchar](255) NOT NULL CONSTRAINT [DF_library_content]  DEFAULT (''),
    [scorm_params] [text] NOT NULL CONSTRAINT [DF_library_scorm_params]  DEFAULT (''),
    [cms] [tinyint] NOT NULL CONSTRAINT [DF_library_cms]  DEFAULT ((0)),
    [pointId] [int] NOT NULL CONSTRAINT [DF_library_pointId]  DEFAULT ((0)),
    [courses] [varchar](255) NOT NULL CONSTRAINT [DF_library_courses]  DEFAULT (''),
    [lms] [int] NOT NULL CONSTRAINT [DF_library_lms]  DEFAULT ((0)),
    [place] [varchar](255) NOT NULL CONSTRAINT [DF_library_place]  DEFAULT (''),
    [not_moderated] [bit] NOT NULL CONSTRAINT [DF_library_not_moder]  DEFAULT ((0)),
 CONSTRAINT [PK_library] PRIMARY KEY CLUSTERED
(
    [bid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[list]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[list](
    [kod] [varchar](100) NOT NULL CONSTRAINT [DF__list__kod__0F624AF8]  DEFAULT (''),
    [qtype] [int] NOT NULL CONSTRAINT [DF__list__qtype__10566F31]  DEFAULT ((0)),
    [qdata] [text] NOT NULL CONSTRAINT [DF__list__qdata__4222D4EF]  DEFAULT (''),
    [qtema] [varchar](255) NOT NULL CONSTRAINT [DF__list__qtema__114A936A]  DEFAULT (''),
    [qmoder] [int] NOT NULL CONSTRAINT [DF__list__qmoder__123EB7A3]  DEFAULT ((0)),
    [adata] [text] NOT NULL CONSTRAINT [DF__list__adata__4316F928]  DEFAULT (''),
    [balmax] [float] NOT NULL CONSTRAINT [DF__list__balmax__1332DBDC]  DEFAULT ((0)),
    [balmin] [float] NOT NULL CONSTRAINT [DF__list__balmin__14270015]  DEFAULT ((0)),
    [url] [text] NOT NULL CONSTRAINT [DF__list__url__440B1D61]  DEFAULT (''),
    [last] [int] NOT NULL CONSTRAINT [DF__list__last__151B244E]  DEFAULT ((0)),
    [timelimit] [int] NULL CONSTRAINT [DF__list__timelimit__160F4887]  DEFAULT (NULL),
    [weight] [text] NULL,
    [is_shuffled] [int] NULL CONSTRAINT [DF__list__is_shuffle__17036CC0]  DEFAULT ((1)),
    [created_by] [int] NOT NULL CONSTRAINT [DF__list__created_by__17F790F9]  DEFAULT ((0)),
    [timetoanswer] [int] NOT NULL CONSTRAINT [DF__list__timetoans__17F790F9]  DEFAULT ((0)),
    [prepend_test] [varchar](255) NOT NULL CONSTRAINT [DF__list__prepent_test__17F790F9]  DEFAULT (''),
    [is_poll] [int] NOT NULL CONSTRAINT [DF__list__ispoll__17F790F9]  DEFAULT ((0)),
    [ordr] [int] NOT NULL CONSTRAINT [DF__list__ordr__17F790F9]  DEFAULT ((10)),
    [name] [varchar](255) NULL DEFAULT NULL,
 CONSTRAINT [PK_list] PRIMARY KEY CLUSTERED
(
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

/****** Object:  Table [dbo].[list_files]    Script Date: 04/20/2010 17:50:55 ******/

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[list_files] (
  [kod] varchar(255) NOT NULL,
  [file_id] int NOT NULL
)
ON [PRIMARY]
GO

/****** Object:  Table [dbo].[logseance]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[logseance](
    [stid] [int] NOT NULL CONSTRAINT [DF__logseance__stid__19DFD96B]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__logseance__mid__1AD3FDA4]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__logseance__cid__1BC821DD]  DEFAULT ((0)),
    [tid] [int] NOT NULL CONSTRAINT [DF__logseance__tid__1CBC4616]  DEFAULT ((0)),
    [kod] [varchar](255) NOT NULL CONSTRAINT [DF__logseance__kod__1DB06A4F]  DEFAULT (''),
    [number] [int] NOT NULL CONSTRAINT [DF__logseance__numbe__1EA48E88]  DEFAULT ((0)),
    [time] [int] NOT NULL CONSTRAINT [DF__logseance__time__1F98B2C1]  DEFAULT ((0)),
    [bal] [float] NOT NULL CONSTRAINT [DF__logseance__bal__208CD6FA]  DEFAULT ((0)),
    [balmax] [float] NOT NULL CONSTRAINT [DF__logseance__balma__2180FB33]  DEFAULT ((0)),
    [balmin] [float] NOT NULL CONSTRAINT [DF__logseance__balmi__22751F6C]  DEFAULT ((0)),
    [good] [int] NOT NULL CONSTRAINT [DF__logseance__od__236943A5]  DEFAULT ((0)),
    [vopros] [text] NOT NULL CONSTRAINT [DF__logseance__vopros__245D67DE]  DEFAULT (''),
    [otvet] [text] NOT NULL CONSTRAINT [DF__logseance__otvet__245D67DE]  DEFAULT (''),
    [attach] [image] NOT NULL,
    [filename] [varchar](255) NOT NULL CONSTRAINT [DF__logseance__filen__245D67DE]  DEFAULT (''),
    [text] [text] NOT NULL,
    [sheid] [int] NOT NULL CONSTRAINT [DF__logseance__sheid__25518C17]  DEFAULT ((0)),
    [comments] [text] NULL,
    [review] [image] NULL,
    [review_filename] [varchar](255) DEFAULT (''),
    [qtema] [varchar](255) NOT NULL CONSTRAINT [DF__logseance__qtema__114A936A]  DEFAULT (''),
 CONSTRAINT [PK_logseance] PRIMARY KEY CLUSTERED
(
    [stid] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO



/****** Object:  Table [dbo].[loguser]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[loguser](
    [stid] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__loguser__mid__2739D489]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__loguser__cid__282DF8C2]  DEFAULT ((0)),
    [tid] [int] NOT NULL CONSTRAINT [DF__loguser__tid__29221CFB]  DEFAULT ((0)),
    [balmax] [float] NOT NULL CONSTRAINT [DF__loguser__balmax__2A164134]  DEFAULT ((0)),
    [balmin] [float] NOT NULL CONSTRAINT [DF__loguser__balmin__2B0A656D]  DEFAULT ((0)),
    [balmax2] [float] NOT NULL CONSTRAINT [DF__loguser__balmax2__2BFE89A6]  DEFAULT ((0)),
    [balmin2] [float] NOT NULL CONSTRAINT [DF__loguser__balmin2__2CF2ADDF]  DEFAULT ((0)),
    [bal] [float] NOT NULL CONSTRAINT [DF__loguser__bal__2DE6D218]  DEFAULT ((0)),
    [questdone] [int] NOT NULL CONSTRAINT [DF__loguser__questdo__2EDAF651]  DEFAULT ((0)),
    [questall] [int] NOT NULL CONSTRAINT [DF__loguser__questal__2FCF1A8A]  DEFAULT ((0)),
    [qty] [int] NOT NULL CONSTRAINT [DF__loguser__qty__30C33EC3]  DEFAULT ((0)),
    [free] [int] NOT NULL CONSTRAINT [DF__loguser__free__31B762FC]  DEFAULT ((0)),
    [skip] [int] NOT NULL CONSTRAINT [DF__loguser__skip__32AB8735]  DEFAULT ((0)),
    [start] [int] NOT NULL CONSTRAINT [DF__loguser__start__339FAB6E]  DEFAULT ((0)),
    [stop] [int] NOT NULL CONSTRAINT [DF__loguser__stop__3493CFA7]  DEFAULT ((0)),
    [fulltime] [int] NOT NULL CONSTRAINT [DF__loguser__fulltim__3587F3E0]  DEFAULT ((0)),
    [moder] [int] NOT NULL CONSTRAINT [DF__loguser__moder__367C1819]  DEFAULT ((0)),
    [needmoder] [int] NOT NULL CONSTRAINT [DF__loguser__needmod__37703C52]  DEFAULT ((0)),
    [status] [int] NOT NULL CONSTRAINT [DF__loguser__status__3864608B]  DEFAULT ((0)),
    [moderby] [int] NOT NULL CONSTRAINT [DF__loguser__moderby__395884C4]  DEFAULT ((0)),
    [modertime] [int] NOT NULL CONSTRAINT [DF__loguser__moderti__3A4CA8FD]  DEFAULT ((0)),
    [teachertest] [int] NOT NULL CONSTRAINT [DF__loguser__teacher__3B40CD36]  DEFAULT ((0)),
    [log] [image] NOT NULL,
    [sheid] [int] NOT NULL CONSTRAINT [DF__loguser__sheid__3B40CD36]  DEFAULT ((0)),
 CONSTRAINT [PK_loguser] PRIMARY KEY CLUSTERED
(
    [stid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
 

CREATE TABLE [dbo].[labor_safety_specs](
    [labor_safety_spec_id] [int] IDENTITY(1,1) NOT NULL,
    [user_id] [int] NOT NULL CONSTRAINT [DF__lss__MID__7E6CC920]  DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_lss] ON [dbo].[labor_safety_specs]
(
    [labor_safety_spec_id] ASC
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_lss_1] ON [dbo].[labor_safety_specs]
(
    [user_id] ASC
) ON [PRIMARY]
GO




/****** Object:  Table [dbo].[moderators] ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[moderators](
    [moderator_id] [int] IDENTITY(1,1) NOT NULL,
    [user_id] [int] NOT NULL CONSTRAINT [DF__moderators__user_id__4C6B5938]  DEFAULT (0),
    [project_id] [int] NOT NULL CONSTRAINT [DF__moderators__project_id__4C6B5938]  DEFAULT (0),
 CONSTRAINT [PK_moderators] PRIMARY KEY CLUSTERED
(
    [moderator_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[notice]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[notice](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [type] [int],
    [cluster] [varchar](32),
    [event] [varchar](255) NULL CONSTRAINT [DF__notice__event__4C6B5938]  DEFAULT (NULL),
    [receiver] [int] NULL CONSTRAINT [DF__notice__receiver__4D5F7D71]  DEFAULT (NULL),
    [title] [varchar](255) NULL CONSTRAINT [DF__notice__title__4E53A1AA]  DEFAULT (NULL),
    [message] [text] NULL CONSTRAINT [DF__notice__message__4F47C5E3]  DEFAULT (NULL),
	[enabled] [int] NOT NULL CONSTRAINT [DF__notice__enabled__540C7B00]  DEFAULT ((1)),
	[priority] [int] NOT NULL CONSTRAINT [DF__notice__priority__540C7B11]  DEFAULT ((1)),
 CONSTRAINT [PK_notice] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  UserDefinedFunction [dbo].[CONCAT]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[CONCAT](@str0 varchar(255) , @str1 varchar(255) = '123')
RETURNS varchar(255)
AS
BEGIN
    return @str0 + @str1;
END
GO
/****** Object:  Table [dbo].[news]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[news](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [date] [varchar](50) NULL CONSTRAINT [DF__news__date__5AB9788F]  DEFAULT (NULL),
    [author] [varchar](1024) NULL CONSTRAINT [DF__news__author__5BAD9CC8]  DEFAULT (NULL),
    [created] [datetime] NULL,
    [created_by] [int] NULL,
    [announce] text,
    [message] [text] NOT NULL CONSTRAINT [DF__news__message__4F7CD00D]  DEFAULT (''),
    [subject_name] varchar(255) NULL,
    [url] varchar(4000) NULL,
    [subject_id] int NULL,
	[icon_url] [varchar](255) NULL,
    [mobile] [bit] NULL DEFAULT ((0)),
 CONSTRAINT [PK_news] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[FROM_UNIXTIME]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[FROM_UNIXTIME] (@timestamp bigint)
RETURNS char(19)
AS
BEGIN
  DECLARE @iyear  int,    @imonth int,     @iday int,     @ihours int,     @iminutes int,     @iseconds int;
  DECLARE @cyear char(4), @cmonth char(2), @cday char(2), @chours char(2), @cminutes char(2), @cseconds char(2);

  DECLARE @i int, @current_timestamp bigint, @temp bigint;

  DECLARE @returned_date char(19);

  SET @i = 1970;
  SET @current_timestamp = 0;
  SET @temp = 0;

  WHILE (@current_timestamp < @timestamp)
     BEGIN
       SET @temp = @current_timestamp;
       IF (@i%4 = 0)
           SET @current_timestamp = @current_timestamp + 366*24*60*60;
       ELSE
           SET @current_timestamp = @current_timestamp + 365*24*60*60;
        SET @i = @i + 1;
     END

  IF(@i = 1971)
     SET @temp = 0;

  SET @iyear = @i - 1;

  IF(@iyear%4 = 0)
     BEGIN
        SET @i = 1;
        WHILE (@current_timestamp < @timestamp )
           BEGIN
              IF(@i != 1)
                 SET @temp = @current_timestamp;
              IF (@i = 1)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 2)
                 SET @current_timestamp = @current_timestamp + 29*24*60*60;
              ELSE IF (@i = 3)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 4)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 5)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 6)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 7)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 8)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 9)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 10)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 11)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 12)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              SET @i = @i + 1;
     END
     END
  ELSE
     BEGIN
        SET @i = 1;
        WHILE (@current_timestamp < @timestamp )
           BEGIN
              IF(@i != 1)
                 SET @temp = @current_timestamp;
              IF (@i = 1)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 2)
                 SET @current_timestamp = @current_timestamp + 28*24*60*60;
              ELSE IF (@i = 3)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 4)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 5)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 6)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 7)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 8)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 9)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 10)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              ELSE IF (@i = 11)
                 SET @current_timestamp = @current_timestamp + 30*24*60*60;
              ELSE IF (@i = 12)
                 SET @current_timestamp = @current_timestamp + 31*24*60*60;
              SET @i = @i + 1;                             END
     END

  SET @imonth = @i;

  SET @iday              = (@timestamp - @temp)/(24*60*60);
  SET @ihours            = (@timestamp - @temp - @iday*24*60*60)/(60*60);
  SET @iminutes          = (@timestamp - @temp - @iday*24*60*60 - @ihours*60*60)/60;
  SET @iseconds          =  @timestamp - @temp - @iday*24*60*60 - @ihours*60*60 - @iminutes*60;

  SET @iday = @iday + 1;


  SET @cyear = STR(@iyear, 4, 0);
  IF (LEN(@imonth) = 2)
     SET @cmonth = STR(@imonth, 2, 0);
  ELSE
     SET @cmonth = '0' + STR(@imonth, 1, 0);

  IF (LEN(@iday) = 2)
     SET @cday = STR(@iday, 2, 0);
  ELSE
     SET @cday = '0' + STR(@iday, 1, 0);


  IF (LEN(@ihours) = 2)
     SET @chours = STR(@ihours, 2, 0);
  ELSE
     SET @chours = '0' + STR(@ihours, 1, 0);

  IF (LEN(@iminutes) = 2)
     SET @cminutes = STR(@iminutes, 2, 0);
  ELSE
     SET @cminutes = '0' + STR(@iminutes, 1, 0);

  IF (LEN(@iseconds) = 2)
     SET @cseconds = STR(@iseconds, 2, 0);
  ELSE
     SET @cseconds = '0' + STR(@iseconds, 1, 0);


  SET @returned_date = @cyear + '-' + @cmonth + '-' + @cday + ' ' + @chours + ':' + @cminutes + ':' + @cseconds;

  return @returned_date;
END
GO
/****** Object:  Table [dbo].[news2]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[news2](
    [nID] [int] IDENTITY(1,1) NOT NULL,
    [date] [datetime] NULL,
    [Title] [varchar](255) NOT NULL CONSTRAINT [DF__news2__Title__5D95E53A]  DEFAULT (''),
    [author] [varchar](50) NULL CONSTRAINT [DF__news2__author__5E8A0973]  DEFAULT (NULL),
    [message] [text] NOT NULL CONSTRAINT [DF__news2__message__5165187F]  DEFAULT (''),
    [lang] [char](3) NOT NULL CONSTRAINT [DF__news2__lang__5F7E2DAC]  DEFAULT (''),
    [show] [int] NOT NULL CONSTRAINT [DF__news2__show__607251E5]  DEFAULT ((0)),
    [resource_id] [int] NOT NULL CONSTRAINT [DF__resource_id__show__607251E5]  DEFAULT ((0)),
    [standalone] [int] NOT NULL CONSTRAINT [DF__news2__standalon__6166761E]  DEFAULT ((0)),
    [application] int CONSTRAINT [DF__news2__applicati__0D1ADB2A] DEFAULT 0 NULL,
    [soid] varchar(16) NULL,
    [type] int CONSTRAINT [DF__news2__type__0E0EFF63] DEFAULT 0 NOT NULL
CONSTRAINT [PK_news2] PRIMARY KEY CLUSTERED
(
    [nID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[oauth_apps]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[oauth_apps](
    [app_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_title] DEFAULT (''),
    [description] [text] NOT NULL CONSTRAINT [DF_oauth_apps_description] DEFAULT (''),
    [created] [datetime] NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_oauth_apps_created_by] DEFAULT ((0)),
    [callback_url] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_callback_url] DEFAULT (''),
    [api_key] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_api_key] DEFAULT (''),
    [consumer_key] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_consumer_key] DEFAULT (''),
    [consumer_secret] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_consumer_secret] DEFAULT (''),
 CONSTRAINT [PK_oauth_apps] PRIMARY KEY CLUSTERED
(
    [app_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[oauth_tokens]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[oauth_tokens](
    [token_id] [int] IDENTITY(1,1) NOT NULL,
    [app_id] [int] NOT NULL CONSTRAINT [DF_oauth_tokens_app_id] DEFAULT ((0)),
    [token] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_tokens_token] DEFAULT (''),
    [token_secret] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_tokens_token_secret] DEFAULT (''),
    [state] [int] NOT NULL CONSTRAINT [DF_oauth_tokens_state] DEFAULT ((0)),
    [verify] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_tokens_token_verify] DEFAULT (''),
    [user_id] [int] NOT NULL CONSTRAINT [DF_oauth_tokens_user_id] DEFAULT ((0)),
 CONSTRAINT [PK_oauth_tokens] PRIMARY KEY CLUSTERED
(
    [token_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[oauth_nonces]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[oauth_nonces](
    [nonce_id] [int] IDENTITY(1,1) NOT NULL,
    [app_id] [int] NOT NULL CONSTRAINT [DF_oauth_nonces_app_id] DEFAULT ((0)),
    [ts] [datetime] NULL,
    [nonce] [varchar](255) NOT NULL CONSTRAINT [DF_oauth_nonces_nonce] DEFAULT (''),
 CONSTRAINT [PK_oauth_nonces] PRIMARY KEY CLUSTERED
(
    [nonce_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO


/****** Object:  UserDefinedFunction [dbo].[GREATEST]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[offlines](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [subject_id] [int] NOT NULL CONSTRAINT [DF_offlines_subjid] DEFAULT ((0)),
    [created] [datetime] NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_offlines_title] DEFAULT (''),
 CONSTRAINT [PK_offlines] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO


/****** Object:  UserDefinedFunction [dbo].[GREATEST]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO
CREATE FUNCTION [dbo].[GREATEST] (@first_entry bigint, @second_entry bigint)
RETURNS bigint
AS
BEGIN
    DECLARE @return_value bigint;
    IF(@first_entry <= @second_entry)
        SET @return_value = @second_entry;
    ELSE
        SET @return_value = @first_entry;
    RETURN @return_value;
END
GO
/****** Object:  Table [dbo].[organizations]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[organizations](
    [oid] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NULL CONSTRAINT [DF__organizat__title__65370702]  DEFAULT (NULL),
    [cid] [int] NULL CONSTRAINT [DF__organizatio__cid__662B2B3B]  DEFAULT (NULL),
    [root_ref] [int] NULL CONSTRAINT [DF__organizat__root___671F4F74]  DEFAULT (NULL),
    [level] [int] NULL CONSTRAINT [DF__organizat__level__681373AD]  DEFAULT (NULL),
    [next_ref] [int] NULL CONSTRAINT [DF__organizat__next___690797E6]  DEFAULT (NULL),
    [prev_ref] [int] NULL CONSTRAINT [DF__organizat__prev___69FBBC1F]  DEFAULT (NULL),
    [mod_ref] [int] NULL CONSTRAINT [DF__organizat__mod_r__6AEFE058]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__organizat__statu__6BE40491]  DEFAULT (NULL),
    [vol1] [int] NULL CONSTRAINT [DF__organizati__vol1__6CD828CA]  DEFAULT (NULL),
    [vol2] [int] NULL CONSTRAINT [DF__organizati__vol2__6DCC4D03]  DEFAULT (NULL),
    [metadata] [text] NULL,
    [module] [int] NOT NULL CONSTRAINT [DF__organizati__mod__6DCC4D03]  DEFAULT ((0)),
 CONSTRAINT [PK_organizations] PRIMARY KEY CLUSTERED
(
    [oid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO
CREATE TABLE [dbo].[organizations_bookmarks] (
  [bookmark_id] int IDENTITY(1,1) NOT NULL,
  [parent_id] int NOT NULL DEFAULT ((0)),
  [prev_id] int NOT NULL DEFAULT ((0)),
  [title] varchar(255) default NULL,
  [item_id] int NOT NULL DEFAULT ((0)),
    [user_id] int NOT NULL,
  [lesson_id] int NOT NULL DEFAULT ((0)),
  [resource_id] int NOT NULL DEFAULT ((0)),
  PRIMARY KEY  ([bookmark_id])
) ON [PRIMARY]
GO



/****** Object:  UserDefinedFunction [dbo].[LEAST]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO
CREATE FUNCTION [dbo].[LEAST] (@first_entry bigint, @second_entry bigint)
RETURNS bigint
AS
BEGIN
    DECLARE @return_value bigint;
    IF(@first_entry <= @second_entry)
        SET @return_value = @first_entry;
    ELSE
        SET @return_value = @second_entry;
    RETURN @return_value;
END
GO
/****** Object:  Table [dbo].[periods]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[periods](
    [lid] [int] IDENTITY(1,1) NOT NULL,
    [starttime] [int] NOT NULL CONSTRAINT [DF__periods__startti__0A688BB1]  DEFAULT ((540)),
    [stoptime] [int] NOT NULL CONSTRAINT [DF__periods__stoptim__0B5CAFEA]  DEFAULT ((630)),
    [name] [varchar](255) NULL CONSTRAINT [DF__periods__name__0C50D423]  DEFAULT (NULL),
    [count_hours] [int] NULL,
 CONSTRAINT [PK_periods] PRIMARY KEY CLUSTERED
(
    [lid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[PASSWORD]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[PASSWORD] (@pass varchar(255))
RETURNS varchar(255)
AS
BEGIN
RETURN SUBSTRING([master].[dbo].fn_varbintohexstr(HashBytes('MD5', @pass)), 3, 32)
END
GO
/****** Object:  Table [dbo].[permission2act]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[permission2act](
    [pmid] [int] NOT NULL CONSTRAINT [DF__permission__pmid__0F2D40CE]  DEFAULT ((0)),
    [acid] [varchar](8) NOT NULL CONSTRAINT [DF__permission__acid__10216507]  DEFAULT (''),
    [type] [varchar](255) NOT NULL CONSTRAINT [DF__permission__type__11158940]  DEFAULT ('dean'),
 CONSTRAINT [PK_permission2act] PRIMARY KEY CLUSTERED
(
    [pmid] ASC,
    [acid] ASC,
    [type] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[SHOW]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[SHOW] (@table_name  varchar(30))
RETURNS varchar(30)
AS
BEGIN
   RETURN @table_name
END
GO
/****** Object:  Table [dbo].[permission2mid]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[permission2mid](
    [pmid] [int] NOT NULL CONSTRAINT [DF__permission__pmid__12FDD1B2]  DEFAULT ((0)),
    [mid] [int] NULL CONSTRAINT [DF__permission2__mid__13F1F5EB]  DEFAULT (NULL)
) ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[UNIX_TIMESTAMP]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[UNIX_TIMESTAMP] (@indate datetime)
RETURNS BIGINT
AS
BEGIN
	DECLARE @diff BIGINT
	IF @indate < '19020101' OR @indate > '20380101'
	BEGIN
		SET @diff = DATEDIFF(minute, DATEADD(minute, DATEPART ( TZoffset , SYSDATETIMEOFFSET()), '1970-01-01 00:00:00' ), @indate)
		SET @diff = @diff * 60
	END
	ELSE
		SET @diff = DATEDIFF(second, DATEADD(minute, DATEPART ( TZoffset , SYSDATETIMEOFFSET()), '1970-01-01 00:00:00' ), @indate)
	RETURN @diff
END
GO

/****** Object:  Table [dbo].[permission_groups]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[permission_groups](
    [pmid] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](64) NULL CONSTRAINT [DF__permission__name__15DA3E5D]  DEFAULT (NULL),
    [default] [int] NULL CONSTRAINT [DF__permissio__defau__16CE6296]  DEFAULT ((0)),
    [type] [varchar](255) NULL CONSTRAINT [DF__permission__type__17C286CF]  DEFAULT ('dean'),
    [rang] [int] NOT NULL CONSTRAINT [DF__permission__rang__18B6AB08]  DEFAULT ((0)),
    [application] [int] NOT NULL CONSTRAINT [DF__permission__app__18B6AB08]  DEFAULT ((0)),
 CONSTRAINT [PK_permission_groups] PRIMARY KEY CLUSTERED
(
    [pmid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[ppt2swf]    Script Date: 02/01/2011 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ppt2swf](
    [status] [int] NOT NULL DEFAULT ('0'),
    [process] [int] NULL DEFAULT ('0'),
    [success_date] [datetime]  NULL,
    [pool_id] [int] NOT NULL DEFAULT ((0)),
    [url] [varchar](255) NOT NULL DEFAULT (''),
    [webinar_id] [int] NOT NULL DEFAULT ((0)),
) ON [PRIMARY]
GO

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[quizzes](
    [quiz_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_quizzes_status]  DEFAULT ((0)),
    [description] [text] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_quizzes_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_quizzes_questions]  DEFAULT ((0)),
    [data] [text] NOT NULL CONSTRAINT [DF_quizzes_data] DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_quizzes_subject_id]  DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF_quizzes_location]  DEFAULT ((0)),
    [calc_rating] [int] NOT NULL CONSTRAINT [DF_quizzes_calc_rating]  DEFAULT ((0)),
 CONSTRAINT [PK_quizzes] PRIMARY KEY CLUSTERED
(
    [quiz_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[quizzes_feedback](
    [user_id] [int] NOT NULL DEFAULT ((0)),
    [subject_id] [int] NOT NULL DEFAULT ((0)),
    [lesson_id] [int] NOT NULL DEFAULT((0)),
    [status] [int] NOT NULL DEFAULT ((0)),
    [begin] [datetime],
    [end] [datetime],
    [place] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback] DEFAULT (''),
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback_title] DEFAULT (''),
    [subject_name] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback_subject_name] DEFAULT (''),
    [trainer] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback_trainer] DEFAULT (''),
    [trainer_id] [int] NOT NULL DEFAULT ((0)),
	[created] [datetime],
 CONSTRAINT [PK_quizzes_feedback] PRIMARY KEY CLUSTERED
(
    [user_id] ASC,
    [subject_id] ASC,
    [lesson_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[quizzes_answers](
    [quiz_id] [int] NOT NULL CONSTRAINT [DF_quizzes_answers_quiz_id] DEFAULT ((0)),
    [question_id] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_question_id] DEFAULT (''),
    [question_title] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_question_title]  DEFAULT (''),
    [theme] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_theme]  DEFAULT (''),
    [answer_id] [int] NOT NULL CONSTRAINT [DF_quizzes_answers_answer_id] DEFAULT ((0)),
    [answer_title] [varchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_answer_title]  DEFAULT (''),
 CONSTRAINT [PK_quizzes_answers] PRIMARY KEY CLUSTERED
(
    [quiz_id] ASC,
    [question_id] ASC,
    [answer_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[tasks](
    [task_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_tasks_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_tasks_status]  DEFAULT ((0)),
    [description] [text] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_tasks_created_by]  DEFAULT ((0)),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_tasks_subject_id]  DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF_tasks_location]  DEFAULT ((0)),
 CONSTRAINT [PK_tasks] PRIMARY KEY CLUSTERED
(
    [task_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

CREATE TABLE [dbo].[tasks_variants](
	[variant_id] [int] IDENTITY(1,1) NOT NULL,
	[task_id] [int] NOT NULL,
	[name] [varchar](1024) NOT NULL,
	[description] [text] NOT NULL,
 CONSTRAINT [PK__tasks_variants] PRIMARY KEY CLUSTERED
(
	[variant_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]


GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


CREATE TABLE [dbo].[quizzes_results](
    [user_id] [int] NULL,
    [lesson_id] [int] NULL,
    [question_id] [varchar](255),
    [answer_id] [int] NOT NULL,
    [freeanswer_data] [text] NOT NULL CONSTRAINT [DF_quizzes_results_freeanswer_data]  DEFAULT (''),
    [quiz_id] [int] NOT NULL,
    [subject_id] [int] NULL CONSTRAINT [DF_quizzes_results_subject_id]  DEFAULT ((0)),
    [junior_id] [int] NOT NULL CONSTRAINT [DF_quizzes_results_junior_id] DEFAULT ((0)),
    [link_id] [int] NOT NULL CONSTRAINT [DF_quizzes_results_link_id] DEFAULT ((0))

) ON [PRIMARY]



GO

CREATE TABLE [dbo].[reports](
    [report_id] [int] IDENTITY(1,1) NOT NULL,
    [domain] [varchar](255) NULL CONSTRAINT [DF__reports__domain__36470DEF]  DEFAULT (NULL),
    [name] [varchar](255) NULL CONSTRAINT [DF__reports__name__36470DEF]  DEFAULT (NULL),
    [fields] [text] NULL,
    [created] [datetime],
    [created_by] [int] NOT NULL CONSTRAINT [DF__reports__created_by__40C49C62]  DEFAULT ((0)),
    [status] [int] NULL CONSTRAINT [DF__reports__status__29572725]  DEFAULT ((0)),
 CONSTRAINT [PK_reports] PRIMARY KEY CLUSTERED
(
    [report_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[responsibilities](
    [responsibility_id] int IDENTITY(1, 1) NOT NULL,
    [user_id] INT NOT NULL,
    [item_type] [int] NOT NULL,
    [item_id] [int] NOT NULL,
	PRIMARY KEY CLUSTERED ([responsibility_id])
) ON [PRIMARY]
GO



/****** Object:  Table [dbo].[rooms]    Script Date: 04/20/2010 17:50:56 ******/
/****** Object:  Table [dbo].[rooms]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rooms](
    [rid] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NULL CONSTRAINT [DF__rooms__name__36470DEF]  DEFAULT (NULL),
    [volume] [int] NULL CONSTRAINT [DF__rooms__volume__373B3228]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__rooms__status__382F5661]  DEFAULT (NULL),
    [type] [int] NULL CONSTRAINT [DF__rooms__type__39237A9A]  DEFAULT (NULL),
    [description] [varchar](max) NULL,
 CONSTRAINT [PK_rooms] PRIMARY KEY CLUSTERED
(
    [rid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[rooms2course]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rooms2course](
    [rid] [int] NULL CONSTRAINT [DF__rooms2cours__rid__3B0BC30C]  DEFAULT (NULL),
    [cid] [int] NULL CONSTRAINT [DF__rooms2cours__cid__3BFFE745]  DEFAULT (NULL)
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[schedule]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[schedule](
    [SHEID] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF__schedule__title__3DE82FB7]  DEFAULT (''),
    [url] [text] NULL,
    [descript] [text] NOT NULL CONSTRAINT [DF__schedule__descri__6383C8BA]  DEFAULT (''),
    [begin] [datetime] NOT NULL CONSTRAINT [DF__schedule__begin__3EDC53F0]  DEFAULT ((0)),
    [end] [datetime] NOT NULL CONSTRAINT [DF__schedule__end__3FD07829]  DEFAULT ((0)),
    [createID] [int] NOT NULL CONSTRAINT [DF__schedule__create__40C49C62]  DEFAULT ((0)),
	[createDate] [datetime] NULL,
    [typeID] [int] NOT NULL CONSTRAINT [DF__schedule__typeID__41B8C09B]  DEFAULT ((0)),
	[material_id] [int] NULL,
    [vedomost] [int] NULL CONSTRAINT [DF__schedule__vedomo__42ACE4D4]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__schedule__CID__43A1090D]  DEFAULT ((0)),
    [CHID] [int] NULL CONSTRAINT [DF__schedule__CHID__44952D46]  DEFAULT (NULL),
    [startday] [int] NOT NULL CONSTRAINT [DF__schedule__startd__4589517F]  DEFAULT ((0)),
    [stopday] [int] NOT NULL CONSTRAINT [DF__schedule__stopda__467D75B8]  DEFAULT ((0)),
    [timetype] [int] NOT NULL CONSTRAINT [DF__schedule__timety__477199F1]  DEFAULT ((0)),
    [isgroup] [int] NULL CONSTRAINT [DF__schedule__isgrou__4865BE2A]  DEFAULT ((0)),
    [cond_sheid] [varchar](255) NULL CONSTRAINT [DF__schedule__cond_s__4959E263]  DEFAULT ('-1'),
    [cond_mark] [varchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_m__4A4E069C]  DEFAULT ('-'),
    [cond_progress] [varchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_p__4B422AD5]  DEFAULT ((0)),
    [cond_avgbal] [varchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_a__4C364F0E]  DEFAULT ((0)),
    [cond_sumbal] [varchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_s__4D2A7347]  DEFAULT ((0)),
    [cond_operation] [int] NOT NULL CONSTRAINT [DF__schedule__cond_o__4E1E9780]  DEFAULT ((0)),
    [period] [varchar](255) NOT NULL CONSTRAINT [DF__schedule__period__4F12BBB9]  DEFAULT ('-1'),
    [rid] [int] NOT NULL CONSTRAINT [DF__schedule__rid__5006DFF2]  DEFAULT ((0)),
    [gid] [int] NOT NULL CONSTRAINT [DF__schedule__gid__5006DFF2]  DEFAULT ((0)),
    [teacher] [int] NOT NULL CONSTRAINT [DF__schedule__teache__50FB042B]  DEFAULT ((0)),
    [moderator] [int] NOT NULL CONSTRAINT [DF__schedule__moderator]  DEFAULT ((0)),
    [pub] [int] NOT NULL CONSTRAINT [DF__schedule__pub__50FB042B]  DEFAULT ((0)),
    [sharepointId] [int] NOT NULL CONSTRAINT [DF_schedule_sharepointId]  DEFAULT ((0)),
    [connectId] [varchar](255) NOT NULL CONSTRAINT [DF_schedule_connectId]  DEFAULT (''),
    [recommend] [bit] NOT NULL CONSTRAINT [DF_schedule_recommend]  DEFAULT ((0)),
    [notice] [int] NOT NULL CONSTRAINT [DF_schedule_notice]  DEFAULT ((0)),
    [notice_days] [int] NOT NULL CONSTRAINT [DF_schedule_notice_days]  DEFAULT ((0)),
    [all] [bit] NOT NULL CONSTRAINT [DF_schedule_all]  DEFAULT ((0)),
    [has_proctoring] [tinyint](4) NOT NULL default ((0)),
    [perm] int NOT NULL DEFAULT (0),
    [params] [text],
    [activities] [text],
    [threshold] [text],
	[order] [int] NOT NULL DEFAULT ((0)),
    [tool] [varchar](255) NOT NULL CONSTRAINT [DF_schedule_tool]  DEFAULT (''),
	[isfree] [tinyint] NOT NULL CONSTRAINT [DF__schedule__isfree__5006DFF2]  DEFAULT ((0)),
    [section_id] [int] NULL,
    [session_id] [int] NULL,
    [notify_before] int NOT NULL DEFAULT (0),
    [webinar_event_id] [int] NULL,

 CONSTRAINT [PK_schedule] PRIMARY KEY CLUSTERED
(
    [SHEID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

CREATE TABLE proctoring_files (
    [proctoring_file_id] [int] IDENTITY(1,1) NOT NULL,
	[type] VARCHAR(50) NOT NULL,
	SSID [INT] NOT NULL DEFAULT (0),
	url VARCHAR(1024) NULL,
	file_id [INT] NULL DEFAULT (0),
	stamp datetime NULL DEFAULT NULL,
	CONSTRAINT [proctoring_file_id] PRIMARY KEY CLUSTERED
	([proctoring_file_id] ASC) ON [PRIMARY]
);



/****** Object:  Table [dbo].[scheduleID]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[scheduleID](
    [SSID] [int] IDENTITY(1,1) NOT NULL,
    [SHEID] [int] NOT NULL CONSTRAINT [DF__scheduleI__SHEID__52E34C9D]  DEFAULT ((0)),
    [MID] [int] NOT NULL CONSTRAINT [DF__scheduleID__MID__53D770D6]  DEFAULT ((0)),
    [beginRelative] [datetime] NOT NULL CONSTRAINT [DF__scheduleID__beginRelative__3EDC53F0]  DEFAULT ((0)),
    [endRelative] [datetime] NOT NULL CONSTRAINT [DF__scheduleID__endRelative__3FD07829]  DEFAULT ((0)),
    [begin_personal] [datetime] NOT NULL DEFAULT ((0)),
    [end_personal] [datetime] NOT NULL  DEFAULT ((0)),
    [gid] [int] NULL CONSTRAINT [DF__scheduleID__gid__54CB950F]  DEFAULT (NULL),
    [isgroup] [int] NULL CONSTRAINT [DF__scheduleI__isgro__55BFB948]  DEFAULT ((0)),
    [V_STATUS] [float] NOT NULL CONSTRAINT [DF__scheduleI__V_STA__56B3DD81]  DEFAULT ((-1)),
	[V_DONE]  [int] NOT NULL CONSTRAINT [DF__scheduleI__V_DONE__56B3DD81]  DEFAULT ((0)),
    [V_DESCRIPTION] [varchar](255) NOT NULL CONSTRAINT [DF__scheduleI__V_DES__57A801BA]  DEFAULT (''),
    [DESCR] [text] NULL,
    [SMSremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__SMSre__589C25F3]  DEFAULT ((0)),
    [ICQremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__ICQre__59904A2C]  DEFAULT ((0)),
    [EMAILremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__EMAIL__5A846E65]  DEFAULT ((0)),
    [ISTUDremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__ISTUD__5B78929E]  DEFAULT ((0)),
    [test_corr] [int] NOT NULL CONSTRAINT [DF__scheduleI__test___5C6CB6D7]  DEFAULT ((0)),
    [test_wrong] [int] NOT NULL CONSTRAINT [DF__scheduleI__test___5D60DB10]  DEFAULT ((0)),
    [test_date] [datetime] NOT NULL CONSTRAINT [DF__scheduleI__test___5E54FF49]  DEFAULT ((0)),
    [test_answers] [text] NULL,
    [test_tries] [int] NULL CONSTRAINT [DF__scheduleI__test___5F492382]  DEFAULT ((0)),
    [toolParams] [text] NULL,
    [comments] [text] NULL,
    [chief] [int] NOT NULL CONSTRAINT [DF__scheduleI__chie___5F492383]  DEFAULT ((0)),
    [passed_proctoring] [tinyint](4) NOT NULL default ((0)),
    [video_proctoring] [tinyint](4) NOT NULL default ((0)),
    [auth_proctoring] [tinyint](4) NOT NULL default ((0)),
    [file_id] [int] NULL,
    [created] [datetime] NULL,
    [remote_event_id] [int] NULL,
    [updated] [datetime] NULL,
    [launched] [datetime] NULL,
 CONSTRAINT [PK_scheduleID] PRIMARY KEY CLUSTERED
(
    [SSID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE TABLE [dbo].[soap_activities] (
  [activity_id] int IDENTITY(1, 1) NOT NULL,
  [direction] int CONSTRAINT [DF__soap_acti__direc__70148828] DEFAULT '0' NOT NULL,
  [request] [text],
  [response] [text],
  [method] varchar(255) NOT NULL DEFAULT (''),
  [created] datetime,
  CONSTRAINT [PK__soap_act__482FBD636E2C3FB6] PRIMARY KEY CLUSTERED ([activity_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

/****** Object:  Table [dbo].[schedule_marks_history]    Script Date: 11/29/2011 16:00:00 ******/
/****** Object:  Table [dbo].[schedule_marks_history]    Script Date: 11/29/2011 16:00:00 ******/
CREATE TABLE [dbo].[schedule_marks_history]  ( 
	[MID]    	[int] NOT NULL,
	[SSID]  	[int] NOT NULL,
	[mark]   	[int] NOT NULL DEFAULT ((0)),
	[updated]	[datetime] NOT NULL
)
GO

/****** Object:  Table [dbo].[providers]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[providers](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_providers_title]  DEFAULT (''),
    [address] [text] NOT NULL CONSTRAINT [DF_providers_address]  DEFAULT (''),
    [contacts] [text] NOT NULL CONSTRAINT [DF_providers_contacts]  DEFAULT (''),
    [description] [text] NOT NULL CONSTRAINT [DF_providers_description]  DEFAULT (''),
 CONSTRAINT [PK_providers] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[suppliers]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[suppliers](
    [supplier_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_suppliers_title]  DEFAULT (''),
    [address] [text] NOT NULL CONSTRAINT [DF_suppliers_address]  DEFAULT (''),
    [contacts] [text] NOT NULL CONSTRAINT [DF_suppliers_contacts]  DEFAULT (''),
    [description] [text] NOT NULL CONSTRAINT [DF_suppliers_description]  DEFAULT (''),
 CONSTRAINT [PK_suppliers] PRIMARY KEY CLUSTERED
(
    [supplier_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[scorm_tracklog]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[scorm_tracklog](
    [trackID] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__scorm_track__mid__5772F790]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__scorm_track__cid__58671BC9]  DEFAULT ((0)),
    [ModID] [int] NOT NULL CONSTRAINT [DF__scorm_tra__ModID__595B4002]  DEFAULT ((0)),
    [McID] [int] NOT NULL CONSTRAINT [DF__scorm_trac__McID__5A4F643B]  DEFAULT ((0)),
    [lesson_id] [int] NOT NULL CONSTRAINT [DF__scorm_trac__lesson_id__5A4F643B]  DEFAULT ((0)),
    [trackdata] [ntext] NOT NULL default '',
    [stop] [datetime] NOT NULL CONSTRAINT [DF__scorm_trac__stop__5B438874]  DEFAULT ((0)),
    [start] [datetime] NOT NULL CONSTRAINT [DF__scorm_tra__start__5C37ACAD]  DEFAULT ((0)),
    [score] [float] NOT NULL CONSTRAINT [DF__scorm_tra__score__5D2BD0E6]  DEFAULT ((0)),
    [scoremax] [float] NOT NULL CONSTRAINT [DF__scorm_tra__score__5E1FF51F]  DEFAULT ((0)),
    [scoremin] [float] NOT NULL CONSTRAINT [DF__scorm_tra__score__5F141958]  DEFAULT ((0)),
    [status] [varchar](15) NOT NULL CONSTRAINT [DF__scorm_tra__statu__60083D91]  DEFAULT (''),
 CONSTRAINT [PK_scorm_tracklog] PRIMARY KEY CLUSTERED
(
    [trackID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[scorm_report]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[scorm_report](
    [report_id] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__scorm_report__mid__5772F790]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__scorm_report__cid__58671BC9]  DEFAULT ((0)),
    [lesson_id] [int] NOT NULL CONSTRAINT [DF__scorm_report__lesson_id__5A4F643B]  DEFAULT ((0)),
    [report_data] [image] NOT NULL,
    [updated] [datetime] NOT NULL CONSTRAINT [DF__scorm_report__updated__5B438874]  DEFAULT ((0)),
 CONSTRAINT [PK_scorm_report] PRIMARY KEY CLUSTERED
(
    [report_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[seance]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[seance](
    [stid] [int] NOT NULL CONSTRAINT [DF__seance__stid__61316BF4]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__seance__mid__6225902D]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__seance__cid__6319B466]  DEFAULT ((0)),
    [tid] [int] NOT NULL CONSTRAINT [DF__seance__tid__640DD89F]  DEFAULT ((0)),
    [kod] [varchar](255) NOT NULL CONSTRAINT [DF__seance__kod__6501FCD8]  DEFAULT (''),
    [attach] [image] NOT NULL,
    [filename] [varchar](255) NOT NULL CONSTRAINT [DF__seance__filename__65F62111]  DEFAULT (''),
    [text] [text] NOT NULL CONSTRAINT [DF__seance__text__6501FCD8]  DEFAULT (''),
    [time] [datetime] NULL,
    [bal] [float] NULL CONSTRAINT [DF__seance__bal__66EA454A]  DEFAULT (NULL),
    [lastbal] [float] NULL CONSTRAINT [DF__seance__lastbal__67DE6983]  DEFAULT (NULL),
    [comments] [text] NULL,
    [review] [image] NULL,
    [review_filename] [varchar](255) NOT NULL CONSTRAINT [DF_seance_review_filename]  DEFAULT (''),
 CONSTRAINT [PK_seance] PRIMARY KEY CLUSTERED
(
    [stid] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO


/****** Object:  Table [dbo].[sessions]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[sessions](
    [sessid] [int] IDENTITY(1,1) NOT NULL,
    [sesskey] [varchar](32) NOT NULL CONSTRAINT [DF__sessions__sesske__61F08603]  DEFAULT (''),
    [mid] [int] NOT NULL CONSTRAINT [DF__sessions__mid__62E4AA3C]  DEFAULT ((0)),
    [start] [datetime] NOT NULL CONSTRAINT [DF__sessions__start__63D8CE75]  DEFAULT ((0)),
    [stop] [datetime] NOT NULL CONSTRAINT [DF__sessions__stop__64CCF2AE]  DEFAULT ((0)),
    [ip] [varchar](16) NOT NULL CONSTRAINT [DF__sessions__ip__65C116E7]  DEFAULT (''),
    [logout] [int] NOT NULL CONSTRAINT [DF__sessions__logout__66B53B20]  DEFAULT ((0)),
    [browser_name] [varchar](64) NULL,
    [browser_version] [varchar](64) NULL,
    [flash_version] [varchar](64) NULL,
    [os] [varchar](64) NULL,
    [screen] [varchar](64) NULL,
    [cookie] [smallint] NULL,
    [js] [smallint] NULL,
    [java_version] [varchar](64) NULL,
    [silverlight_version] [varchar](64) NULL,
    [acrobat_reader_version] [varchar](64) NULL,
    [msxml_version] [varchar](64) NULL,
    [lesson_id] [int] NULL,
    [course_id] [int] NULL,
    [resource_id] [int] NULL,
    [lesson_type] [int] NULL,
 CONSTRAINT [PK_sessions] PRIMARY KEY CLUSTERED
(
    [sessid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[structure_of_organ]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[structure_of_organ](
    [soid] [int] IDENTITY(1,1) NOT NULL,
    [soid_external] [varchar](255) NULL CONSTRAINT [DF__structure__soid___6D9742D9]  DEFAULT (NULL),
    [name] [varchar](255) NULL CONSTRAINT [DF__structure___name__6E8B6712]  DEFAULT (NULL),
    [code] [varchar](16) NULL CONSTRAINT [DF__structure___code__6F7F8B4B]  DEFAULT (NULL),
    [mid] [int] NULL CONSTRAINT [DF__structure_o__mid__7073AF84]  DEFAULT ((0)),
    [info] [varchar](4000) NULL CONSTRAINT [DF__structure___info__6E8B6712]  DEFAULT (NULL),
    [owner_soid] [int] NULL CONSTRAINT [DF__structure__owner__7167D3BD]  DEFAULT (NULL),
    [agreem] [int] NULL CONSTRAINT [DF__structure__agree__725BF7F6]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__structure___type__73501C2F]  DEFAULT ((0)),
    [own_results] [int] NOT NULL CONSTRAINT [DF__structure___ownres__73501C2F]  DEFAULT ((1)),
    [enemy_results] [int] NOT NULL CONSTRAINT [DF__structure___enemyres__73501C2F]  DEFAULT ((1)),
    [display_results] [int] NOT NULL CONSTRAINT [DF__structure___dispres__73501C2F]  DEFAULT ((0)),
    [threshold] [int] NOT NULL CONSTRAINT [DF__structure___threshold__73501C2F]  DEFAULT ((0)),
	[lft] [int] NOT NULL CONSTRAINT [DF__structure___lft__73501C2F]  DEFAULT ((0)),
	[level] [int] NOT NULL CONSTRAINT [DF__structure___level__73501C2F]  DEFAULT ((0)),
	[rgt] [int] NOT NULL CONSTRAINT [DF__structure___rgt__73501C2F]  DEFAULT ((0)),
	[is_manager] [int] NOT NULL CONSTRAINT [DF__structure___is_manager__73501C2F]  DEFAULT ((0)),
	[profile_id] int NULL,
	[original_profile_id] int NULL,
	[specialization] int CONSTRAINT [DF__structure__speci__11DF9047] DEFAULT 0 NOT NULL,
	[claimant] int CONSTRAINT [DF__structure__claim__12D3B480] DEFAULT 0 NOT NULL,
    [position_date] DATE DEFAULT NULL,
	[blocked] int NOT NULL DEFAULT (0),
    [employment_type] [varchar](16) NULL,
    [employee_status] [tinyint](1) NULL,
    [manager_soid] [int] DEFAULT (NULL),
	[staff_unit_id] [int] DEFAULT (NULL),
	[is_first_position] [int] DEFAULT (NULL),
	[created_at] [date], /*!!!*/
	[deleted_at] [date], /*!!!*/
    [is_integration2] int DEFAULT 0,
    [last_at_session_id] int NOT NULL DEFAULT (0),
CONSTRAINT [PK_structure_of_organ] PRIMARY KEY CLUSTERED
(
    [soid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** ВАЖНО!!!! последовательность и состав полей должна быть такая же как в structure_of_organ ******/

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[structure_of_organ_history] (
  [soid] int NULL,
  [soid_external] varchar(255) COLLATE Cyrillic_General_CI_AS CONSTRAINT [DF__struct_hist__soid___6D9742D9] DEFAULT NULL NULL,
  [name] varchar(255) COLLATE Cyrillic_General_CI_AS CONSTRAINT [DF__struct_hist___name__6E8B6712] DEFAULT NULL NULL,
  [code] varchar(16) COLLATE Cyrillic_General_CI_AS CONSTRAINT [DF__struct_hist___code__6F7F8B4B] DEFAULT NULL NULL,
  [mid] int CONSTRAINT [DF__struct_hist_o__mid__7073AF84] DEFAULT 0 NULL,
  [info] varchar(4000) COLLATE Cyrillic_General_CI_AS CONSTRAINT [DF__struct_hist___info__6E8B6712] DEFAULT NULL NULL,
  [owner_soid] int CONSTRAINT [DF__struct_hist__owner__7167D3BD] DEFAULT NULL NULL,
  [agreem] int CONSTRAINT [DF__struct_hist__agree__725BF7F6] DEFAULT 0 NULL,
  [type] int CONSTRAINT [DF__struct_hist___type__73501C2F] DEFAULT 0 NOT NULL,
  [own_results] int CONSTRAINT [DF__struct_hist___ownres__73501C2F] DEFAULT 1 NOT NULL,
  [enemy_results] int CONSTRAINT [DF__struct_hist___enemyres__73501C2F] DEFAULT 1 NOT NULL,
  [display_results] int CONSTRAINT [DF__struct_hist___dispres__73501C2F] DEFAULT 0 NOT NULL,
  [threshold] int CONSTRAINT [DF__struct_hist___threshold__73501C2F] DEFAULT 0 NOT NULL,
  [lft] int CONSTRAINT [DF__struct_hist___lft__73501C2F] DEFAULT 0 NOT NULL,
  [level] int CONSTRAINT [DF__struct_hist___level__73501C2F] DEFAULT 0 NOT NULL,
  [rgt] int CONSTRAINT [DF__struct_hist___rgt__73501C2F] DEFAULT 0 NOT NULL,
  [is_manager] int CONSTRAINT [DF__struct_hist___is_manager__73501C2F] DEFAULT 0 NOT NULL,
  [profile_id] int NULL,
  [original_profile_id] int NULL,
  [specialization] int CONSTRAINT [DF__struct_hist__speci__11DF9047] DEFAULT 0 NOT NULL,
  [claimant] int CONSTRAINT [DF__struct_hist__claim__12D3B480] DEFAULT 0 NOT NULL,
  [position_date] date NULL,
  [blocked] int CONSTRAINT [DF__struct_hist__block__68536ACF] DEFAULT 0 NOT NULL,
  [employment_type] [varchar](16) NULL,
  [employee_status] [tinyint](1) NULL,
  [manager_soid] [int] DEFAULT (NULL),
  [staff_unit_id] int NULL,
  [is_first_position] [int] DEFAULT (NULL),
  [created_at] date NULL,
  [deleted_at] date NULL,
  [is_integration2] int DEFAULT 0
)
ON [PRIMARY]
GO


/****** Object:  Table [dbo].[staff_units]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[staff_units](
  [staff_unit_id] [int] IDENTITY(1,1),
  [staff_unit_id_external] [varchar](255) DEFAULT NULL,
  [manager_staff_unit_id_external] [varchar](255) DEFAULT NULL,
  [soid] [int] DEFAULT NULL,
  [profile_id] [int] DEFAULT NULL,
  [name] [varchar](255) DEFAULT NULL,
  [quantity] [int] DEFAULT (0),
  [quantity_text] [int] varchar (50),
CONSTRAINT [PK_staff_unit_id] PRIMARY KEY CLUSTERED
(
    [staff_unit_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

 
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subscriptions]  (
    [subscription_id] [int] IDENTITY(1,1) NOT NULL,
    [user_id]         [int] NOT NULL,
    [channel_id]      [int] NOT NULL,
    CONSTRAINT [PK_subscriptions] PRIMARY KEY([subscription_id])
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subscription_entries]  (
    [entry_id]    [int] IDENTITY(1,1) NOT NULL,
    [channel_id]  [int] NOT NULL,
    [title]       [varchar](255) NULL,
    [link]        [varchar](255) NULL,
    [description] [text] NULL,
    [content]     [text] NULL,
    [author]      [int] NOT NULL,
    CONSTRAINT [PK_subscription_entries] PRIMARY KEY([entry_id])
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subscription_channels]  (
    [channel_id]    [int] IDENTITY(1,1) NOT NULL,
    [activity_name] [varchar](45) NULL,
    [subject_name]  [varchar](45) NULL,
    [subject_id]    [int] NOT NULL CONSTRAINT [DF__subscript__subje__01741E54]  DEFAULT ((0)),
    [subject] varchar(50) NOT NULL DEFAULT ('subject'),
    [lesson_id]     [int] NOT NULL CONSTRAINT [DF__subscript__lesso__0268428D]  DEFAULT ((0)),
    [title]         [varchar](255) NULL,
    [description]   [text] NULL,
    [link]          [varchar](255) NULL,
    CONSTRAINT [PK_subscription_channels] PRIMARY KEY([channel_id])
)
GO


/****** Object:  Table [dbo].[tag]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tag](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [body] [varchar](255) NULL
CONSTRAINT [PK_tag] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tag_ref]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tag_ref](
    [tag_id] [int] NOT NULL CONSTRAINT [DF__tag_ref__tag_id__7FB5F314]  DEFAULT ((0)),
    [item_type] [int] NOT NULL CONSTRAINT [DF__tag_ref__item_type__7FB5F314]  DEFAULT ((0)),
    [item_id] [int] NOT NULL CONSTRAINT [DF__tag_ref__item_id__7FB5F314]  DEFAULT ((0))
CONSTRAINT [PK_tag_ref] PRIMARY KEY CLUSTERED
(
    [tag_id] ASC,
    [item_type] ASC,
    [item_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[test]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[test](
    [tid] [int] IDENTITY(1,1) NOT NULL,
    [cid] [int] NOT NULL CONSTRAINT [DF__test__cid__047AA831]  DEFAULT ((0)),
    [cidowner] [int] NOT NULL CONSTRAINT [DF__test__cidowner__056ECC6A]  DEFAULT ((0)),
    [title] [varchar](255) NOT NULL CONSTRAINT [DF__test__title__0662F0A3]  DEFAULT (''),
    [datatype] [int] NOT NULL CONSTRAINT [DF__test__datatype__075714DC]  DEFAULT ((0)),
    [data] [text] NOT NULL CONSTRAINT [DF__test__data__6EF57B66]  DEFAULT (''),
    [random] [int] NOT NULL CONSTRAINT [DF__test__random__084B3915]  DEFAULT ((0)),
    [lim] [int] NOT NULL CONSTRAINT [DF__test__lim__093F5D4E]  DEFAULT ((0)),
    [qty] [int] NOT NULL CONSTRAINT [DF__test__qty__0A338187]  DEFAULT ('1'),
    [sort] [int] NOT NULL CONSTRAINT [DF__test__sort__0B27A5C0]  DEFAULT ((0)),
    [free] [int] NOT NULL CONSTRAINT [DF__test__free__0C1BC9F9]  DEFAULT ((0)),
    [skip] [int] NOT NULL CONSTRAINT [DF__test__skip__0D0FEE32]  DEFAULT ((0)),
    [rating] [int] NOT NULL CONSTRAINT [DF__test__rating__0E04126B]  DEFAULT ((0)),
    [status] [int] NOT NULL CONSTRAINT [DF__test__status__0EF836A4]  DEFAULT ((0)),
    [questres] [int] NOT NULL CONSTRAINT [DF__test__questres__0FEC5ADD]  DEFAULT ((0)),
    [endres] [int] NOT NULL CONSTRAINT [DF__test__endres__10E07F16]  DEFAULT ('1'),
    [showurl] [int] NOT NULL CONSTRAINT [DF__test__showurl__11D4A34F]  DEFAULT ('1'),
    [showotvet] [int] NOT NULL CONSTRAINT [DF__test__showotvet__12C8C788]  DEFAULT ((0)),
    [timelimit] [int] NOT NULL CONSTRAINT [DF__test__timelimit__13BCEBC1]  DEFAULT ((0)),
    [startlimit] [int] NOT NULL CONSTRAINT [DF__test__startlimit__14B10FFA]  DEFAULT ('1'),
    [limitclean] [int] NOT NULL CONSTRAINT [DF__test__limitclean__15A53433]  DEFAULT ((0)),
    [last] [int] NOT NULL CONSTRAINT [DF__test__last__1699586C]  DEFAULT ((0)),
    [lastmid] [int] NOT NULL CONSTRAINT [DF__test__lastmid__178D7CA5]  DEFAULT ((0)),
    [cache_qty] [int] NOT NULL CONSTRAINT [DF__test__cache_qty__1881A0DE]  DEFAULT ((0)),
    [random_vars] [text] NULL,
    [allow_view_log] [int] NOT NULL CONSTRAINT [DF__test__allow_view__1975C517]  DEFAULT ((1)),
    [created_by] [int] NOT NULL CONSTRAINT [DF__test__created_by__1A69E950]  DEFAULT ((0)),
    [comments] [text] NULL,
    [mode] [int] NOT NULL CONSTRAINT [DF__test__mode__1B5E0D89]  DEFAULT ((0)),
    [is_poll] [int] NOT NULL CONSTRAINT [DF__test__ispoll__1B5E0D89]  DEFAULT ((0)),
    [poll_mid] [int] NOT NULL CONSTRAINT [DF__test__pollmid__1B5E0D89]  DEFAULT ((0)),
    [test_id] [int] NOT NULL CONSTRAINT [DF__test__test_id__1B5E0D89]  DEFAULT ((0)),
    [lesson_id] [int] NOT NULL CONSTRAINT [DF__test__lesson_id__1B5E0D89]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__test__type__1B5E0D89]  DEFAULT ((0)),
	[threshold] [int] NOT NULL CONSTRAINT [DF__test__threshold__1B5E0D89]  DEFAULT ((75)),
    [adaptive] [int] NOT NULL CONSTRAINT [DF__test__adaptive__1B5E0D89]  DEFAULT ((0)),
 CONSTRAINT [PK_test] PRIMARY KEY CLUSTERED
(
    [tid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tests_questions]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tests_questions](
    [subject_id] [int] NOT NULL CONSTRAINT [DF__tests_questioons__subject_id]  DEFAULT ((0)),
    [test_id] [int] NOT NULL CONSTRAINT [DF__tests_questioons__test_id]  DEFAULT ((0)),
    [kod] [varchar](100) NOT NULL CONSTRAINT [DF__tests_questioons__kod]  DEFAULT (''),
 CONSTRAINT [PK_tests_questions] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [test_id] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[testcount]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[testcount](
    [mid] [int] NOT NULL CONSTRAINT [DF__testcount__mid__23F3538A]  DEFAULT ((0)),
    [tid] [int] NOT NULL CONSTRAINT [DF__testcount__tid__24E777C3]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__testcount__cid__25DB9BFC]  DEFAULT ((0)),
    [qty] [int] NOT NULL CONSTRAINT [DF__testcount__qty__26CFC035]  DEFAULT ((0)),
    [last] [int] NOT NULL CONSTRAINT [DF__testcount__last__27C3E46E]  DEFAULT ((0)),
    [lesson_id] [int] NOT NULL CONSTRAINT [DF__testcount__lesson_id__27C3E46E] DEFAULT ((0)),
 CONSTRAINT [PK_testcount] PRIMARY KEY CLUSTERED
(
    [mid] ASC,
    [tid] ASC,
    [cid] ASC,
    [lesson_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[video]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[video](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [filename] [varchar](255) NULL,
    [created] [int] NOT NULL CONSTRAINT [DF_video_created]  DEFAULT ((0)),
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_video_title]  DEFAULT (''),
    [main_video] [int] NOT NULL CONSTRAINT [DF_video_main_video]  DEFAULT ((0)),
 CONSTRAINT [PK_video] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[updates]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[updates](
    [update_id] [int] NOT NULL CONSTRAINT [DF__updates_update_id__50C5FA01]  DEFAULT ((0)),
    [version] [nvarchar](255) NOT NULL CONSTRAINT [DF__updates_version__50C5FA01]  DEFAULT (''),
    [created] [datetime] NULL CONSTRAINT [DF__updates_created__50C5FA01]  DEFAULT (NULL),
    [updated] [datetime] NULL CONSTRAINT [DF__updates_updated__50C5FA01]  DEFAULT (NULL),
	[created_by] [int] NOT NULL CONSTRAINT [DF__updates_created_by__50C5FA01]  DEFAULT ((0)),
    [organization] [nvarchar](255) NOT NULL CONSTRAINT [DF__updates_organization__50C5FA01]  DEFAULT (''),
    [description] [ntext] NULL,
    [servers] [ntext] NULL,
CONSTRAINT [PK_updates] PRIMARY KEY CLUSTERED
(
    [update_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[user_login_log]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[user_login_log](
    [login] [varchar](255) NULL CONSTRAINT [DF__user_login_log_login__50C5FA01]  DEFAULT (NULL),
    [date] [datetime] NULL CONSTRAINT [DF__user_login_log__date__50C5FA01]  DEFAULT (NULL),
    [event_type] [int] NOT NULL CONSTRAINT [DF__user_login_log__event_type__50C5FA01]  DEFAULT ((0)),
    [status] [int] NOT NULL CONSTRAINT [DF__user_login_log__status__50C5FA01]  DEFAULT ((0)),
    [comments] [varchar](255) NULL,
    [ip] [varchar](255) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[crontask]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[crontask](
    [crontask_id] [varchar](255) NOT NULL CONSTRAINT [DF_crontask_crontask_id]  DEFAULT (''),
    [crontask_runtime] [int] NULL,
    [crontask_endtime] [int] NULL
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[managers]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[managers](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__managers__mid__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_managers] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[sequence_current]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[sequence_current](
    [mid] [int] NOT NULL CONSTRAINT [DF__sequence_current__mid__45544755]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__sequence_current__cid__45544755]  DEFAULT ((0)),
    [current] [varchar](255) NOT NULL CONSTRAINT [DF__sequence_current__current__45544755]  DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF__sequence_current__subject_id]  DEFAULT ((0)),
	[lesson_id] [int] NOT NULL CONSTRAINT [DF__sequence_current__lesson_id]  DEFAULT ((0)),
 CONSTRAINT [PK_sequence_current] PRIMARY KEY CLUSTERED
(
    [mid] ASC,
    [cid] ASC,
    [subject_id] ASC,
    [lesson_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[sequence_history]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[sequence_history](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL,
    [cid] [int] NOT NULL,
    [item] [varchar](255) NOT NULL CONSTRAINT [DF__sequence_history_item__45544755]  DEFAULT (''),
    [date] [datetime] NOT NULL,
    [subject_id] [int] NOT NULL CONSTRAINT [DF_sequence_history_subject_id] DEFAULT ((0)),
	[lesson_id] [int] NOT NULL CONSTRAINT [DF__sequence_history__lesson_id]  DEFAULT ((0)),
 CONSTRAINT [PK_sequence_history] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[developers]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[developers](
    [mid] [int] NOT NULL CONSTRAINT [DF__developers_mid__45544755]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__developers_cid__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_developers] PRIMARY KEY CLUSTERED
(
    [mid] ASC,
    [cid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[methodologist]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[methodologist](
    [mid] [int] NOT NULL CONSTRAINT [DF__methodologist_mid__45544755]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__methodologist_cid__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_methodologist] PRIMARY KEY CLUSTERED
(
    [mid] ASC,
    [cid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[webinar_users]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_users](
    [pointId] [int] NOT NULL CONSTRAINT [DF__webinar_u__point__55DFB4D9]  DEFAULT ((0)),
    [userId] [int] NOT NULL CONSTRAINT [DF__webinar_u__userI__56D3D912]  DEFAULT ((0)),
    [last] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
    [pointId] ASC,
    [userId] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
CREATE TABLE [dbo].[videochat_users](
    [pointId] [int] NOT NULL CONSTRAINT [DF__videoc_u__point__55DFB4D9]  DEFAULT ((0)),
    [userId] [int] NOT NULL CONSTRAINT [DF__videoc_u__userI__56D3D912]  DEFAULT ((0)),
    [last] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
    [pointId] ASC,
    [userId] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[webinar_plan]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[webinar_plan](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [pointId] [int] NOT NULL CONSTRAINT [DF__webinar_p__point__5B988E2F]  DEFAULT ((0)),
    [href] [varchar](255) NOT NULL CONSTRAINT [DF__webinar_pl__href__5C8CB268]  DEFAULT (''),
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_webinar_plan_title]  DEFAULT (''),
    [bid] [int] NOT NULL CONSTRAINT [DF_webinar_plan_bid]  DEFAULT ((0)),
PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[webinar_plan_current]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_plan_current](
    [pointId] [int] NOT NULL CONSTRAINT [DF__webinar_p__point__5F691F13]  DEFAULT ((0)),
    [currentItem] [int] NOT NULL CONSTRAINT [DF__webinar_p__curre__605D434C]  DEFAULT ((0)),
 CONSTRAINT [PK__webinar_plan_cur__5E74FADA] PRIMARY KEY CLUSTERED
(
    [pointId] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[webinar_plan_current]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_files](
    [webinar_id] [int] NOT NULL,
    [file_id] [int] NOT NULL,
    [num] [int] NOT NULL CONSTRAINT [DF_WEBINAR_FILES_NUM] DEFAULT ((0))
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[Courses]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Courses](
    [CID] [int] IDENTITY(1,1) NOT NULL,
    [Title] [varchar](255) NOT NULL CONSTRAINT [DF__Courses__Title__31EC6D26]  DEFAULT (''),
    [Description] [text] NOT NULL CONSTRAINT [DF__Courses__Descrip__77BFCB91]  DEFAULT (''),
    [TypeDes] [int] NOT NULL CONSTRAINT [DF__Courses__TypeDes__32E0915F]  DEFAULT ((0)),
    [CD] [text] NOT NULL CONSTRAINT [DF__Courses__CD__78B3EFCA]  DEFAULT (''),
    [cBegin] [datetime] NOT NULL CONSTRAINT [DF__Courses__cBegin__33D4B598]  DEFAULT ((0)),
    [cEnd] [datetime] NOT NULL CONSTRAINT [DF__Courses__cEnd__34C8D9D1]  DEFAULT ((0)),
    [Fee] [float] NOT NULL CONSTRAINT [DF__Courses__Fee__35BCFE0A]  DEFAULT ((0)),
    [valuta] [int] NOT NULL CONSTRAINT [DF__Courses__valuta__36B12243]  DEFAULT ((0)),
    [Status] [varchar](25) NOT NULL CONSTRAINT [DF__Courses__Status__37A5467C]  DEFAULT (''),
    [createby] [varchar](50) NOT NULL CONSTRAINT [DF__Courses__createb__38996AB5]  DEFAULT (''),
    [createdate] [datetime] NOT NULL CONSTRAINT [DF__Courses__created__398D8EEE]  DEFAULT ((0)),
    [longtime] [int] NOT NULL CONSTRAINT [DF__Courses__longtim__3A81B327]  DEFAULT ((0)),
    [did] [varchar](255) NULL,
    [credits_student] [int] NOT NULL CONSTRAINT [DF__Courses__credits__3C69FB99]  DEFAULT ((0)),
    [credits_teacher] [int] NOT NULL CONSTRAINT [DF__Courses__credits__3D5E1FD2]  DEFAULT ((0)),
    [locked] [int] NOT NULL CONSTRAINT [DF__Courses__locked__3E52440B]  DEFAULT ((0)),
    [chain] [int] NOT NULL CONSTRAINT [DF__Courses__chain__3F466844]  DEFAULT ((0)),
    [subject_id] [int] NOT NULL CONSTRAINT [DF__Courses__subject_id__3F466844]  DEFAULT ((0)),
    [is_poll] [int] NOT NULL CONSTRAINT [DF__Courses__is_poll__403A8C7D]  DEFAULT ((0)),
    [is_module_need_check] [int] NOT NULL CONSTRAINT [DF__Courses__is_module_need_check__403A8C7E]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__Courses__type__403A8C7E]  DEFAULT ((0)),
    [tree] [text] NOT NULL CONSTRAINT [DF__Courses__tree__403A8C7E]  DEFAULT (''),
    [progress] [int] NOT NULL CONSTRAINT [DF__Courses__progress__403A8C7E]  DEFAULT ((0)),
    [sequence] [int] NOT NULL CONSTRAINT [DF_Courses_sequence]  DEFAULT ((0)),
    [provider] [int] NOT NULL CONSTRAINT [DF_Courses_provider]  DEFAULT ((0)),
    [provider_options] [varchar](255) NOT NULL CONSTRAINT [DF_Courses_provider_options]  DEFAULT (''),
    [planDate] [datetime] NULL,
    [developStatus] [varchar](45) NULL,
    [lastUpdateDate] datetime NULL,
    [archiveDate] datetime NULL,
    [services] [int] NOT NULL CONSTRAINT [DF_Courses_services]  DEFAULT ((0)),
    [has_tree] [int] NOT NULL CONSTRAINT [DF_Courses_hastree]  DEFAULT ((0)),
    [new_window] [int] NOT NULL CONSTRAINT [DF_Courses_new_window]  DEFAULT ((0)),
    [emulate] [int] NOT NULL CONSTRAINT [DF_Courses_emulate]  DEFAULT ((0)),
    [format] [int] NOT NULL CONSTRAINT [DF_Courses_format] DEFAULT((0)),
    [author] [int] NOT NULL CONSTRAINT [DF_Courses_author] DEFAULT((0)),
    [emulate_scorm] [int] NOT NULL DEFAULT((0)),
    [extra_navigation] [int] NOT NULL DEFAULT((0)),
    [entry_point] [varchar](255) NULL,
    [activity_id] [varchar](255) NULL,
 CONSTRAINT [PK_Courses] PRIMARY KEY CLUSTERED
(
    [CID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[webinar_history]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[webinar_history](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [pointId] [int] NOT NULL CONSTRAINT [DF__webinar_h__point__7834CCDD]  DEFAULT ((0)),
    [userId] [int] NOT NULL CONSTRAINT [DF__webinar_h__userI__7928F116]  DEFAULT ((0)),
    [action] [varchar](255) NOT NULL CONSTRAINT [DF__webinar_h__actio__7A1D154F]  DEFAULT (''),
    [item] [varchar](255) NOT NULL CONSTRAINT [DF__webinar_hi__item__7B113988]  DEFAULT (''),
    [datetime] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[events]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[events](
    [event_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF__events__event_id__4D94879B]  DEFAULT (''),
    [tool] [varchar](255) NOT NULL CONSTRAINT [DF__events__tool__4D94879B]  DEFAULT (''),
	[scale_id] [int] NOT NULL CONSTRAINT [DF__events_scaleid__4E88ABD4]  DEFAULT ((1)),
	[weight] [int] NOT NULL CONSTRAINT [DF__events_weight__4E88ABD4]  DEFAULT ((5)),
	[external_id] [int] NULL,
 CONSTRAINT [PK_events] PRIMARY KEY CLUSTERED
(
    [event_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


CREATE TABLE [dbo].[webinar_answers](
    [aid] [int] IDENTITY(1,1) NOT NULL,
    [qid] [int] NOT NULL,
    [text] [varchar](255) NULL,
 CONSTRAINT [PK_webinar_answers] PRIMARY KEY CLUSTERED
(
    [aid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_questions](
    [qid] [int] IDENTITY(1,1) NOT NULL,
    [text] [varchar](255) NULL,
    [type] [int] NULL,
    [point_id] [int] NULL,
    [is_voted] [int] NULL,
 CONSTRAINT [PK_webinar_questions] PRIMARY KEY CLUSTERED
(
    [qid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

CREATE UNIQUE NONCLUSTERED INDEX [IX_webinar_questions_text] ON [dbo].[webinar_questions]
(
    [text] ASC,
    [point_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_votes](
    [vid] [int] IDENTITY(1,1) NOT NULL,
    [user_id] [int] NULL,
    [qid] [int] NULL,
    [aid] [int] NULL,
 CONSTRAINT [PK_webinar_votes] PRIMARY KEY CLUSTERED
(
    [vid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_whiteboard](
    [actionId] [int] IDENTITY(1,1) NOT NULL,
    [pointId] [int] NULL,
    [userId] [int] NULL,
    [actionType] [varchar](255) NULL,
    [dateTime] [datetime] NULL,
    [color] [int] NULL,
    [tool] [int] NULL,
    [text] [text] NULL,
    [width] [int] NULL,
    [height] [int] NULL,
 CONSTRAINT [PK_webinar_whiteboard] PRIMARY KEY CLUSTERED
(
    [actionId] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_whiteboard_points](
    [pointId] [int] IDENTITY(1,1) NOT NULL,
    [actionId] [int] NULL,
    [x] [int] NULL,
    [y] [int] NULL,
    [type] [int] NULL,
 CONSTRAINT [PK_webinar_whiteboard_points] PRIMARY KEY CLUSTERED
(
    [pointId] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_records](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [subject_id] [int] NULL,
    [webinar_id] [int] NULL,
    [name] [varchar](255) NULL,
 CONSTRAINT [PK_webinar_records] PRIMARY KEY CLUSTERED
(
    [id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE wiki_articles
(
  id      INT IDENTITY(1,1) NOT NULL,
  created DATETIME,
  title   VARCHAR(255) NOT NULL,
  subject_name VARCHAR(255),
  subject_id INT NOT NULL,
  lesson_id INT NULL,
  changed DATETIME,
  CONSTRAINT wiki_articles_pk PRIMARY KEY (id)
);
GO

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE wiki_archive
(
  id      INT IDENTITY(1,1) NOT NULL,
  article_id    INT NOT NULL,
  created DATETIME,
  author  INT NOT NULL,
  body    TEXT,
  CONSTRAINT wiki_archive_pk PRIMARY KEY (id)
);
GO


GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[claimants](
    [SID] [int] IDENTITY(1,1) NOT NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__claimants__MID__1DE57479]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__claimants__CID__1ED998B2]  DEFAULT ((0)),
	[base_subject] [int] NOT NULL CONSTRAINT [DF__claimants__base_subject__1ED998B2]  DEFAULT ((0)),
    [Teacher] [int] NOT NULL CONSTRAINT [DF__claimants__Teach__1FCDBCEB]  DEFAULT ((0)),
    [created] [datetime] NULL,
	[created_by] [int] NOT NULL CONSTRAINT [DF__claimants__created_by__1DE57479]  DEFAULT ((0)),
    [begin] [datetime] NULL,
    [end] [datetime] NULL,
    [status] [int] NOT NULL CONSTRAINT [DF_claimants_status]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF_claimants_type]  DEFAULT ((0)),
    [mid_external] [varchar](255) NOT NULL CONSTRAINT [DF_claimants_mid_external]  DEFAULT (''),
    [lastname] [varchar](255) NOT NULL CONSTRAINT [DF_claimants_lastname]  DEFAULT (''),
    [firstname] [varchar](255) NOT NULL CONSTRAINT [DF_claimants_firstname]  DEFAULT (''),
    [patronymic] [varchar](255) NOT NULL CONSTRAINT [DF_claimants_patronymic]  DEFAULT (''),
    [comments] [varchar](255) NOT NULL CONSTRAINT [DF_claimants_comments]  DEFAULT (''),
    [dublicate] [bit] NOT NULL CONSTRAINT [DF__claimants__dublicate__1DE57479]  DEFAULT ((0)),
 CONSTRAINT [PK_claimants] PRIMARY KEY CLUSTERED
(
    [SID] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO






SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[classifiers](
    [classifier_id] [int] IDENTITY(1,1) NOT NULL,
    [lft] [int] NOT NULL CONSTRAINT [DF_classifiers_lft]  DEFAULT ((0)),
    [rgt] [int] NOT NULL CONSTRAINT [DF_classifiers_rgt]  DEFAULT ((0)),
    [level] [int] NOT NULL CONSTRAINT [DF_classifiers_level]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF_classifiers_type]  DEFAULT ((0)),
    [name] [varchar](255) NOT NULL CONSTRAINT [DF_classifiers_name]  DEFAULT (''),
 CONSTRAINT [PK_classifiers] PRIMARY KEY CLUSTERED
(
    [classifier_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
CREATE TABLE [dbo].[classifiers_types](
    [type_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF_classifiers_types_name]  DEFAULT (''),
    [link_types] [varchar](255) NOT NULL CONSTRAINT [DF_classifiers_types_link_name]  DEFAULT (''),
 CONSTRAINT [PK_classifiers_types] PRIMARY KEY CLUSTERED
(
    [type_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[classifiers_images](
    [classifier_image_id] [int] IDENTITY(1,1) NOT NULL,
    [item_id] [int] NOT NULL,
    [type] [int] NOT NULL,
 CONSTRAINT [PK_classifiers_images] PRIMARY KEY CLUSTERED
(
    [classifier_image_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[classifiers_links](
    [item_id] [int] NOT NULL,
    [classifier_id] [int] NOT NULL,
    [type] [int] NOT NULL,
 CONSTRAINT [PK_classifiers_links] PRIMARY KEY CLUSTERED
(
    [item_id] ASC,
    [classifier_id] ASC,
    [type] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[comments](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [activity_name] [varchar](255) NOT NULL CONSTRAINT [DF_comments_activity_name]  DEFAULT (''),
    [subject_name] [varchar](255) NOT NULL CONSTRAINT [DF_comments_subject_name]  DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_comments_subject_id]  DEFAULT ((0)),
    [user_id] [int] NOT NULL CONSTRAINT [DF_comments_user_id]  DEFAULT ((0)),
    [item_id] [int] NOT NULL CONSTRAINT [DF_comments_item_id]  DEFAULT ((0)),
    [message] [text] NULL,
    [created] [datetime] NULL,
    [updated] [datetime] NULL,
 CONSTRAINT [PK_comments] PRIMARY KEY CLUSTERED
(
    [id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO











SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[test_abstract](
    [test_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_test_abstract_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_test_abstract_status]  DEFAULT ((0)),
    [description] [text] NULL,
	[keywords] [text] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_test_abstract_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_test_abstract_questions]  DEFAULT ((0)),
    [data] [text] NOT NULL CONSTRAINT [DF__test_abstract_data] DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_test_abstract_subject_id]  DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF_test_abstract_location]  DEFAULT ((0)),
 CONSTRAINT [PK_test_abstract] PRIMARY KEY CLUSTERED
(
    [test_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[test_feedback](
    [test_feedback_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_test_feedback_title]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF_test_feedback_type]  DEFAULT ((0)),
    [text] [text] NULL,
	[parent] [int] NOT NULL CONSTRAINT [DF_test_feedback_parent]  DEFAULT ((0)),
    [treshold_min] [int] NOT NULL CONSTRAINT [DF_test_feedback_treshold_min]  DEFAULT ((0)),
    [treshold_max] [int] NOT NULL CONSTRAINT [DF_test_feedback_treshold_max]  DEFAULT ((0)),
    [test_id] [int] NOT NULL CONSTRAINT [DF_test_feedback_test_id]  DEFAULT ((0)),
    [question_id] [varchar](255) NOT NULL CONSTRAINT [DF_test_feedback_question_id]  DEFAULT (''),
    [answer_id] [varchar](255) NOT NULL CONSTRAINT [DF_test_feedback_answer_id]  DEFAULT (''),
    [show_event] [int] NOT NULL CONSTRAINT [DF_test_feedback_show_event]  DEFAULT ((0)),
    [show_on_values] [text] NULL,
 CONSTRAINT [PK_test_feedback] PRIMARY KEY CLUSTERED
(
    [test_feedback_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exercises](
    [exercise_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [varchar](255) NOT NULL CONSTRAINT [DF_exercises_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_exercises_status]  DEFAULT ((0)),
    [description] [text] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_exercises_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_exercises_questions]  DEFAULT ((0)),
    [data] [text] NOT NULL CONSTRAINT [DF_exercises_data] DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_exercises_subject_id]  DEFAULT ((0)),
 CONSTRAINT [PK_exercises] PRIMARY KEY CLUSTERED
(
    [exercise_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[supervisors](
    [user_id] [int] NOT NULL CONSTRAINT [DF__supervisors_user_id] DEFAULT ((0)),
 CONSTRAINT [PK_supervisors] PRIMARY KEY CLUSTERED
(
    [user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[employee](
    [user_id] [int] NOT NULL CONSTRAINT [DF__employee_user_id] DEFAULT ((0)),
 CONSTRAINT [PK_employee] PRIMARY KEY CLUSTERED
(
    [user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO




SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[resources](
    [resource_id] [int] IDENTITY(1,1) NOT NULL,
    [resource_id_external] [varchar](255) NULL,
    [title] [varchar](255) NULL,
    [url] [varchar](255) NOT NULL CONSTRAINT [DF_resources_url]  DEFAULT (''),
    [volume] [varchar](255) NOT NULL CONSTRAINT [DF_resources_volume]  DEFAULT ('0'),
    [filename] [varchar](255) NOT NULL  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF_resources_type]  DEFAULT ((0)),
    [edit_type] [int] NOT NULL DEFAULT ((0)),
    [filetype] [int] NOT NULL CONSTRAINT [DF_resources_filetype]  DEFAULT ((0)),
    [description] [text] NOT NULL CONSTRAINT [DF_resources_description]  DEFAULT (''),
    [content] [text] NULL CONSTRAINT [DF_resources_content]  DEFAULT (''),
    [created] [datetime] NULL ,
    [updated] [datetime] NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_resources_created_by]  DEFAULT ((0)),
    [services] [int] NOT NULL CONSTRAINT [DF_resources_services]  DEFAULT ((0)),
    [subject] [varchar](50) NOT NULL CONSTRAINT [DF_resources_subject] DEFAULT ('subject'),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_resources_subject_id] DEFAULT ((0)),
    [status] [int] NOT NULL CONSTRAINT [DF_resources_sstatus] DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF_resources_location] DEFAULT ((0)),
    [db_id] [varchar](255) NOT NULL CONSTRAINT [DF_resources_db_id]  DEFAULT (''),
    [test_id] [int] NOT NULL CONSTRAINT [DF_resources_test_id] DEFAULT ((0)),
	[activity_id] [int] NOT NULL CONSTRAINT [DF_resources_activity_id] DEFAULT ((0)),
	[activity_type] [int] NOT NULL CONSTRAINT [DF_resources_activity_type] DEFAULT ((0)),
	[related_resources] [text] NULL CONSTRAINT [DF_resources_related_resources]  DEFAULT (''),
    [parent_id] [int] NOT NULL CONSTRAINT [DF_resources_parent_id] DEFAULT ((0)),
    [parent_revision_id] [int] NOT NULL CONSTRAINT [DF_resources_parent_revision_id] DEFAULT ((0)),
    [storage_id] [int] NOT NULL CONSTRAINT [DF_resources_storage_id] DEFAULT ((0)),
    [external_viewer] [varchar](16) NOT NULL  DEFAULT (''),
 CONSTRAINT [PK_resources] PRIMARY KEY CLUSTERED
(
    [resource_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[resource_revisions](
    [revision_id] [int] IDENTITY(1,1) NOT NULL,
    [resource_id] [int] NOT NULL,
    [url] [varchar](255) NOT NULL CONSTRAINT [DF_revisions_url]  DEFAULT (''),
    [volume] [varchar](255) NOT NULL CONSTRAINT [DF_revisions_volume]  DEFAULT ('0'),
    [filename] [varchar](255) NOT NULL  DEFAULT (''),
    [filetype] [int] NOT NULL CONSTRAINT [DF_revisions_filetype]  DEFAULT ((0)),
    [content] [text] NULL CONSTRAINT [DF_revisions_content]  DEFAULT (''),
    [updated] [datetime] NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_revisions_created_by]  DEFAULT ((0)),
 CONSTRAINT [PK_revision_revisions] PRIMARY KEY CLUSTERED
(
    [revision_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[projects](
    [projid] [int] IDENTITY(1,1) NOT NULL,
    [external_id] [varchar](45) NULL,
    [code] [varchar](255) NULL,
    [name] [varchar](255) NULL,
    [shortname] [varchar](32) NULL,
    [supplier_id] [int] NULL,
    [description] [text] NULL,
    [type] [varchar](45) NULL,
    [reg_type] [varchar](45) NULL,
    [begin] [datetime] NULL,
    [end] [datetime] NULL,
    [longtime] [int] NULL,
    [price] [float] NULL,
	[price_currency] [varchar](25) NULL,
    [plan_users] [int] NULL,
    [services] [int] NOT NULL CONSTRAINT [DF_subjects_services]  DEFAULT ((0)),
    [period] [int] NOT NULL CONSTRAINT [DF_subjects_period] DEFAULT((0)),
    [period_restriction_type] [int] NOT NULL DEFAULT((0)),
	[created] [datetime] NULL,
    [last_updated] [datetime] NULL,
	[access_mode] [int] NOT NULL CONSTRAINT [DF_subjects_access_mode] DEFAULT((0)),
	[access_elements] [int] NULL,
	[mode_free_limit] [int] NULL,
	[auto_done] [int] NOT NULL DEFAULT((0)),
	[base] [int] NOT NULL DEFAULT((0)),
	[base_id] [int] NOT NULL DEFAULT((0)),
	[base_color] [varchar](45) NULL,
	[claimant_process_id] [int] NOT NULL DEFAULT((0)),
	[state] [int] DEFAULT((0)),
	[default_uri] [varchar](255) NULL,
	[scale_id] [int] DEFAULT((0)),
	[auto_mark] [int] DEFAULT((0)),
	[auto_graduate] [int] DEFAULT((0)),
	[formula_id] [int] NULL,
	[threshold] [int] NULL,
	[protocol] [varchar](255) NULL,
    [is_public] [tinyint] NOT NULL DEFAULT((0)),

	-- DEPRECATED!!!
    [begin_planned] [datetime] NULL,
    [end_planned] [datetime] NULL,

 CONSTRAINT [PK_projects] PRIMARY KEY CLUSTERED
(
    [projid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO


CREATE TABLE meetingsID (
  SSID int IDENTITY(1,1) NOT NULL,
  meeting_id int NOT NULL default 0,
  MID int NOT NULL default 0,
  begin_personal datetime DEFAULT NULL,
  end_personal datetime DEFAULT NULL,
  gid int default NULL,
  isgroup int default 0,
  V_STATUS float NOT NULL default -1,
  V_DONE int NOT NULL default 0,
  V_DESCRIPTION varchar(255) NOT NULL default '',
  DESCR text,
  SMSremind int NOT NULL default 0,
  ICQremind int NOT NULL default 0,
  EMAILremind int NOT NULL default 0,
  ISTUDremind int NOT NULL default 0,
  test_corr int NOT NULL default 0,
  test_wrong int NOT NULL default 0,
  test_date datetime NULL,
  test_answers text,
  test_tries int default 0,
  toolParams text,
  comments text,
  chief int NOT NULL default 0,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  launched datetime NULL,
  PRIMARY KEY  (SSID)
);
CREATE INDEX MID  ON meetingsID (MID);
CREATE INDEX meeting_id  ON meetingsID (meeting_id);
CREATE INDEX meeting_id_MID ON meetingsID (meeting_id,MID);


CREATE TABLE meetings (
  meeting_id int IDENTITY(1,1) NOT NULL,
  title varchar(255) NOT NULL default '',
  url text,
  descript text NOT NULL,
  [begin] datetime NOT NULL,
  [end] datetime NOT NULL,
  createID int NOT NULL default 0,
  createDate datetime NULL,
  typeID int NOT NULL default 0,
  vedomost int default 0,
  CID int NOT NULL default 0,
  project_id int default NULL,
  startday int NOT NULL DEFAULT 0,
  stopday int NOT NULL DEFAULT 0,
  timetype int NOT NULL DEFAULT 0,
  isgroup int default 0,
  cond_project_id varchar(255) default -1,
  cond_mark varchar(255) NOT NULL default '-',
  cond_progress varchar(255) NOT NULL default '0',
  cond_avgbal varchar(255) NOT NULL default '0',
  cond_sumbal varchar(255) NOT NULL default '0',
  cond_operation int NOT NULL default 0,
  max_mark int NOT NULL default 0,
  period varchar(255) NOT NULL default '-1',
  rid int NOT NULL default 0,
  moderator int NOT NULL default 0,
  gid int default -1,
  perm int NOT NULL default 0,
  pub int NOT NULL default 0,
  sharepointId int NOT NULL default 0,
  connectId varchar(255) NOT NULL default '',
  recommend int NOT NULL default 0,
  notice int NOT NULL default '0',
  notice_days int NOT NULL default '0',
  [all] int NOT NULL default 0,
  params text,
  activities text,
  [order] int default 0,
  tool varchar(255) NOT NULL default '',
  isfree int NOT NULL default 0,
  section_id int NULL,
  PRIMARY KEY  (meeting_id),
);


CREATE INDEX [begin] ON meetings ([begin]);
CREATE INDEX [end] ON meetings ([end]);
CREATE INDEX typeID  ON meetings (typeID);
CREATE INDEX vedomost  ON meetings (vedomost);
CREATE INDEX project_id  ON meetings (project_id);
CREATE INDEX period  ON meetings (period);
CREATE INDEX rid  ON meetings (rid);
CREATE INDEX gid  ON meetings (gid);


CREATE TABLE [dbo].[meetings_marks_history]  (
	[MID]    	[int] NOT NULL,
	[SSID]  	[int] NOT NULL,
	[mark]   	[int] NOT NULL DEFAULT ((0)),
	[updated]	[datetime] NOT NULL
)
GO

CREATE TABLE projects_marks (
  cid int  NOT NULL DEFAULT '0',
  mid int NOT NULL DEFAULT '0',
  mark varchar(255) NOT NULL DEFAULT '-1',
  alias varchar(255) NOT NULL DEFAULT '',
  confirmed int NOT NULL DEFAULT '0',
  comments varchar(1024) ,
  PRIMARY KEY (cid,mid),
);
CREATE INDEX cid  ON projects_marks (cid);
CREATE INDEX mid  ON projects_marks (mid);

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subjects](
    [subid] [int] IDENTITY(1,1) NOT NULL,
	[is_labor_safety] [int] DEFAULT((0)),
    [external_id] [varchar](45) NULL,
    [direction_id] [int] NULL,
    [code] [varchar](255) NULL,
    [name] [varchar](MAX) NULL,
    [shortname] [varchar](MAX) NULL,
    [supplier_id] [int] NULL,
    [short_description] [varchar](255) NULL,
    [description] [text] NULL,
    [type] [varchar](45) NULL,
    [reg_type] [varchar](45) NULL,
    [begin] [datetime] NULL,
    [end] [datetime] NULL,
    [longtime] [int] NULL,
    [price] [int] NULL,
	[price_currency] [varchar](25) NULL,
    [plan_users] [int] NULL,
    [services] [int] NOT NULL CONSTRAINT [DF_subjects_services]  DEFAULT ((0)),
    [period] [int] NOT NULL CONSTRAINT [DF_subjects_period] DEFAULT((0)),
    [period_restriction_type] [int] NOT NULL DEFAULT((0)),
	[created] [datetime] NULL,
    [last_updated] [datetime] NULL,
	[access_mode] [int] NOT NULL CONSTRAINT [DF_subjects_access_mode] DEFAULT((0)),
	[access_elements] [int] NULL,
	[mode_free_limit] [int] NULL,
	[auto_done] [int] NOT NULL DEFAULT((0)),
	[base] [int] NOT NULL DEFAULT((0)),
	[base_id] [int] NOT NULL DEFAULT((0)),
	[base_color] [varchar](45) NULL,
	[claimant_process_id] [int] NOT NULL DEFAULT((0)),
	[state] [int] DEFAULT((0)),
	[default_uri] [varchar](255) NULL,
	[scale_id] [int] DEFAULT((0)),
	[auto_mark] [int] DEFAULT((0)),
	[auto_graduate] [int] DEFAULT((0)),
	[formula_id] [int] NULL,
	[threshold] [int] NULL,
	[in_slider] [int] NOT NULL DEFAULT((0)),
	[in_banner] [int] DEFAULT((0)),
    [create_from_tc_session] [int] NULL,
	[provider_id] [int] NULL,
	[status] [int] NULL,
	[format] [int] NULL,
	[criterion_id] [int] NULL,
	[criterion_type] [int] NULL,
	[created_by] [int] NULL,
	[category] [int] NULL,
	[city] [int] NULL,
    [primary_type] int NULL,
    [mark_required] int NULL,
    [check_form] int NULL,
	[provider_type] [int] NOT NULL DEFAULT((2)),
    [after_training] int NULL,
    [feedback] int NULL,
    [education_type] int NOT NULL DEFAULT ((2)),
    [rating] float NULL,
    [banner_url] VARCHAR(255) NULL,

	-- DEPRECATED!!!
    [begin_planned] [datetime] NULL,
    [end_planned] [datetime] NULL,

 CONSTRAINT [PK_subjects] PRIMARY KEY CLUSTERED
(
    [subid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO








SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subjects_courses](
    [subject_id] [int] NOT NULL,
    [course_id] [int] NOT NULL,
 CONSTRAINT [PK_subjects_courses] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [course_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subjects_quests](
    [subject_id] [int] NOT NULL,
    [quest_id] [int] NOT NULL,
 CONSTRAINT [PK_subjects_quests] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [quest_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subjects_exercises](
    [subject_id] [int] NOT NULL,
    [exercise_id] [int] NOT NULL,
 CONSTRAINT [PK_subjects_exercises] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [exercise_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subjects_resources](
    [subject_id] [int] NOT NULL,
    [resource_id] [int] NOT NULL,
    [subject] [varchar](50) NOT NULL DEFAULT ('subject'),
 CONSTRAINT [PK_subjects_resources] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [resource_id] ASC,
    [subject] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[subjects_quizzes](
    [subject_id] [int] NOT NULL,
    [quiz_id] [int] NOT NULL,
 CONSTRAINT [PK_subjects_quizzes] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [quiz_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[subjects_tasks](
    [subject_id] [int] NOT NULL,
    [task_id] [int] NOT NULL,
 CONSTRAINT [PK_subjects_tasks] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [task_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[subject_criteria](
  [subject_id] [int] NOT NULL,
  [criterion_id] [int] NOT NULL,
  [criterion_type] [int] NOT NULL,
 CONSTRAINT [PK_subject_criteria] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [criterion_id] ASC,
    [criterion_type] ASC,
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[feedback](
    [feedback_id] [int] IDENTITY(1,1) NOT NULL,
    [subject_id] [int] NOT NULL,
    [user_id] [int] NULL,
    [quest_id] [int] NOT NULL,
    [status] [int] NOT NULL DEFAULT ((0)),
    [date_finished] [datetime] NULL,
    [name] [varchar](255) NOT NULL DEFAULT (''),
    [respondent_type] [tinyint] NOT NULL DEFAULT ((0)),
    [assign_type] [tinyint] NOT NULL DEFAULT ((1)),
    [assign_days] [int] NULL,
    [assign_new] [tinyint] NULL,
    [assign_anonymous] [tinyint] NULL,
    [assign_teacher] [tinyint] NULL,
    [assign_anonymous_hash] [varchar](255) NULL,
 CONSTRAINT [PK_feedback] PRIMARY KEY CLUSTERED
(
    [feedback_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

CREATE TABLE [dbo].[feedback_users](
    [feedback_user_id] [int] IDENTITY(1,1) NOT NULL,
    [user_id] [int] NOT NULL,
    [feedback_id] [int] NOT NULL,
    [subordinate_id] [int] NULL,
    [common_date_end] [int] NULL,
 CONSTRAINT [PK_feedback_users] PRIMARY KEY CLUSTERED
(
    [feedback_user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO


GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[feedback_users](
    [feedback_user_id] [int] UNSIGNED IDENTITY(1,1) NOT NULL,
    [user_id] [int] NOT NULL,
    [feedback_id] [int] NOT NULL,
    [subordinate_id] [int] NULL,

 CONSTRAINT [feedback_users_idx] PRIMARY KEY CLUSTERED
(
    [feedback_user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO




GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[messages](
    [message_id] [int] IDENTITY(1,1) NOT NULL,
    [from] [int] NOT NULL CONSTRAINT [DF_messages_from]  DEFAULT ((0)),
    [to] [int] NOT NULL CONSTRAINT [DF_messages_to]  DEFAULT ((0)),
    [theme] [varchar](255) NULL,
    [subject] [varchar](255) NULL,
    [subject_id] [int] NULL,
    [message] [text] NULL,
    [created] [datetime] NULL,
    [readed] [int] NOT NULL CONSTRAINT [DF_messages_readed]  DEFAULT ((0)),
 CONSTRAINT [PK_messages] PRIMARY KEY CLUSTERED
(
    [message_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO




SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[interface](
    [interface_id] [int] IDENTITY(1,1) NOT NULL,
    [role] [varchar](255) NOT NULL CONSTRAINT [DF_interface_role]  DEFAULT (''),
    [user_id] [int] NOT NULL CONSTRAINT [DF_interface_user_id]  DEFAULT ((0)),
    [block] [varchar](255) NOT NULL CONSTRAINT [DF_interface_block]  DEFAULT (''),
    [necessity] [int] NULL CONSTRAINT [DF_interface_nessity]  DEFAULT ((0)),
    [x] [int] NOT NULL CONSTRAINT [DF_interface_x]  DEFAULT ((1)),
    [y] [int] NOT NULL CONSTRAINT [DF_interface_y]  DEFAULT ((1)),
    [width] [int] NOT NULL DEFAULT ((100)),
    [param_id] [varchar](255) NOT NULL DEFAULT (''),
 CONSTRAINT [PK_interface] PRIMARY KEY CLUSTERED
(
    [interface_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO




SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[task_conversations](
	[conversation_id] [int] IDENTITY(1,1) NOT NULL,
	[lesson_id] [int] NOT NULL,
	[user_id] [int] NOT NULL,
	[teacher_id] [int] NOT NULL,
	[type] [int] NOT NULL,
	[variant_id] [int] NULL,
	[message] [text] NULL,
	[date] [datetime] NULL,
 CONSTRAINT [INTERVIEW_ID] PRIMARY KEY CLUSTERED
(
	[conversation_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


CREATE TABLE [dbo].[captcha](
    [login] [varchar](255) NOT NULL,
    [attempts] [int] NOT NULL CONSTRAINT [DF_captcha_attempts]  DEFAULT ((0)),
    [updated] [datetime] NOT NULL
) ON [PRIMARY]


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat_channels]
(
  [id]         [INT] IDENTITY(1,1) NOT NULL,
  [subject_name] [VARCHAR](255),
  [subject_id] [INT] NOT NULL,
  [lesson_id] [INT] NULL,
  [name]      [VARCHAR](255),
  [start_date]    [DATETIME],
  [end_date]    [DATETIME],
  [show_history] [bit] NOT NULL DEFAULT ((1)),
  [start_time] [INT],
  [end_time] [INT],
  [is_general] [bit] NOT NULL DEFAULT ((0)),
  CONSTRAINT chat_channels_pk PRIMARY KEY (id)
)
GO





SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat_history]
(
  [id]         [INT] IDENTITY(1,1) NOT NULL,
  [channel_id] [INT] NOT NULL,
  [sender] [INT] NOT NULL,
  [receiver] [INT] NULL,
  [message]       [TEXT] NOT NULL,
  [created]    [DATETIME] NOT NULL
  CONSTRAINT chat_history_pk PRIMARY KEY (id)
)
GO



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat_ref_users]
(
  [channel_id] [INT] NOT NULL,
  [user_id] [INT] NOT NULL,
  CONSTRAINT chat_ref_users_pk PRIMARY KEY (channel_id,user_id)
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[storage]
(
  [id]         [INT] IDENTITY(1,1) NOT NULL,
  [parent_id] [INT] NULL,
  [hash]      [VARCHAR](4000),
  [phash]      [VARCHAR](4000),
  [subject_name] [VARCHAR](255),
  [subject_id] [INT] NOT NULL,
  [name]      [VARCHAR](255),
  [alias]      [VARCHAR](255),
  [is_file] [bit] NOT NULL,
  [description]      [VARCHAR](255),
  [user_id] [INT] NULL,
  [created]    [DATETIME] NULL,
  [changed]    [DATETIME] NULL,
  CONSTRAINT storage_pk PRIMARY KEY (id)
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[storage_filesystem]
(
    [id]         [INT] IDENTITY(1,1) NOT NULL,
    [parent_id] [INT] NULL,
    [subject_name] [VARCHAR](255),
    [subject_id] [INT] NOT NULL,
    [name]      [VARCHAR](255),
    [alias]      [VARCHAR](255),
    [is_file] [bit] NOT NULL,
    [description]      [VARCHAR](255),
    [user_id] [INT] NULL,
    [created]    [DATETIME] NULL,
    [changed]    [DATETIME] NULL,
    CONSTRAINT storage_filesystem_pk PRIMARY KEY (id)
)
GO

CREATE TABLE [dbo].[interesting_facts] (
  [interesting_facts_id] int IDENTITY(1, 1) NOT NULL,
  [title] text NULL,
  [text] text NULL,
  [status] int DEFAULT 0 NULL,
  CONSTRAINT [interesing_facts_pk] PRIMARY KEY CLUSTERED ([interesting_facts_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO



CREATE TABLE [dbo].[session_guest] (
  [session_guest_id] int IDENTITY(1, 1) NOT NULL,
  [start] datetime NULL,
  [stop] datetime NULL,
  PRIMARY KEY CLUSTERED ([session_guest_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[sections] (
  [section_id] int IDENTITY(1, 1) NOT NULL,
  [subject_id] [INT] NOT NULL DEFAULT '0',
  [project_id] [INT] NOT NULL DEFAULT '0',
  [name] [VARCHAR](255),
  [order] [INT] NULL,
  PRIMARY KEY CLUSTERED ([section_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[reports_roles](
    [role] [varchar](100) NOT NULL CONSTRAINT [DF__reports_roles__role__5629CD9C]  DEFAULT (''),
    [report_id] [int] NOT NULL CONSTRAINT [DF__reports_roles__report_id__571DF1D5]  DEFAULT ((0)),
 CONSTRAINT [PK_reports_roles] PRIMARY KEY CLUSTERED
(
    [role] ASC,
    [report_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[support_requests](
        [support_request_id] [int] IDENTITY(1,1) NOT NULL,
        [date_] [datetime] NULL,
        [theme] [varchar](255) NULL,
        [status] [int] NULL,
        [problem_description] [varchar](max) NULL,
        [wanted_result] [varchar](max) NULL,
        [user_id] [int] NULL,
        [url] [varchar](255) NULL,
        [file_id] [int] NULL,
 CONSTRAINT [PK_support_requests] PRIMARY KEY CLUSTERED 
(
	[support_request_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[likes](
	[like_id] INT IDENTITY(1,1) NOT NULL,
	[item_type] INT NOT NULL,
	[item_id] INT NOT NULL,
	[count_like] INT NOT NULL DEFAULT '0',
	[count_dislike] INT NOT NULL DEFAULT '0',
CONSTRAINT [PK_likes] PRIMARY KEY CLUSTERED
(
    [like_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[like_user](
	[like_user_id] INT IDENTITY(1,1) NOT NULL ,
	[item_type] INT NOT NULL,
	[item_id] INT NOT NULL,
	[user_id] INT NOT NULL,
	[value] INT NOT NULL,
	[date] DATETIME NOT NULL,
CONSTRAINT [PK_like_user] PRIMARY KEY CLUSTERED
(
    [like_user_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO



CREATE TABLE [dbo].[questionnaires] (
  [quest_id] [int] IDENTITY(1,1) NOT NULL,
  [type] VARCHAR(16),
  [status] int,
  [name] VARCHAR(255),
  [description] text,
  [subject_id] int NOT NULL DEFAULT 0,
  [scale_id] int NOT NULL DEFAULT 0,
  [profile_id] int,
  [creator_role] VARCHAR(255) NOT NULL default(''),
  [displaycomment] tinyint default NULL,
  PRIMARY KEY CLUSTERED([quest_id])
) ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/
CREATE TABLE [dbo].[quest_clusters] (
  [cluster_id] [int] IDENTITY(1,1) NOT NULL,
  [quest_id] int,
  [name] varchar(255),
  PRIMARY KEY CLUSTERED([cluster_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_questions] (
  [question_id] [int] IDENTITY(1,1) NOT NULL,
  [cluster_id] int,
  [type] VARCHAR(16),
  [question] text,
  [shorttext] VARCHAR(255),
  [mode_scoring] int,
  [show_free_variant] int,
  [justification] varchar(MAX) NULL,
  PRIMARY KEY CLUSTERED([question_id])

) ON [PRIMARY]
GO

CREATE TABLE [dbo].[schedule_log] (
  [id] [int] IDENTITY(1,1) NOT NULL,
  [lesson_id] int,
  [user_id] int,
  [date_start] datetime,
  CONSTRAINT schedule_log_pk PRIMARY KEY (id)
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_question_quests] (
  [question_id] [int] NOT NULL,
  [quest_id] int NOT NULL,
  [cluster_id] int NULL,
  PRIMARY KEY CLUSTERED([question_id], [quest_id])

) ON [PRIMARY]
GO


CREATE TABLE [dbo].[quest_question_variants] (
  [question_variant_id] [int] IDENTITY(1,1) NOT NULL,
  [question_id] int,
  [variant] text,
  [shorttext] varchar(255),
  [file_id] int,
  [is_correct] int,
  [category_id] varchar(1024),
  [weight] float,
  [data] varchar(MAX) NULL,
  PRIMARY KEY CLUSTERED([question_variant_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_attempts] (
  [attempt_id] [int] IDENTITY(1,1) NOT NULL,
  [user_id] int,
  [quest_id] int,
  [type] varchar(16),
  [context_event_id] int,
  [context_type] int,
  [date_begin] datetime,
  [date_end] datetime,
  [status] int,
  [score_weighted] float,
  [score_raw] int,
  [score_sum] [float] NULL,
  [duration] int,
  [is_resultative] int,
  PRIMARY KEY CLUSTERED([attempt_id])
) ON [PRIMARY]
GO


CREATE TABLE [dbo].[quest_attempt_clusters] (
  [attempt_cluster_id] [int] IDENTITY(1,1) NOT NULL,
  [quest_attempt_id] int,
  [cluster_id] int,
  [score_percented] float,
  PRIMARY KEY CLUSTERED([attempt_cluster_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_categories] (
  [category_id] [int] IDENTITY(1,1) NOT NULL,
  [quest_id] int,
  [name] varchar(255),
  [description] text,
  [formula] text,
  PRIMARY KEY CLUSTERED([category_id])
) ON [PRIMARY]
GO
CREATE TABLE [dbo].[quest_category_results] (
  [category_result_id] [int] IDENTITY(1,1) NOT NULL,
  [attempt_id] int,
  [category_id] int,
  [score_raw] int,
  [result] text,
  PRIMARY KEY CLUSTERED([category_result_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_question_results] (
  [question_result_id] [int] IDENTITY(1,1) NOT NULL,
  [attempt_id] int,
  [question_id] int,
  [variant] TEXT,
  [free_variant] varchar(4000),
  [is_correct] int,
  [score_weighted] float,
  [score_raw] int,
  [category_id] int,
  [show_feedback] int,
  [score_min] [float] NULL,
  [score_max] [float] NULL,
  [comment] TEXT,
  PRIMARY KEY CLUSTERED([question_result_id])
) ON [PRIMARY]
CREATE TABLE [dbo].[quest_category_results] (
  [category_result_id] [int] IDENTITY(1,1) NOT NULL,
  [attempt_id] int,
  [category_id] int,
  [score_raw] int,
  [result] text,
  PRIMARY KEY CLUSTERED([category_result_id])
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[quest_settings] (
  [quest_id] int NOT NULL,
  [scope_type] tinyint default 0,
  [scope_id] int default 0,
  [info] text,
  [cluster_limits] text,
  [comments] text,
  [mode_selection] tinyint default NULL,
  [mode_selection_questions] tinyint default NULL,
  [mode_selection_all_shuffle] tinyint default NULL,
  [mode_passing] tinyint default NULL,
  [mode_display] tinyint default NULL,
  [mode_display_clusters] tinyint default NULL,
  [mode_display_questions] tinyint default NULL,
  [show_result] tinyint default NULL,
  [show_log] tinyint default NULL,
  [limit_time] tinyint default NULL,
  [limit_attempts] tinyint default NULL,
  [limit_clean] tinyint default NULL,
  [mode_test_page] int NOT NULL DEFAULT 0,
  [mode_self_test] tinyint default NULL,
  PRIMARY KEY CLUSTERED([quest_id], [scope_type], [scope_id])
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
CREATE TABLE [dbo].[quest_question_results] (
  [question_result_id] [int] IDENTITY(1,1) NOT NULL,
  [attempt_id] int,
  [question_id] int,
  [variant] TEXT,
  [free_variant] TEXT,
  [is_correct] int,
  [score_weighted] float,
  [score_raw] int,
  [category_id] int,
  [score_min] [float] NULL,
  [score_max] [float] NULL,
  [comment] TEXT,
  PRIMARY KEY CLUSTERED([question_result_id])
) ON [PRIMARY]
GO


CREATE INDEX [at_profile_kpis_profile_id] ON [at_profile_kpis] ([profile_id]);
CREATE INDEX [at_profile_kpis_kpi_id] ON [at_profile_kpis] ([kpi_id]);
GO

CREATE TABLE [dbo].[user_additional_fields] (
	[user_id] int NOT NULL default 0,
	[field_id] int NOT NULL default 0,
	[value] text NOT NULL,
  PRIMARY KEY CLUSTERED([user_id], [field_id])
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


IF OBJECT_ID('dbo.es_events', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_events];
go

create table [dbo].[es_events] (
    [event_id] int not null identity primary key,
    [event_type_id] int not null,
    [event_trigger_id] int not null,
    [event_group_id] int not null default(0),
    [description] text not null default(''),
    [create_time] float(53) not null
);
go

IF OBJECT_ID('dbo.es_event_users', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_users];
go

create table [dbo].[es_event_users] (
    [event_id] int not null,
    [user_id] int not null,
    [views] int not null default 0,
    primary key (event_id,user_id)
);
go

IF OBJECT_ID('dbo.es_event_group_types', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_group_types];
go
create table [dbo].[es_event_group_types] (
    event_group_type_id int not null,
    name varchar(255) not null,
    PRIMARY KEY (event_group_type_id)
);
go

IF OBJECT_ID('dbo.es_event_types', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_types];
go
create table [dbo].[es_event_types] (
    event_type_id int not null identity primary key,
    name varchar(255) not null,
    event_group_type_id int not null
);
go

IF OBJECT_ID('dbo.es_notify_types', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_notify_types];
go
create table [dbo].[es_notify_types] (
    notify_type_id int not null,
    name varchar(255) not null,
    PRIMARY KEY (notify_type_id)
);
go

IF OBJECT_ID('dbo.es_user_notifies', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_user_notifies];
go
create table [dbo].[es_user_notifies] (
    user_id int not null,
    notify_type_id int not null,
    event_type_id int not null,
    is_active tinyint not null default 0,
    PRIMARY KEY (user_id, notify_type_id, event_type_id)
);
go

IF OBJECT_ID('dbo.es_event_groups', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_groups];
go
create table [dbo].[es_event_groups] (
    event_group_id int not null identity primary key,
    trigger_instance_id int not null,
    type varchar(255) not null,
    data text not null,
    CONSTRAINT group_name UNIQUE(trigger_instance_id,type)
);
go

IF OBJECT_ID('dbo.kbase_assessment', 'U') IS NOT NULL
      DROP TABLE [dbo].[kbase_assessment];
go
create table [dbo].[kbase_assessment] (
    [id] int not null identity,
    [type] int NOT NULL default ((0)),
    [resource_id] int NOT NULL,
    [MID] int NOT NULL default ((0)),
    [assessment] int NOT NULL default ((0)),
    PRIMARY KEY (id)
);
go



SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_applications] (
  [application_id] int IDENTITY(1, 1) NOT NULL,
  [session_id] int NULL,
  [session_quarter_id] int NULL,
  [session_department_id] int NULL,
  [department_application_id] int NULL,
  [department_id] int NULL,
  [user_id] int NULL,
  [position_id] int NULL,
  [provider_id] int NULL,
  [subject_id] int NULL,
  [period] varchar(16) NULL,
	[criterion_id] int NULL,
	[category] int NULL,
	[created] datetime NULL,
	[expire] date NULL,
	[primary_type] int NULL,
	[criterion_type] int NULL,
	[status] int NULL,
	[department_goal] varchar(255) null,
	[education_goal] varchar(255) null,
  [cost_item] int NULL,
  [price] int NULL,
  [price_employee] int NULL,
	[event_name] varchar(255) null,
	[initiator] int NOT NULL DEFAULT 0,
	[payment_type] int NOT NULL DEFAULT 0,
	[payment_percent] int NOT NULL DEFAULT 0,

  CONSTRAINT [PK_tc_applications] PRIMARY KEY CLUSTERED
  (
    [application_id] ASC
  ) ON [PRIMARY]
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_applications_impersonal] (
  [application_impersonal_id] int IDENTITY(1, 1) NOT NULL,
  [session_id] int NULL,
  [session_quarter_id] int NULL,
  [session_department_id] int NULL,
  [department_application_id] int NULL,
  [department_id] int NULL,
  [provider_id] int NULL,
  [subject_id] int NULL,
  [period] varchar(16) NULL,
  [criterion_id] int NULL,
  [category] int NULL,
  [created] datetime NULL,
  [expire] date NULL,
  [primary_type] int NULL,
  [criterion_type] int NULL,
  [status] int NULL,
  [cost_item] int NULL,
  [price] int NULL,
  [quantity] int NULL,
  [event_name] varchar(255) null,
  CONSTRAINT [PK_tc_applications_impersonal] PRIMARY KEY CLUSTERED
  (
    [application_impersonal_id] ASC
  ) ON [PRIMARY]
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_provider_contacts] (
  [contact_id] int IDENTITY(1, 1) NOT NULL,
  [provider_id] int NULL,
  [name] varchar(255) NULL,
  [position] varchar(64) NULL,
  [phone] varchar(32) NULL,
  [email] varchar(32) NULL
)
ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_provider_teachers] (
  [teacher_id] int IDENTITY(1, 1) NOT NULL,
  [provider_id] int NULL,
  [name] varchar(255) NULL,
  [description] varchar(2048) NULL,
  [contacts] varchar(2048) NULL,
  [created] [datetime] NULL,
  [created_by] [int] NULL,
  [user_id] [int] NULL,
  CONSTRAINT [PK_tc_provider_teachers] PRIMARY KEY CLUSTERED
  (
    [teacher_id] ASC
  ) ON [PRIMARY]
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_provider_teachers2subjects] (
  [teacher_id] int NOT NULL,
  [provider_id] int NULL,
  [subject_id] int NOT NULL,
  CONSTRAINT [PK_tc_provider_teachers2subjects] PRIMARY KEY CLUSTERED
  (
    [teacher_id] ASC,
    [subject_id] ASC
  ) ON [PRIMARY]
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_provider_scmanagers] (
  [user_id] int NOT NULL,
  [provider_id] int NOT NULL,
  CONSTRAINT [PK_tc_provider_scmanagers] PRIMARY KEY CLUSTERED
  (
    [user_id] ASC,
    [provider_id] ASC
  ) ON [PRIMARY]
)
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_providers] (
  [provider_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [description] text NULL,
  [status] tinyint NULL,
  [type] tinyint NOT NULL DEFAULT 0,
  [address_legal] varchar(1000) NULL,
  [address_postal] varchar(1000) NULL,
  [inn] varchar(32) NULL,
  [kpp] varchar(32) NULL,
  [bik] varchar(32) NULL,
  [subscriber_fio] varchar(255) NULL,
  [subscriber_position] varchar(255) NULL,
  [subscriber_reason] varchar(255) NULL,
  [account] varchar(255) NULL,
  [account_corr] varchar(255) NULL,
  [created] [datetime] NULL,
  [created_by] [int] NULL,
  [create_from_tc_session] [int] NULL,
  [department_id] [int] NOT NULL DEFAULT 0,
  [dzo_id] [int] NOT NULL DEFAULT 0,
	[licence] VARCHAR(255) NULL DEFAULT NULL,
	[registration] VARCHAR(255) NULL DEFAULT NULL,
	[pass_by] [int] NOT NULL DEFAULT 0,
	[prefix_id] [int] NOT NULL DEFAULT 0,
	[information] text NULL,

  CONSTRAINT [PK_tc_providers] PRIMARY KEY CLUSTERED
(
    [provider_id] ASC
) ON [PRIMARY]
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[tc_provider_files](
	[provider_id] [int] NOT NULL,
	[file_id] [int] NOT NULL,
 CONSTRAINT [PK_tc_provider_files] PRIMARY KEY CLUSTERED
(
	[provider_id] ASC,
	[file_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO


CREATE TABLE [dbo].[tc_provider_rooms] (
	[room_id] [INT] IDENTITY(1, 1) NOT NULL,
	[provider_id] [INT] NOT NULL,
	[name] VARCHAR(255) NULL DEFAULT NULL,
	[type] [TINYINT] NULL DEFAULT NULL,
	[places] [INT] NOT NULL DEFAULT 0,
	[description] [TEXT] NULL DEFAULT NULL,
  [created] [datetime] NULL,
  [created_by] [int] NULL,

  CONSTRAINT [PK_tc_provider_rooms] PRIMARY KEY CLUSTERED
(
    [room_id] ASC
) ON [PRIMARY]
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_session_departments] (
  [session_department_id] int IDENTITY(1, 1) NOT NULL,
  [department_id] int NULL,
  [session_id] int NULL,
  [session_quarter_id] int NULL,
  CONSTRAINT [PK_tc_session_departments] PRIMARY KEY CLUSTERED
(
    [session_department_id] ASC
) ON [PRIMARY]
)
ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_department_applications] (
    [department_application_id] int NOT NULL IDENTITY(1,1) ,
    [department_id] int NOT NULL DEFAULT ((0)),
    [session_department_id] int NOT NULL DEFAULT ((0)),
    [session_id] int NOT NULL DEFAULT ((0)),
    [subject_id] int NOT NULL DEFAULT ((0)),
    [profile_id] int NOT NULL DEFAULT ((0)),
    [is_offsite] int NOT NULL DEFAULT ((0)),
    [city_id] int NOT NULL DEFAULT ((0)),
    [category] int NOT NULL DEFAULT ((0)),
    [study_month] date NULL,
    [session_quarter_id] int NOT NULL,
PRIMARY KEY ([department_application_id])
)

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_sessions] (
  [session_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [cycle_id] int NULL,
  [date_begin] date NULL,
  [date_end] date NULL,
  [norm] int NULL,
  [status] int NOT NULL DEFAULT ((0)),
  [type] int NOT NULL DEFAULT ((0)),
  [checked_items] text NOT NULL DEFAULT (''),
  [provider_id] int DEFAULT NULL,
  [responsible_id] int DEFAULT NULL
  CONSTRAINT [PK_tc_sessions] PRIMARY KEY CLUSTERED
(
    [session_id] ASC
)
)
ON [PRIMARY]
GO


GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_sessions_quarter] (
  [session_quarter_id] int IDENTITY(1, 1) NOT NULL,
  [session_id] int NOT NULL,
  [name] varchar(255) NULL,
  [cycle_id] int NULL,
  [date_begin] date NULL,
  [date_end] date NULL,
  [norm] int NULL,
  [status] int NOT NULL DEFAULT ((0)),
  [type] int NOT NULL DEFAULT ((0)),
  [checked_items] text NOT NULL DEFAULT (''),
  [provider_id] int DEFAULT NULL
  CONSTRAINT [PK_tc_sessions_quarter] PRIMARY KEY CLUSTERED
(
    [session_quarter_id] ASC
)
)
ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_document] (
  [document_id] int IDENTITY(1, 1) NOT NULL,
  [name] varchar(255) NULL,
  [add_date] datetime NULL,
  [subject_id] int NOT NULL DEFAULT ((0)),
  [type] int NOT NULL DEFAULT ((0)),
  [filename] varchar(255) NULL,
  CONSTRAINT [PK_tc_document] PRIMARY KEY CLUSTERED
(
    [document_id] ASC
)
)
ON [PRIMARY]
GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_feedbacks] (
  [subject_id] int NOT NULL,
  [user_id] int NOT NULL,
  [mark] int NULL,
  [text]  varchar(2048) NULL,
  [date] datetime NULL,
  [mark_goal] int NULL,
  [mark_goal2] int NULL,
  [longtime] int NULL,
  [mark_usefull] int NULL,
  [mark_motivation] int NULL,
  [mark_course] int NULL,
  [mark_teacher] int NULL,
  [mark_papers] int NULL,
  [mark_organization] int NULL,
  [recomend] int NULL,
  [mark_final] int NULL,
  [text_goal]  varchar(1024) NULL,
  [text_usefull]  varchar(1024) NULL,
  [text_not_usefull]  varchar(1024) NULL,
  CONSTRAINT [PK_tc_feedbacks] PRIMARY KEY CLUSTERED
  (
    [user_id] ASC,
    [subject_id] ASC
  ) ON [PRIMARY]
)
ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_corporate_learning] (
    [corporate_learning_id] int NOT NULL IDENTITY(1,1) ,
    [name] varchar(255) NULL ,
    [month] datetime NULL ,
    [cycle_id] int NULL ,
    [cost_for_organizer] varchar(255) NULL ,
    [organizer_id] int NULL ,
    [manager_name] varchar(255) NULL ,
    [people_count] varchar(255) NULL ,
    [meeting_type] int NULL
PRIMARY KEY ([corporate_learning_id])
)

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_corporate_learning_participant] (
    [participant_id] int NOT NULL ,
    [corporate_learning_id] int NOT NULL ,
    [cost] int NULL ,
PRIMARY KEY ([participant_id], [corporate_learning_id])
)

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_providers_subjects](
    [provider_subject_id] [int] IDENTITY(1,1) NOT NULL,
    [subject_id] [int] NOT NULL,
    [provider_id] [int] NOT NULL,
 CONSTRAINT [PK_tc_providers_subjects] PRIMARY KEY CLUSTERED
(
    [provider_subject_id]
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tc_prefixes](
    [prefix_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NULL,
    [counter] [int] NOT NULL DEFAULT ((1)),
    [prefix_type] [int] NOT NULL DEFAULT ((1)),
 CONSTRAINT [PK_tc_prefixes] PRIMARY KEY CLUSTERED
(
    [prefix_id]
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


CREATE TABLE [dbo].[mail_queue](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [subject] [varchar](1024) NOT NULL DEFAULT (''),
    [recipient] [varchar](255) NOT NULL DEFAULT (''),
    [body] [TEXT] NOT NULL DEFAULT (''),
    [created] [datetime],
    [data] [TEXT] NOT NULL DEFAULT (''),
    [sended] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_MQ] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO



SET ANSI_NULLS ON
go
set quoted_identifier on
go
create function [dbo].[ranker] (@evGroupId int, @group int, @rank int)
returns int
as
begin
return case when @evGroupId = @group then @rank+1 else 1 end
end
go



CREATE TABLE [dbo].[eclass] (
  [id] INT NOT NULL IDENTITY,
  [lesson_id] INT NULL,
  [synced] INT NOT NULL default ((0)),
  [sync_date] DATETIME,
  [title] [VARCHAR](255),
  [subject_id] INT NULL,
  PRIMARY KEY ([webinar_id])
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/

/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
create FUNCTION [dbo].SUBSTRING_INDEX
(
@BaseString varchar(4000),
@caracter varchar(4000),
@pos int
) RETURNS varchar(4000)

AS
/* ****************************************************
Description:
EQuivalent a mysql substring_index
Created by Omar Rodriguez Tineo
**************************************************** */
BEGIN

/*
DECLARE @pos INT
Declare @BaseString varchar(255)
Declare @caracter varchar(255)
*/
Declare @indice tinyint
Declare @pos2 int
Declare @lastPos2 int
Declare @length int
Declare @result varchar(4000)

set @result = @BaseString
set @pos2= 1

set @length = LEN(@caracter)
if @pos <= 1
begin
   set @length = 0
end

set @lastPos2 = 1
set @indice = 0

while @indice < ABS(@pos)
begin

set @lastPos2 = @pos2
set @pos2 = CHARINDEX(@caracter,@BaseString,@pos2+1)

set @indice = @indice +1



if @indice = ABS(@pos)
begin
if (@pos2-1) <= 0
begin
set @result= substring(left(@BaseString,1), @lastPos2 + @length, 255)
break
end
else
begin
set @result= substring(left(@BaseString,(@pos2-1)), @lastPos2 + @length, 255)
break
end
end
else
continue

end

RETURN @result
END
GO
/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/

/**/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
create FUNCTION [dbo].LPAD
(
@str varchar(4000),
@length int,
@padstr varchar(1)
) RETURNS varchar(4000)
AS
BEGIN
RETURN replicate(@padstr, @length - len(@str)) + @str
END
GO
/**/

/**/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
create FUNCTION [dbo].RPAD
(
@str varchar(4000),
@length int,
@padstr varchar(1)
) RETURNS varchar(4000)
AS
BEGIN
RETURN @str + replicate(@padstr, @length - len(@str))
END
GO
/**/

CREATE INDEX ADMINS_MID ON ADMINS (MID);
CREATE INDEX BLOG_CREATED_BY ON BLOG (CREATED_BY);
CREATE INDEX BLOG_SUBJECT_ID ON BLOG (SUBJECT_ID);
CREATE INDEX CAPTCHA_ATTEMPTS ON CAPTCHA (ATTEMPTS);
CREATE INDEX CERTIFICATES_SUBJECT_ID ON CERTIFICATES (SUBJECT_ID);
CREATE INDEX CERTIFICATES_USER_ID ON CERTIFICATES (USER_ID);
CREATE INDEX CHAT_CHANNELS_END_TIME ON CHAT_CHANNELS (END_TIME);
CREATE INDEX CHAT_CHANNELS_IS_GENERAL ON CHAT_CHANNELS (IS_GENERAL);
CREATE INDEX CHAT_CHANNELS_LESSON_ID ON CHAT_CHANNELS (LESSON_ID);
CREATE INDEX CHAT_CHANNELS_SHOW_HISTORY ON CHAT_CHANNELS (SHOW_HISTORY);
CREATE INDEX CHAT_CHANNELS_START_TIME ON CHAT_CHANNELS (START_TIME);
CREATE INDEX CHAT_CHANNELS_SUBJECT_ID ON CHAT_CHANNELS (SUBJECT_ID);
CREATE INDEX CHAT_HISTORY_CHANNEL_ID ON CHAT_HISTORY (CHANNEL_ID);
CREATE INDEX CHAT_HISTORY_RECEIVER ON CHAT_HISTORY (RECEIVER);
CREATE INDEX CHAT_HISTORY_SENDER ON CHAT_HISTORY (SENDER);
CREATE INDEX CHAT_HISTORY_CREATED ON CHAT_HISTORY (CREATED);
CREATE INDEX CHAT_REF_USERS_CHANNEL_ID ON CHAT_REF_USERS (CHANNEL_ID);
CREATE INDEX CHAT_REF_USERS_USER_ID ON CHAT_REF_USERS (USER_ID);
CREATE INDEX CLAIMANTS_BASE_SUBJECT ON CLAIMANTS (BASE_SUBJECT);
CREATE INDEX CLAIMANTS_CID ON CLAIMANTS (CID);
CREATE INDEX CLAIMANTS_CREATED_BY ON CLAIMANTS (CREATED_BY);
CREATE INDEX CLAIMANTS_DUBLICATE ON CLAIMANTS (DUBLICATE);
CREATE INDEX CLAIMANTS_MID ON CLAIMANTS (MID);
CREATE INDEX CLAIMANTS_STATUS ON CLAIMANTS (STATUS);
CREATE INDEX CLAIMANTS_TEACHER ON CLAIMANTS (TEACHER);
CREATE INDEX CLAIMANTS_TYPE_ ON CLAIMANTS (TYPE);
CREATE INDEX CLAIMANTS_CID_MID_STATUS ON CLAIMANTS (CID, MID, STATUS);
CREATE INDEX CLASSIFIERS_IMAGES_ITEM_ID ON CLASSIFIERS_IMAGES (ITEM_ID);
CREATE INDEX CLASSIFIERS_IMAGES_TYPE_ ON CLASSIFIERS_IMAGES (TYPE);
CREATE INDEX CLASSIFIERS_LEVEL_ ON CLASSIFIERS (LEVEL);
CREATE INDEX CLASSIFIERS_LFT ON CLASSIFIERS (LFT);
CREATE INDEX CL_CLASSIFIER_ID ON CLASSIFIERS_LINKS (CLASSIFIER_ID);
CREATE INDEX CLASSIFIERS_LINKS_ITEM_ID ON CLASSIFIERS_LINKS (ITEM_ID);
CREATE INDEX CLASSIFIERS_LINKS_TYPE_ ON CLASSIFIERS_LINKS (TYPE);
CREATE INDEX CLASSIFIERS_LINKS_TYPE_ITEM_ID ON CLASSIFIERS_LINKS (TYPE, ITEM_ID);
CREATE INDEX CLASSIFIERS_RGT ON CLASSIFIERS (RGT);
CREATE INDEX CLASSIFIERS_TYPE_ ON CLASSIFIERS (TYPE);
CREATE INDEX COMMENTS_ITEM_ID ON COMMENTS (ITEM_ID);
CREATE INDEX COMMENTS_SUBJECT_ID ON COMMENTS (SUBJECT_ID);
CREATE INDEX COMMENTS_USER_ID ON COMMENTS (USER_ID);
CREATE INDEX COURSES_CHAIN ON COURSES (CHAIN);
CREATE INDEX COURSES_CREDITS_STUDENT ON COURSES (CREDITS_STUDENT);
CREATE INDEX COURSES_CREDITS_TEACHER ON COURSES (CREDITS_TEACHER);
CREATE INDEX COURSES_EMULATE ON COURSES (EMULATE);
CREATE INDEX COURSES_FORMAT ON COURSES (FORMAT);
CREATE INDEX COURSES_HAS_TREE ON COURSES (HAS_TREE);
CREATE INDEX COURSES_IS_MODULE_NEED_CHECK ON COURSES (IS_MODULE_NEED_CHECK);
CREATE INDEX COURSES_IS_POLL ON COURSES (IS_POLL);
CREATE INDEX COURSES_LOCKED ON COURSES (LOCKED);
CREATE INDEX COURSES_LONGTIME ON COURSES (LONGTIME);
CREATE INDEX COURSES_MARKS_CID ON COURSES_MARKS (CID);
CREATE INDEX COURSES_MARKS_CONFIRMED ON COURSES_MARKS (CONFIRMED);
CREATE INDEX COURSES_MARKS_MID ON COURSES_MARKS (MID);
CREATE INDEX COURSES_NEW_WINDOW ON COURSES (NEW_WINDOW);
CREATE INDEX COURSES_PROGRESS ON COURSES (PROGRESS);
CREATE INDEX COURSES_PROVIDER ON COURSES (PROVIDER);
CREATE INDEX COURSES_SEQUENCE_ ON COURSES (SEQUENCE);
CREATE INDEX COURSES_SERVICES ON COURSES (SERVICES);
CREATE INDEX COURSES_STATUS ON COURSES (STATUS);
CREATE INDEX COURSES_TYPE_ ON COURSES (TYPE);
CREATE INDEX COURSES_TYPEDES ON COURSES (TYPEDES);
CREATE INDEX COURSES_VALUTA ON COURSES (VALUTA);
CREATE INDEX CRONTASK_CRONTASK_RUNTIME ON CRONTASK (CRONTASK_RUNTIME);
CREATE INDEX D_OPTIONS_UN_SUBJECTS ON DEANS_OPTIONS (UNLIMITED_SUBJECTS);
CREATE INDEX DEAN_POLL_USERS_HEAD_MID ON DEAN_POLL_USERS (HEAD_MID);
CREATE INDEX DEAN_POLL_USERS_LESSON_ID ON DEAN_POLL_USERS (LESSON_ID);
CREATE INDEX DEAN_POLL_USERS_STUDENT_MID ON DEAN_POLL_USERS (STUDENT_MID);
CREATE INDEX DEANS_MID ON DEANS (MID);
CREATE INDEX DEANS_OPTIONS_USER_ID ON DEANS_OPTIONS (USER_ID);
CREATE INDEX DEANS_RESPONSIBILITIES_USER_ID ON DEANS_RESPONSIBILITIES (USER_ID);
CREATE INDEX DEANS_SUBJECT_ID ON DEANS (SUBJECT_ID);
CREATE INDEX DEVELOPERS_CID ON DEVELOPERS (CID);
CREATE INDEX DEVELOPERS_MID ON DEVELOPERS (MID);
CREATE INDEX DO_ASSIGN_NEW_SUBJECTS ON DEANS_OPTIONS (ASSIGN_NEW_SUBJECTS);
CREATE INDEX DO_UNLIMITED_CLASSIFIERS ON DEANS_OPTIONS (UNLIMITED_CLASSIFIERS);
CREATE INDEX DR_CLASSIFIER_ID ON DEANS_RESPONSIBILITIES (CLASSIFIER_ID);
CREATE INDEX EVENTS_SCALE_ID ON EVENTS (SCALE_ID);
CREATE INDEX EVENTS_TOOL ON EVENTS (TOOL);
CREATE INDEX EVENTS_WEIGHT ON EVENTS (WEIGHT);
CREATE INDEX EXERCISES_CREATED_BY ON EXERCISES (CREATED_BY);
CREATE INDEX EXERCISES_QUESTIONS ON EXERCISES (QUESTIONS);
CREATE INDEX EXERCISES_STATUS ON EXERCISES (STATUS);
CREATE INDEX EXERCISES_SUBJECT_ID ON EXERCISES (SUBJECT_ID);
CREATE INDEX FAQ_PUBLISHED ON FAQ (PUBLISHED);
CREATE INDEX FILE__FDATE ON [FILE] (FDATE);
CREATE INDEX FILE__FNUM ON [FILE] (FNUM);
CREATE INDEX FILE__FTYPE ON [FILE] (FTYPE);
CREATE INDEX FILE__FX ON [FILE] (FX);
CREATE INDEX FILE__FY ON [FILE] (FY);
CREATE INDEX FILES_FILE_SIZE ON FILES (FILE_SIZE);
CREATE INDEX FORMULA_CID ON FORMULA (CID);
CREATE INDEX FORMULA_TYPE_ ON FORMULA (TYPE);
CREATE INDEX FORUMS_LIST_FLAGS ON FORUMS_LIST (FLAGS);
CREATE INDEX FORUMS_LIST_SUBJECT_ID ON FORUMS_LIST (SUBJECT_ID);
CREATE INDEX FORUMS_LIST_SUBJECT ON FORUMS_LIST (SUBJECT);
CREATE INDEX FORUMS_LIST_USER_ID ON FORUMS_LIST (USER_ID);
CREATE INDEX FORUMS_MESSAGES_ANSWER_TO ON FORUMS_MESSAGES (ANSWER_TO);
CREATE INDEX FORUMS_MESSAGES_DELETED_BY ON FORUMS_MESSAGES (DELETED_BY);
CREATE INDEX FORUMS_MESSAGES_FLAGS ON FORUMS_MESSAGES (FLAGS);
CREATE INDEX FORUMS_MESSAGES_FORUM_ID ON FORUMS_MESSAGES (FORUM_ID);
CREATE INDEX FORUMS_MESSAGES_IS_HIDDEN ON FORUMS_MESSAGES (IS_HIDDEN);
CREATE INDEX FORUMS_MESSAGES_LEVEL_ ON FORUMS_MESSAGES (LEVEL);
CREATE INDEX FORUMS_MESSAGES_RATING ON FORUMS_MESSAGES (RATING);
CREATE INDEX FORUMS_MESSAGES_SECTION_ID ON FORUMS_MESSAGES (SECTION_ID);
CREATE INDEX FORUMS_MESSAGES_FORUM_ID_SECTION_ID ON FORUMS_MESSAGES (SECTION_ID, FORUM_ID);
CREATE INDEX FORUMS_MESSAGES_USER_ID_FORUM_ID ON FORUMS_MESSAGES (FORUM_ID, USER_ID);
CREATE INDEX FMS_MESSAGE_ID ON FORUMS_MESSAGES_SHOWED (MESSAGE_ID);
CREATE INDEX FMS_USER_ID ON FORUMS_MESSAGES_SHOWED (USER_ID);
CREATE INDEX FORUMS_MESSAGES_TEXT_SIZE ON FORUMS_MESSAGES (TEXT_SIZE);
CREATE INDEX FORUMS_MESSAGES_USER_ID ON FORUMS_MESSAGES (USER_ID);
CREATE INDEX FORUMS_SECTIONS_COUNT_MSG ON FORUMS_SECTIONS (COUNT_MSG);
CREATE INDEX FORUMS_SECTIONS_FLAGS ON FORUMS_SECTIONS (FLAGS);
CREATE INDEX FORUMS_SECTIONS_FORUM_ID ON FORUMS_SECTIONS (FORUM_ID);
CREATE INDEX FORUMS_SECTIONS_IS_HIDDEN ON FORUMS_SECTIONS (IS_HIDDEN);
CREATE INDEX FORUMS_SECTIONS_LESSON_ID ON FORUMS_SECTIONS (LESSON_ID);
CREATE INDEX FORUMS_SECTIONS_ORDER_ ON FORUMS_SECTIONS ([ORDER]);
CREATE INDEX FORUMS_SECTIONS_PARENT_ID ON FORUMS_SECTIONS (PARENT_ID);
CREATE INDEX FORUMS_SECTIONS_USER_ID ON FORUMS_SECTIONS (USER_ID);
CREATE INDEX GRADUATED_CERTIFICATE_ID ON GRADUATED (CERTIFICATE_ID);
CREATE INDEX GRADUATED_CID ON GRADUATED (CID);
CREATE INDEX GRADUATED_IS_LOOKABLE ON GRADUATED (IS_LOOKABLE);
CREATE INDEX GRADUATED_MID ON GRADUATED (MID);
CREATE INDEX GRADUATED_PROGRESS ON GRADUATED (PROGRESS);
CREATE INDEX GRADUATED_STATUS ON GRADUATED (STATUS);
CREATE INDEX GROUPNAME_CID ON GROUPNAME (CID);
CREATE INDEX GROUPNAME_OWNER_GID ON GROUPNAME (OWNER_GID);
CREATE INDEX GROUPUSER_CID ON GROUPUSER (CID);
CREATE INDEX GROUPUSER_GID ON GROUPUSER (GID);
CREATE INDEX GROUPUSER_MID ON GROUPUSER (MID);
CREATE INDEX HACP_DEBUG_DIRECTION ON HACP_DEBUG (DIRECTION);
CREATE INDEX HELP_LINK_SUBJECT ON HELP (LINK_SUBJECT);
CREATE INDEX HELP_MODERATED ON HELP (MODERATED);
CREATE INDEX HOLIDAYS_TYPE_ ON HOLIDAYS (TYPE);
CREATE INDEX HOLIDAYS_USER_ID ON HOLIDAYS (USER_ID);
CREATE INDEX HTMLPAGE_GROUP_ID ON HTMLPAGE (GROUP_ID);
CREATE INDEX HTMLPAGE_GROUPS_LEVEL_ ON HTMLPAGE_GROUPS (LEVEL);
CREATE INDEX HTMLPAGE_GROUPS_LFT ON HTMLPAGE_GROUPS (LFT);
CREATE INDEX HTMLPAGE_GROUPS_ORDR ON HTMLPAGE_GROUPS (ORDR);
CREATE INDEX HTMLPAGE_GROUPS_RGT ON HTMLPAGE_GROUPS (RGT);
CREATE INDEX HTMLPAGE_ORDR ON HTMLPAGE (ORDR);
CREATE INDEX INTERESTING_FACTS_STATUS ON INTERESTING_FACTS (STATUS);
CREATE INDEX INTERFACE_NECESSITY ON INTERFACE (NECESSITY);
CREATE INDEX INTERFACE_USER_ID ON INTERFACE (USER_ID);
CREATE INDEX INTERFACE_X ON INTERFACE (X);
CREATE INDEX INTERFACE_Y ON INTERFACE (Y);
CREATE INDEX INTERVIEW_FILES_INTERVIEW_ID ON INTERVIEW_FILES (INTERVIEW_ID);
CREATE INDEX INTERVIEW_INTERVIEW_HASH ON INTERVIEW (INTERVIEW_HASH);
CREATE INDEX INTERVIEW_LESSON_ID ON INTERVIEW (LESSON_ID);
CREATE INDEX INTERVIEW_TO_WHOM ON INTERVIEW (TO_WHOM);
CREATE INDEX INTERVIEW_TYPE_ ON INTERVIEW (TYPE);
CREATE INDEX INTERVIEW_USER_ID ON INTERVIEW (USER_ID);
CREATE INDEX LIBRARY_CID ON LIBRARY (CID);
CREATE INDEX LIBRARY_IS_ACTIVE_VERSION ON LIBRARY (IS_ACTIVE_VERSION);
CREATE INDEX LIBRARY_IS_PACKAGE ON LIBRARY (IS_PACKAGE);
CREATE INDEX LIBRARY_MID ON LIBRARY (MID);
CREATE INDEX LIBRARY_NEED_ACCESS_LEVEL ON LIBRARY (NEED_ACCESS_LEVEL);
CREATE INDEX LIBRARY_PARENT ON LIBRARY (PARENT);
CREATE INDEX LIBRARY_POINTID ON LIBRARY (POINTID);
CREATE INDEX LIBRARY_QUANTITY ON LIBRARY (QUANTITY);
CREATE INDEX LIBRARY_TYPE_ ON LIBRARY (TYPE);
CREATE INDEX LIBRARY_UID_ ON LIBRARY (UID);
CREATE INDEX LIST_CREATED_BY ON LIST (CREATED_BY);
CREATE INDEX LIST_FILES_FILE_ID ON LIST_FILES (FILE_ID);
CREATE INDEX LIST_IS_POLL ON LIST (IS_POLL);
CREATE INDEX LIST_IS_SHUFFLED ON LIST (IS_SHUFFLED);
CREATE INDEX LIST_LAST ON LIST (LAST);
CREATE INDEX LIST_ORDR ON LIST (ORDR);
CREATE INDEX LIST_QMODER ON LIST (QMODER);
CREATE INDEX LIST_QTYPE ON LIST (QTYPE);
CREATE INDEX LIST_TIMELIMIT ON LIST (TIMELIMIT);
CREATE INDEX LIST_TIMETOANSWER ON LIST (TIMETOANSWER);
CREATE INDEX LOGSEANCE_CID ON LOGSEANCE (CID);
CREATE INDEX LOGSEANCE_MID ON LOGSEANCE (MID);
CREATE INDEX LOGSEANCE_NUMBER_ ON LOGSEANCE (NUMBER);
CREATE INDEX LOGSEANCE_SHEID ON LOGSEANCE (SHEID);
CREATE INDEX LOGSEANCE_STID ON LOGSEANCE (STID);
CREATE INDEX LOGSEANCE_TID ON LOGSEANCE (TID);
CREATE INDEX LOGSEANCE_TIME ON LOGSEANCE (TIME);
CREATE INDEX LOGUSER_CID ON LOGUSER (CID);
CREATE INDEX LOGUSER_FREE ON LOGUSER (FREE);
CREATE INDEX LOGUSER_FULLTIME ON LOGUSER (FULLTIME);
CREATE INDEX LOGUSER_MID ON LOGUSER (MID);
CREATE INDEX LOGUSER_MODER ON LOGUSER (MODER);
CREATE INDEX LOGUSER_MODERBY ON LOGUSER (MODERBY);
CREATE INDEX LOGUSER_MODERTIME ON LOGUSER (MODERTIME);
CREATE INDEX LOGUSER_NEEDMODER ON LOGUSER (NEEDMODER);
CREATE INDEX LOGUSER_QTY ON LOGUSER (QTY);
CREATE INDEX LOGUSER_QUESTALL ON LOGUSER (QUESTALL);
CREATE INDEX LOGUSER_QUESTDONE ON LOGUSER (QUESTDONE);
CREATE INDEX LOGUSER_SHEID ON LOGUSER (SHEID);
CREATE INDEX LOGUSER_SKIP_ ON LOGUSER (SKIP);
CREATE INDEX LOGUSER_START_ ON LOGUSER (START);
CREATE INDEX LOGUSER_STATUS ON LOGUSER (STATUS);
CREATE INDEX LOGUSER_STOP_ ON LOGUSER (STOP);
CREATE INDEX LOGUSER_TEACHERTEST ON LOGUSER (TEACHERTEST);
CREATE INDEX LOGUSER_TID ON LOGUSER (TID);
CREATE INDEX MANAGERS_MID ON MANAGERS (MID);
CREATE INDEX MESSAGES_FROM_ ON MESSAGES ([FROM]);
CREATE INDEX MESSAGES_SUBJECT_ID ON MESSAGES (SUBJECT_ID);
CREATE INDEX MESSAGES_TO_ ON MESSAGES ([TO]);
CREATE INDEX METHODOLOGIST_CID ON METHODOLOGIST (CID);
CREATE INDEX METHODOLOGIST_MID ON METHODOLOGIST (MID);
CREATE INDEX NEWS_CREATED_BY ON NEWS (CREATED_BY);
CREATE INDEX NEWS_SUBJECT_ID ON NEWS (SUBJECT_ID);
CREATE INDEX NEWS2_APPLICATION ON NEWS2 (APPLICATION);
CREATE INDEX NEWS2_RESOURCE_ID ON NEWS2 (RESOURCE_ID);
CREATE INDEX NEWS2_SHOW ON NEWS2 (SHOW);
CREATE INDEX NEWS2_STANDALONE ON NEWS2 (STANDALONE);
CREATE INDEX NEWS2_TYPE_ ON NEWS2 (TYPE);
CREATE INDEX NOTICE_ENABLED ON NOTICE (ENABLED);
CREATE INDEX NOTICE_RECEIVER ON NOTICE (RECEIVER);
CREATE INDEX NOTICE_TYPE_ ON NOTICE (TYPE);
CREATE INDEX OAUTH_APPS_CREATED_BY ON OAUTH_APPS (CREATED_BY);
CREATE INDEX OAUTH_NONCES_APP_ID ON OAUTH_NONCES (APP_ID);
CREATE INDEX OAUTH_TOKENS_APP_ID ON OAUTH_TOKENS (APP_ID);
CREATE INDEX OAUTH_TOKENS_STATE ON OAUTH_TOKENS (STATE);
CREATE INDEX OAUTH_TOKENS_USER_ID ON OAUTH_TOKENS (USER_ID);
CREATE INDEX ORGANIZATIONS_CID ON ORGANIZATIONS (CID);
CREATE INDEX ORGANIZATIONS_LEVEL_ ON ORGANIZATIONS (LEVEL);
CREATE INDEX ORGANIZATIONS_MOD_REF ON ORGANIZATIONS (MOD_REF);
CREATE INDEX ORGANIZATIONS_MODULE ON ORGANIZATIONS (MODULE);
CREATE INDEX ORGANIZATIONS_NEXT_REF ON ORGANIZATIONS (NEXT_REF);
CREATE INDEX ORGANIZATIONS_PREV_REF ON ORGANIZATIONS (PREV_REF);
CREATE INDEX ORGANIZATIONS_ROOT_REF ON ORGANIZATIONS (ROOT_REF);
CREATE INDEX ORGANIZATIONS_STATUS ON ORGANIZATIONS (STATUS);
CREATE INDEX ORGANIZATIONS_VOL1 ON ORGANIZATIONS (VOL1);
CREATE INDEX ORGANIZATIONS_VOL2 ON ORGANIZATIONS (VOL2);
CREATE INDEX PASSWORD_HISTORY_USER_ID ON PASSWORD_HISTORY (USER_ID);
CREATE INDEX PEOPLE_ACCESS_LEVEL ON PEOPLE (ACCESS_LEVEL);
CREATE INDEX PEOPLE_BLOCKED ON PEOPLE (BLOCKED);
CREATE INDEX PEOPLE_COUNTLOGIN ON PEOPLE (COUNTLOGIN);
CREATE INDEX PEOPLE_COURSE ON PEOPLE (COURSE);
CREATE INDEX PEOPLE_DUBLICATE ON PEOPLE (DUBLICATE);
CREATE INDEX PEOPLE_EMAIL_CONFIRMED ON PEOPLE (EMAIL_CONFIRMED);
CREATE INDEX PEOPLE_FORCE_PASSWORD ON PEOPLE (FORCE_PASSWORD);
CREATE INDEX PEOPLE_GENDER ON PEOPLE (GENDER);
CREATE INDEX PEOPLE_HEAD_MID ON PEOPLE (HEAD_MID);
CREATE INDEX PEOPLE_ICQNUMBER ON PEOPLE (ICQNUMBER);
CREATE INDEX PEOPLE_INVALID_LOGIN ON PEOPLE (INVALID_LOGIN);
CREATE INDEX PEOPLE_ISAD ON PEOPLE (ISAD);
CREATE INDEX PEOPLE_LAST ON PEOPLE (LAST);
CREATE INDEX PEOPLE_NEED_EDIT ON PEOPLE (NEED_EDIT);
CREATE INDEX PEOPLE_PREFERRED_LANG ON PEOPLE (PREFERRED_LANG);
CREATE INDEX PEOPLE_RANG ON PEOPLE (RANG);
CREATE INDEX PEOPLE_RNID ON PEOPLE (RNID);
CREATE INDEX PERIODS_COUNT_HOURS ON PERIODS (COUNT_HOURS);
CREATE INDEX PERIODS_STARTTIME ON PERIODS (STARTTIME);
CREATE INDEX PERIODS_STOPTIME ON PERIODS (STOPTIME);
CREATE INDEX PERMISSION_GROUPS_APPLICATION ON PERMISSION_GROUPS (APPLICATION);
CREATE INDEX PERMISSION_GROUPS_RANG ON PERMISSION_GROUPS (RANG);
CREATE INDEX PERMISSION2ACT_PMID ON PERMISSION2ACT ( PMID);
CREATE INDEX PERMISSION2MID_MID ON PERMISSION2MID (MID);
CREATE INDEX PERMISSION2MID_PMID ON PERMISSION2MID (PMID);
CREATE INDEX PEU_PROGRAMM_EVENT_ID ON PROGRAMM_EVENTS_USERS (PROGRAMM_EVENT_ID);
CREATE INDEX PPT2SWF_POOL_ID ON PPT2SWF (POOL_ID);
CREATE INDEX PPT2SWF_PROCESS ON PPT2SWF (PROCESS);
CREATE INDEX PPT2SWF_STATUS ON PPT2SWF (STATUS);
CREATE INDEX PPT2SWF_WEBINAR_ID ON PPT2SWF (WEBINAR_ID);
CREATE INDEX PROCESSES_PROCESS_ID ON PROCESSES (PROCESS_ID);
CREATE INDEX PROCESSES_TYPE_ ON PROCESSES (TYPE);
CREATE INDEX PROGRAMM_EVENTS_ITEM_ID ON PROGRAMM_EVENTS (ITEM_ID);
CREATE INDEX PROGRAMM_EVENTS_PROGRAMM_ID ON PROGRAMM_EVENTS (PROGRAMM_ID);
CREATE INDEX PROGRAMM_EVENTS_TYPE_ ON PROGRAMM_EVENTS (TYPE);
CREATE INDEX PROGRAMM_EVENTS_USERS_STATUS ON PROGRAMM_EVENTS_USERS (STATUS);
CREATE INDEX PROGRAMM_EVENTS_USERS_USER_ID ON PROGRAMM_EVENTS_USERS (USER_ID);
CREATE INDEX PROGRAMM_PROGRAMM_TYPE ON PROGRAMM (PROGRAMM_TYPE);
CREATE INDEX PROGRAMM_USERS_PROGRAMM_ID ON PROGRAMM_USERS (PROGRAMM_ID);
CREATE INDEX PROGRAMM_USERS_USER_ID ON PROGRAMM_USERS (USER_ID);
CREATE INDEX QUIZZES_ANSWERS_ANSWER_ID ON QUIZZES_ANSWERS (ANSWER_ID);
CREATE INDEX QUIZZES_ANSWERS_QUIZ_ID ON QUIZZES_ANSWERS (QUIZ_ID);
CREATE INDEX QUIZZES_CREATED_BY ON QUIZZES (CREATED_BY);
CREATE INDEX QUIZZES_FEEDBACK_LESSON_ID ON QUIZZES_FEEDBACK (LESSON_ID);
CREATE INDEX QUIZZES_FEEDBACK_STATUS ON QUIZZES_FEEDBACK (STATUS);
CREATE INDEX QUIZZES_FEEDBACK_SUBJECT_ID ON QUIZZES_FEEDBACK (SUBJECT_ID);
CREATE INDEX QUIZZES_FEEDBACK_TRAINER_ID ON QUIZZES_FEEDBACK (TRAINER_ID);
CREATE INDEX QUIZZES_FEEDBACK_USER_ID ON QUIZZES_FEEDBACK (USER_ID);
CREATE INDEX QUIZZES_LOCATION ON QUIZZES (LOCATION);
CREATE INDEX QUIZZES_QUESTIONS ON QUIZZES (QUESTIONS);
CREATE INDEX QUIZZES_RESULTS_ANSWER_ID ON QUIZZES_RESULTS (ANSWER_ID);
CREATE INDEX QUIZZES_RESULTS_JUNIOR_ID ON QUIZZES_RESULTS (JUNIOR_ID);
CREATE INDEX QUIZZES_RESULTS_LESSON_ID ON QUIZZES_RESULTS (LESSON_ID);
CREATE INDEX QUIZZES_RESULTS_QUIZ_ID ON QUIZZES_RESULTS (QUIZ_ID);
CREATE INDEX QUIZZES_RESULTS_SUBJECT_ID ON QUIZZES_RESULTS (SUBJECT_ID);
CREATE INDEX QUIZZES_RESULTS_USER_ID ON QUIZZES_RESULTS (USER_ID);
CREATE INDEX QUIZZES_STATUS ON QUIZZES (STATUS);
CREATE INDEX QUIZZES_SUBJECT_ID ON QUIZZES (SUBJECT_ID);
CREATE INDEX QUEST_QUESTION_VARIANTS_QUESTION_ID ON QUEST_QUESTION_VARIANTS (QUESTION_ID);
CREATE INDEX QUEST_QUESTION_RESULTS_QUESTION_ID ON QUEST_QUESTION_RESULTS (QUESTION_ID);
CREATE INDEX REPORTS_CREATED_BY ON REPORTS (CREATED_BY);
CREATE INDEX REPORTS_ROLES_REPORT_ID ON REPORTS_ROLES (REPORT_ID);
CREATE INDEX REPORTS_STATUS ON REPORTS (STATUS);
CREATE INDEX RESOURCE_REVISIONS_CREATED_BY ON RESOURCE_REVISIONS (CREATED_BY);
CREATE INDEX RESOURCE_REVISIONS_FILETYPE ON RESOURCE_REVISIONS (FILETYPE);
CREATE INDEX RESOURCE_REVISIONS_RESOURCE_ID ON RESOURCE_REVISIONS (RESOURCE_ID);
CREATE INDEX RESOURCES_ACTIVITY_ID ON RESOURCES (ACTIVITY_ID);
CREATE INDEX RESOURCES_ACTIVITY_TYPE ON RESOURCES (ACTIVITY_TYPE);
CREATE INDEX RESOURCES_CREATED_BY ON RESOURCES (CREATED_BY);
CREATE INDEX RESOURCES_FILETYPE ON RESOURCES (FILETYPE);
CREATE INDEX RESOURCES_LOCATION ON RESOURCES (LOCATION);
CREATE INDEX RESOURCES_PARENT_ID ON RESOURCES (PARENT_ID);
CREATE INDEX RESOURCES_PARENT_REVISION_ID ON RESOURCES (PARENT_REVISION_ID);
CREATE INDEX RESOURCES_SERVICES ON RESOURCES (SERVICES);
CREATE INDEX RESOURCES_STATUS ON RESOURCES (STATUS);
CREATE INDEX RESOURCES_SUBJECT_ID ON RESOURCES (SUBJECT_ID);
CREATE INDEX RESOURCES_TEST_ID ON RESOURCES (TEST_ID);
CREATE INDEX RESOURCES_TYPE_ ON RESOURCES (TYPE);
CREATE INDEX ROOMS_STATUS ON ROOMS (STATUS);
CREATE INDEX ROOMS_TYPE_ ON ROOMS (TYPE);
CREATE INDEX ROOMS_VOLUME ON ROOMS (VOLUME);
CREATE INDEX ROOMS2COURSE_CID ON ROOMS2COURSE (CID);
CREATE INDEX ROOMS2COURSE_RID ON ROOMS2COURSE (RID);
CREATE INDEX S_CHANNELS_SUBJECT_ID ON SUBSCRIPTION_CHANNELS (SUBJECT_ID);
CREATE INDEX SC_LESSON_ID ON SUBSCRIPTION_CHANNELS (LESSON_ID);
CREATE INDEX SCALE_VALUES_SCALE_ID ON SCALE_VALUES (SCALE_ID);
CREATE INDEX SCALE_VALUES_VALUE ON SCALE_VALUES (VALUE);
CREATE INDEX SCALES_TYPE_ ON SCALES (TYPE);
CREATE INDEX SCHEDULE_ALL_ ON SCHEDULE ([ALL]);
CREATE INDEX SCHEDULE_CHID ON SCHEDULE (CHID);
CREATE INDEX SCHEDULE_CID ON SCHEDULE (CID);
CREATE INDEX SCHEDULE_COND_OPERATION ON SCHEDULE (COND_OPERATION);
CREATE INDEX SCHEDULE_COND_SHEID ON SCHEDULE (COND_SHEID);
CREATE INDEX SCHEDULE_CREATEID ON SCHEDULE (CREATEID);
CREATE INDEX SCHEDULE_GID ON SCHEDULE (GID);
CREATE INDEX SCHEDULE_ISFREE ON SCHEDULE (ISFREE);
CREATE INDEX SCHEDULE_MARKS_HISTORY_MARK ON SCHEDULE_MARKS_HISTORY (MARK);
CREATE INDEX SCHEDULE_MARKS_HISTORY_MID ON SCHEDULE_MARKS_HISTORY (MID);
CREATE INDEX SCHEDULE_MARKS_HISTORY_SSID ON SCHEDULE_MARKS_HISTORY (SSID);
CREATE INDEX SCHEDULE_MODERATOR ON SCHEDULE (MODERATOR);
CREATE INDEX SCHEDULE_NOTICE ON SCHEDULE (NOTICE);
CREATE INDEX SCHEDULE_NOTICE_DAYS ON SCHEDULE (NOTICE_DAYS);
CREATE INDEX SCHEDULE_ORDER_ ON SCHEDULE ([ORDER]);
CREATE INDEX SCHEDULE_PERM ON SCHEDULE (PERM);
CREATE INDEX SCHEDULE_PUB ON SCHEDULE (PUB);
CREATE INDEX SCHEDULE_RECOMMEND ON SCHEDULE (RECOMMEND);
CREATE INDEX SCHEDULE_RID ON SCHEDULE (RID);
CREATE INDEX SCHEDULE_SECTION_ID ON SCHEDULE (SECTION_ID);
CREATE INDEX SCHEDULE_SHAREPOINTID ON SCHEDULE (SHAREPOINTID);
CREATE INDEX SCHEDULE_STARTDAY ON SCHEDULE (STARTDAY);
CREATE INDEX SCHEDULE_STOPDAY ON SCHEDULE (STOPDAY);
CREATE INDEX SCHEDULE_TEACHER ON SCHEDULE (TEACHER);
CREATE INDEX SCHEDULE_TIMETYPE ON SCHEDULE (TIMETYPE);
CREATE INDEX SCHEDULE_TYPEID ON SCHEDULE (TYPEID);
CREATE INDEX SCHEDULE_VEDOMOST ON SCHEDULE (VEDOMOST);
CREATE INDEX SCHEDULEID_CHIEF ON SCHEDULEID (CHIEF);
CREATE INDEX SCHEDULEID_EMAILREMIND ON SCHEDULEID (EMAILREMIND);
CREATE INDEX SCHEDULEID_GID ON SCHEDULEID (GID);
CREATE INDEX SCHEDULEID_ICQREMIND ON SCHEDULEID (ICQREMIND);
CREATE INDEX SCHEDULEID_ISTUDREMIND ON SCHEDULEID (ISTUDREMIND);
CREATE INDEX SCHEDULEID_MID ON SCHEDULEID (MID);
CREATE INDEX SCHEDULEID_SHEID ON SCHEDULEID (SHEID);
CREATE INDEX SCHEDULEID_SMSREMIND ON SCHEDULEID (SMSREMIND);
CREATE INDEX SCHEDULEID_TEST_CORR ON SCHEDULEID (TEST_CORR);
CREATE INDEX SCHEDULEID_TEST_TRIES ON SCHEDULEID (TEST_TRIES);
CREATE INDEX SCHEDULEID_TEST_WRONG ON SCHEDULEID (TEST_WRONG);
CREATE INDEX SCHEDULEID_V_DONE ON SCHEDULEID (V_DONE);
CREATE INDEX SCHEDULEID_V_STATUS ON SCHEDULEID (V_STATUS);
CREATE INDEX SCORM_REPORT_CID ON SCORM_REPORT (CID);
CREATE INDEX SCORM_REPORT_LESSON_ID ON SCORM_REPORT (LESSON_ID);
CREATE INDEX SCORM_REPORT_MID ON SCORM_REPORT (MID);
CREATE INDEX SCORM_TRACKLOG_CID ON SCORM_TRACKLOG (CID);
CREATE INDEX SCORM_TRACKLOG_LESSON_ID ON SCORM_TRACKLOG (LESSON_ID);
CREATE INDEX SCORM_TRACKLOG_MCID ON SCORM_TRACKLOG (MCID);
CREATE INDEX SCORM_TRACKLOG_MID ON SCORM_TRACKLOG (MID);
CREATE INDEX SCORM_TRACKLOG_MODID ON SCORM_TRACKLOG (MODID);
CREATE INDEX SCORM_TRACKLOG_MID_LESSON_ID ON SCORM_TRACKLOG (MID, LESSON_ID);
CREATE INDEX SCORM_TRACKLOG_SEARCH ON SCORM_TRACKLOG (MID, CID, MODID, MCID, LESSON_ID););
CREATE INDEX SE_CHANNEL_ID ON SUBSCRIPTION_ENTRIES (CHANNEL_ID);
CREATE INDEX SEANCE_CID ON SEANCE (CID);
CREATE INDEX SEANCE_MID ON SEANCE (MID);
CREATE INDEX SEANCE_STID ON SEANCE (STID);
CREATE INDEX SEANCE_TID ON SEANCE (TID);
CREATE INDEX SECTIONS_ORDER_ ON SECTIONS ([ORDER]);
CREATE INDEX SECTIONS_SUBJECT_ID ON SECTIONS (SUBJECT_ID);
CREATE INDEX SEQUENCE_CURRENT_CID ON SEQUENCE_CURRENT (CID);
CREATE INDEX SEQUENCE_CURRENT_LESSON_ID ON SEQUENCE_CURRENT (LESSON_ID);
CREATE INDEX SEQUENCE_CURRENT_MID ON SEQUENCE_CURRENT (MID);
CREATE INDEX SEQUENCE_CURRENT_SUBJECT_ID ON SEQUENCE_CURRENT (SUBJECT_ID);
CREATE INDEX SEQUENCE_HISTORY_CID ON SEQUENCE_HISTORY (CID);
CREATE INDEX SEQUENCE_HISTORY_LID ON SEQUENCE_HISTORY (LESSON_ID);
CREATE INDEX SEQUENCE_HISTORY_MID ON SEQUENCE_HISTORY (MID);
CREATE INDEX SEQUENCE_HISTORY_SUBID ON SEQUENCE_HISTORY (SUBJECT_ID);
CREATE INDEX SESSIONS_COOKIE ON SESSIONS (COOKIE);
CREATE INDEX SESSIONS_JS ON SESSIONS (JS);
CREATE INDEX SESSIONS_LOGOUT ON SESSIONS (LOGOUT);
CREATE INDEX SESSIONS_MID ON SESSIONS (MID);
CREATE INDEX SESSIONS_START ON SESSIONS (START);
CREATE INDEX SESSION_GUEST_START ON SESSION_GUEST (START);
CREATE INDEX SESSION_GUEST_STOP ON SESSION_GUEST (STOP);
CREATE INDEX SOO_DISPLAY_RESULTS ON STRUCTURE_OF_ORGAN (DISPLAY_RESULTS);
CREATE INDEX SOO_ENEMY_RESULTS ON STRUCTURE_OF_ORGAN (ENEMY_RESULTS);
CREATE INDEX SOO_SPECIALIZATION ON STRUCTURE_OF_ORGAN (SPECIALIZATION);
CREATE INDEX SOP_STATE_OF_PROCESS_ID ON STATE_OF_PROCESS (STATE_OF_PROCESS_ID);
CREATE INDEX SP_RESTRICTION_TYPE ON SUBJECTS (PERIOD_RESTRICTION_TYPE);
CREATE INDEX STATE_OF_PROCESS_ITEM_ID ON STATE_OF_PROCESS (ITEM_ID);
CREATE INDEX STATE_OF_PROCESS_PROCESS_ID ON STATE_OF_PROCESS (PROCESS_ID);
CREATE INDEX STATE_OF_PROCESS_PROCESS_TYPE ON STATE_OF_PROCESS (PROCESS_TYPE);
CREATE INDEX STATE_OF_PROCESS_STATUS ON STATE_OF_PROCESS (STATUS);
CREATE INDEX STORAGE_FILESYSTEM_ID ON STORAGE_FILESYSTEM (ID);
CREATE INDEX STORAGE_FILESYSTEM_IS_FILE ON STORAGE_FILESYSTEM (IS_FILE);
CREATE INDEX STORAGE_FILESYSTEM_PARENT_ID ON STORAGE_FILESYSTEM (PARENT_ID);
CREATE INDEX STORAGE_FILESYSTEM_SUBJECT_ID ON STORAGE_FILESYSTEM (SUBJECT_ID);
CREATE INDEX STORAGE_FILESYSTEM_USER_ID ON STORAGE_FILESYSTEM (USER_ID);
CREATE INDEX STRUCTURE_OF_ORGAN_AGREEM ON STRUCTURE_OF_ORGAN (AGREEM);
CREATE INDEX STRUCTURE_OF_ORGAN_CLAIMANT ON STRUCTURE_OF_ORGAN (CLAIMANT);
CREATE INDEX STRUCTURE_OF_ORGAN_IS_MANAGER ON STRUCTURE_OF_ORGAN (IS_MANAGER);
CREATE INDEX STRUCTURE_OF_ORGAN_LEVEL_ ON STRUCTURE_OF_ORGAN (LEVEL);
CREATE INDEX STRUCTURE_OF_ORGAN_LFT ON STRUCTURE_OF_ORGAN (LFT);
CREATE INDEX STRUCTURE_OF_ORGAN_MID ON STRUCTURE_OF_ORGAN (MID);
CREATE INDEX STRUCTURE_OF_ORGAN_OWN_RESULTS ON STRUCTURE_OF_ORGAN (OWN_RESULTS);
CREATE INDEX STRUCTURE_OF_ORGAN_OWNER_SOID ON STRUCTURE_OF_ORGAN (OWNER_SOID);
CREATE INDEX STRUCTURE_OF_ORGAN_RGT ON STRUCTURE_OF_ORGAN (RGT);
CREATE INDEX STRUCTURE_OF_ORGAN_THRESHOLD ON STRUCTURE_OF_ORGAN (THRESHOLD);
CREATE INDEX STRUCTURE_OF_ORGAN_TYPE_ ON STRUCTURE_OF_ORGAN (TYPE);
CREATE INDEX STRUCTURE_OF_ORGAN_STAFF_UNIT_ID ON STRUCTURE_OF_ORGAN (STAFF_UNIT_ID);
CREATE INDEX STAFF_UNITS_SOID ON STAFF_UNITS (SOID);
CREATE INDEX STAFF_UNITS_PROFILE_ID ON STAFF_UNITS (PROFILE_ID);
CREATE INDEX STUDENTS_CGID ON STUDENTS (CGID);
CREATE INDEX STUDENTS_CID ON STUDENTS (CID);
CREATE INDEX STUDENTS_MID ON STUDENTS (MID);
CREATE INDEX STUDENTS_REGISTERED ON STUDENTS (REGISTERED);
CREATE INDEX SUBJECTS_ACCESS_ELEMENTS ON SUBJECTS (ACCESS_ELEMENTS);
CREATE INDEX SUBJECTS_ACCESS_MODE ON SUBJECTS (ACCESS_MODE);
CREATE INDEX SUBJECTS_AUTO_DONE ON SUBJECTS (AUTO_DONE);
CREATE INDEX SUBJECTS_AUTO_GRADUATE ON SUBJECTS (AUTO_GRADUATE);
CREATE INDEX SUBJECTS_AUTO_MARK ON SUBJECTS (AUTO_MARK);
CREATE INDEX SUBJECTS_BASE ON SUBJECTS (BASE);
CREATE INDEX SUBJECTS_BASE_ID ON SUBJECTS (BASE_ID);
CREATE INDEX SUBJECTS_CLAIMANT_PROCESS_ID ON SUBJECTS (CLAIMANT_PROCESS_ID);
CREATE INDEX SUBJECTS_COURSES_COURSE_ID ON SUBJECTS_COURSES (COURSE_ID);
CREATE INDEX SUBJECTS_COURSES_SUBJECT_ID ON SUBJECTS_COURSES (SUBJECT_ID);
CREATE INDEX SUBJECTS_EXERCISES_EXERCISE_ID ON SUBJECTS_EXERCISES (EXERCISE_ID);
CREATE INDEX SUBJECTS_EXERCISES_SUBJECT_ID ON SUBJECTS_EXERCISES (SUBJECT_ID);
CREATE INDEX SUBJECTS_FORMULA_ID ON SUBJECTS (FORMULA_ID);
CREATE INDEX SUBJECTS_LONGTIME ON SUBJECTS (LONGTIME);
CREATE INDEX SUBJECTS_MODE_FREE_LIMIT ON SUBJECTS (MODE_FREE_LIMIT);
CREATE INDEX SUBJECTS_PERIOD ON SUBJECTS (PERIOD);
CREATE INDEX SUBJECTS_PLAN_USERS ON SUBJECTS (PLAN_USERS);
CREATE INDEX SUBJECTS_QUIZZES_QUIZ_ID ON SUBJECTS_QUIZZES (QUIZ_ID);
CREATE INDEX SUBJECTS_QUIZZES_SUBJECT_ID ON SUBJECTS_QUIZZES (SUBJECT_ID);
CREATE INDEX SUBJECTS_RESOURCES_RESOURCE_ID ON SUBJECTS_RESOURCES (RESOURCE_ID);
CREATE INDEX SUBJECTS_RESOURCES_SUBJECT_ID ON SUBJECTS_RESOURCES (SUBJECT_ID);
CREATE INDEX SUBJECTS_SCALE_ID ON SUBJECTS (SCALE_ID);
CREATE INDEX SUBJECTS_SERVICES ON SUBJECTS (SERVICES);
CREATE INDEX SUBJECTS_STATE_ ON SUBJECTS (STATE);
CREATE INDEX SUBJECTS_SUPPLIER_ID ON SUBJECTS (SUPPLIER_ID);
CREATE INDEX SUBJECTS_TASKS_SUBJECT_ID ON SUBJECTS_TASKS (SUBJECT_ID);
CREATE INDEX SUBJECTS_TASKS_TASK_ID ON SUBJECTS_TASKS (TASK_ID);
CREATE INDEX SUBJECTS_THRESHOLD ON SUBJECTS (THRESHOLD);
CREATE INDEX SUBSCRIPTION_ENTRIES_AUTHOR ON SUBSCRIPTION_ENTRIES (AUTHOR);
CREATE INDEX SUBSCRIPTIONS_CHANNEL_ID ON SUBSCRIPTIONS (CHANNEL_ID);
CREATE INDEX SUBSCRIPTIONS_USER_ID ON SUBSCRIPTIONS (USER_ID);
CREATE INDEX TAG_REF_ITEM_ID ON TAG_REF (ITEM_ID);
CREATE INDEX TAG_REF_ITEM_TYPE ON TAG_REF (ITEM_TYPE);
CREATE INDEX TAG_REF_TAG_ID ON TAG_REF (TAG_ID);
CREATE INDEX TAG_REF_ITEM_ID_ITEM_TYPE ON TAG_REF (ITEM_ID, ITEM_TYPE);
CREATE INDEX TASKS_CREATED_BY ON TASKS (CREATED_BY);
CREATE INDEX TASKS_LOCATION ON TASKS (LOCATION);
CREATE INDEX TASKS_QUESTIONS ON TASKS (QUESTIONS);
CREATE INDEX TASKS_STATUS ON TASKS (STATUS);
CREATE INDEX TASKS_SUBJECT_ID ON TASKS (SUBJECT_ID);
CREATE INDEX TEACHERS_CID ON TEACHERS (CID);
CREATE INDEX TEACHERS_MID ON TEACHERS (MID);
CREATE INDEX TEST_ABSTRACT_CREATED_BY ON TEST_ABSTRACT (CREATED_BY);
CREATE INDEX TEST_ABSTRACT_LOCATION ON TEST_ABSTRACT (LOCATION);
CREATE INDEX TEST_ABSTRACT_QUESTIONS ON TEST_ABSTRACT (QUESTIONS);
CREATE INDEX TEST_ABSTRACT_STATUS ON TEST_ABSTRACT (STATUS);
CREATE INDEX TEST_ABSTRACT_SUBJECT_ID ON TEST_ABSTRACT (SUBJECT_ID);
CREATE INDEX TEST_ADAPTIVE ON TEST (ADAPTIVE);
CREATE INDEX TEST_CACHE_QTY ON TEST (CACHE_QTY);
CREATE INDEX TEST_CID ON TEST (CID);
CREATE INDEX TEST_CIDOWNER ON TEST (CIDOWNER);
CREATE INDEX TEST_CREATED_BY ON TEST (CREATED_BY);
CREATE INDEX TEST_DATATYPE ON TEST (DATATYPE);
CREATE INDEX TEST_ENDRES ON TEST (ENDRES);
CREATE INDEX TEST_FEEDBACK_PARENT ON TEST_FEEDBACK (PARENT);
CREATE INDEX TEST_FEEDBACK_SHOW_EVENT ON TEST_FEEDBACK (SHOW_EVENT);
CREATE INDEX TEST_FEEDBACK_TEST_ID ON TEST_FEEDBACK (TEST_ID);
CREATE INDEX TEST_FEEDBACK_TRESHOLD_MAX ON TEST_FEEDBACK (TRESHOLD_MAX);
CREATE INDEX TEST_FEEDBACK_TRESHOLD_MIN ON TEST_FEEDBACK (TRESHOLD_MIN);
CREATE INDEX TEST_FEEDBACK_TYPE_ ON TEST_FEEDBACK (TYPE);
CREATE INDEX TEST_FREE ON TEST (FREE);
CREATE INDEX TEST_IS_POLL ON TEST (IS_POLL);
CREATE INDEX TEST_LAST ON TEST (LAST);
CREATE INDEX TEST_LASTMID ON TEST (LASTMID);
CREATE INDEX TEST_LESSON_ID ON TEST (LESSON_ID);
CREATE INDEX TEST_LIM ON TEST (LIM);
CREATE INDEX TEST_LIMITCLEAN ON TEST (LIMITCLEAN);
CREATE INDEX TEST_MODE_ ON TEST (MODE);
CREATE INDEX TEST_POLL_MID ON TEST (POLL_MID);
CREATE INDEX TEST_QTY ON TEST (QTY);
CREATE INDEX TEST_QUESTRES ON TEST (QUESTRES);
CREATE INDEX TEST_RANDOM_ ON TEST (RANDOM);
CREATE INDEX TEST_RATING ON TEST (RATING);
CREATE INDEX TEST_SHOWOTVET ON TEST (SHOWOTVET);
CREATE INDEX TEST_SHOWURL ON TEST (SHOWURL);
CREATE INDEX TEST_SKIP_ ON TEST (SKIP);
CREATE INDEX TEST_SORT_ ON TEST (SORT);
CREATE INDEX TEST_STARTLIMIT ON TEST (STARTLIMIT);
CREATE INDEX TEST_STATUS ON TEST (STATUS);
CREATE INDEX TEST_TEST_ID ON TEST (TEST_ID);
CREATE INDEX TEST_THRESHOLD ON TEST (THRESHOLD);
CREATE INDEX TEST_TIMELIMIT ON TEST (TIMELIMIT);
CREATE INDEX TEST_TYPE_ ON TEST (TYPE);
CREATE INDEX TESTCOUNT_CID ON TESTCOUNT (CID);
CREATE INDEX TESTCOUNT_LAST ON TESTCOUNT (LAST);
CREATE INDEX TESTCOUNT_LESSON_ID ON TESTCOUNT (LESSON_ID);
CREATE INDEX TESTCOUNT_MID ON TESTCOUNT (MID);
CREATE INDEX TESTCOUNT_QTY ON TESTCOUNT (QTY);
CREATE INDEX TESTCOUNT_TID ON TESTCOUNT (TID);
CREATE INDEX TESTS_QUESTIONS_SUBJECT_ID ON TESTS_QUESTIONS (SUBJECT_ID);
CREATE INDEX TESTS_QUESTIONS_TEST_ID ON TESTS_QUESTIONS (TEST_ID);
CREATE INDEX UPDATES_CREATED_BY ON UPDATES (CREATED_BY);
CREATE INDEX USER_LOGIN_LOG_EVENT_TYPE ON USER_LOGIN_LOG (EVENT_TYPE);
CREATE INDEX USER_LOGIN_LOG_IP ON USER_LOGIN_LOG (IP);
CREATE INDEX USER_LOGIN_LOG_STATUS ON USER_LOGIN_LOG (STATUS);
CREATE INDEX VIDEO_CREATED ON VIDEO (CREATED);
CREATE INDEX VIDEO_MAIN_VIDEO ON VIDEO (MAIN_VIDEO);
CREATE INDEX VIDEOCHAT_USERS_USERID ON VIDEOCHAT_USERS (USERID);
CREATE INDEX WEBINAR_ANSWERS_QID ON WEBINAR_ANSWERS (QID);
CREATE INDEX WEBINAR_CHAT_POINTID ON WEBINAR_CHAT (POINTID);
CREATE INDEX WEBINAR_CHAT_USERID ON WEBINAR_CHAT (USERID);
CREATE INDEX WEBINAR_FILES_FILE_ID ON WEBINAR_FILES (FILE_ID);
CREATE INDEX WEBINAR_FILES_NUM ON WEBINAR_FILES (NUM);
CREATE INDEX WEBINAR_FILES_WEBINAR_ID ON WEBINAR_FILES (WEBINAR_ID);
CREATE INDEX WEBINAR_HISTORY_POINTID ON WEBINAR_HISTORY (POINTID);
CREATE INDEX WEBINAR_HISTORY_USERID ON WEBINAR_HISTORY (USERID);
CREATE INDEX WEBINAR_PLAN_BID ON WEBINAR_PLAN (BID);
CREATE INDEX WEBINAR_PLAN_POINTID ON WEBINAR_PLAN (POINTID);
CREATE INDEX WEBINAR_QUESTIONS_IS_VOTED ON WEBINAR_QUESTIONS (IS_VOTED);
CREATE INDEX WEBINAR_QUESTIONS_POINT_ID ON WEBINAR_QUESTIONS (POINT_ID);
CREATE INDEX WEBINAR_QUESTIONS_TYPE_ ON WEBINAR_QUESTIONS (TYPE);
CREATE INDEX WEBINAR_RECORDS_SUBJECT_ID ON WEBINAR_RECORDS (SUBJECT_ID);
CREATE INDEX WEBINAR_RECORDS_WEBINAR_ID ON WEBINAR_RECORDS (WEBINAR_ID);
CREATE INDEX WEBINAR_USERS_POINTID ON WEBINAR_USERS (POINTID);
CREATE INDEX WEBINAR_USERS_USERID ON WEBINAR_USERS (USERID);
CREATE INDEX WEBINAR_VOTES_AID ON WEBINAR_VOTES (AID);
CREATE INDEX WEBINAR_VOTES_QID ON WEBINAR_VOTES (QID);
CREATE INDEX WEBINAR_VOTES_USER_ID ON WEBINAR_VOTES (USER_ID);
CREATE INDEX WEBINAR_WHITEBOARD_COLOR ON WEBINAR_WHITEBOARD (COLOR);
CREATE INDEX WEBINAR_WHITEBOARD_HEIGHT ON WEBINAR_WHITEBOARD (HEIGHT);
CREATE INDEX WEBINAR_WHITEBOARD_POINTID ON WEBINAR_WHITEBOARD (POINTID);
CREATE INDEX WEBINAR_WHITEBOARD_POINTS_X ON WEBINAR_WHITEBOARD_POINTS (X);
CREATE INDEX WEBINAR_WHITEBOARD_POINTS_Y ON WEBINAR_WHITEBOARD_POINTS (Y);
CREATE INDEX WEBINAR_WHITEBOARD_TOOL ON WEBINAR_WHITEBOARD (TOOL);
CREATE INDEX WEBINAR_WHITEBOARD_USERID ON WEBINAR_WHITEBOARD (USERID);
CREATE INDEX WEBINAR_WHITEBOARD_WIDTH ON WEBINAR_WHITEBOARD (WIDTH);
CREATE INDEX WEBINARS_SUBJECT_ID ON WEBINARS (SUBJECT_ID);
CREATE INDEX WIKI_ARCHIVE_ARTICLE_ID ON WIKI_ARCHIVE (ARTICLE_ID);
CREATE INDEX WIKI_ARCHIVE_AUTHOR ON WIKI_ARCHIVE (AUTHOR);
CREATE INDEX WIKI_ARTICLES_LESSON_ID ON WIKI_ARTICLES (LESSON_ID);
CREATE INDEX WIKI_ARTICLES_SUBJECT_ID ON WIKI_ARTICLES (SUBJECT_ID);
CREATE INDEX WP_CURRENT_CURRENTITEM ON WEBINAR_PLAN_CURRENT (CURRENTITEM);
CREATE INDEX WW_POINTS_ACTIONID ON WEBINAR_WHITEBOARD_POINTS (ACTIONID);
CREATE INDEX WW_POINTS_TYPE_ ON WEBINAR_WHITEBOARD_POINTS (TYPE);
CREATE INDEX QUEST_ATTEMPTS_USER_ID ON QUEST_ATTEMPTS (USER_ID);
CREATE INDEX QUEST_ATTEMPTS_QUEST_ID ON QUEST_ATTEMPTS (QUEST_ID);
CREATE INDEX QUESTIONNAIRES_QUEST_ID_TYPE ON QUESTIONNAIRES (QUEST_ID, TYPE);
CREATE INDEX QUESTIONNAIRES_TYPE ON QUESTIONNAIRES (TYPE);
CREATE INDEX SUBJECTS_QUESTS_SUBJECT_ID ON SUBJECTS_QUESTS (SUBJECT_ID);
CREATE INDEX SUBJECTS_QUESTS_QUEST_ID ON SUBJECTS_QUESTS (QUEST_ID);
CREATE INDEX SCHEDULE_PARAMS ON SCHEDULE (PARAMS);
CREATE INDEX ES_EVENT_TYPES_NAME ON ES_EVENT_TYPES (NAME);
CREATE INDEX ES_EVENT_TYPES_NAME_EVENT_GROUP_TYPE_ID ON ES_EVENT_TYPES (NAME, EVENT_GROUP_TYPE_ID);
CREATE INDEX PROGRAMM_USERS_USER_ID ON PROGRAMM_USERS (USER_ID);
CREATE INDEX PROGRAMM_USERS_PROGRAMM_ID ON PROGRAMM_USERS (PROGRAMM_ID);
CREATE INDEX STUDY_GROUPS_AUTO_DEPARTMENT_ID ON STUDY_GROUPS_AUTO (DEPARTMENT_ID);
CREATE INDEX STUDY_GROUPS_AUTO_GROUP_ID ON STUDY_GROUPS_AUTO (GROUP_ID);
CREATE INDEX ES_EVENTS_EVENT_TYPE_ID ON ES_EVENTS (EVENT_TYPE_ID);
CREATE INDEX ES_EVENTS_EVENT_GROUP_ID ON ES_EVENTS (EVENT_GROUP_ID);
CREATE INDEX ES_EVENT_USERS_EVENT_ID ON ES_EVENT_USERS (EVENT_ID);
CREATE INDEX ES_EVENT_USERS_USER_ID ON ES_EVENT_USERS (USER_ID);
CREATE INDEX ES_EVENT_USERS_VIEWS ON ES_EVENT_USERS (VIEWS);
CREATE INDEX ES_EVENT_USERS_VIEWS_USER_ID ON ES_EVENT_USERS (VIEWS, USER_ID);
CREATE INDEX mail_queue_created ON mail_queue (created);
GO

-- Заместитель руководителя
CREATE TABLE [deputy_assign] (
	[assign_id] INT PRIMARY KEY IDENTITY(1,1),
	[user_id] INT,
	[deputy_user_id] INT,
	[begin_date] DATETIME,
	[end_date] DATETIME,
	[not_active] bit default 0
);
GO

CREATE INDEX [IX__DEPUTY_ASSIGN__USER_ID] ON [deputy_assign]([user_id]);
CREATE INDEX [IX__DEPUTY_ASSIGN__DEPUTY_USER_ID] ON [deputy_assign]([deputy_user_id]);
CREATE INDEX [IX__DEPUTY_ASSIGN__BEGIN_DATE] ON [deputy_assign]([begin_date]);
CREATE INDEX [IX__DEPUTY_ASSIGN__END_DATE] ON [deputy_assign]([end_date]);
CREATE INDEX [IX__DEPUTY_ASSIGN__NOT_ACTIVE] ON [deputy_assign]([not_active]);

GO