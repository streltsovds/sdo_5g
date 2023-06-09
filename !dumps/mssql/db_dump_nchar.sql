SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Knigi](
    [KID] [int] IDENTITY(1,1) NOT NULL,
    [CID] [int] NOT NULL CONSTRAINT [DF__Knigi__CID__09A971A2]  DEFAULT ((0)),
    [Name] [ntext] NOT NULL CONSTRAINT [DF__Knigi__Name__7E6CC920]  DEFAULT (''),
    [Author] [ntext] NOT NULL CONSTRAINT [DF__Knigi__Author__7F60ED59]  DEFAULT (''),
    [Izdatel] [ntext] NOT NULL CONSTRAINT [DF__Knigi__Izdatel__00551192]  DEFAULT (''),
    [Year] [int] NOT NULL CONSTRAINT [DF__Knigi__Year__0A9D95DB]  DEFAULT ((0)),
    [Description] [ntext] NOT NULL CONSTRAINT [DF__Knigi__Descripti__014935CB]  DEFAULT (''),
    [Url] [ntext] NOT NULL CONSTRAINT [DF__Knigi__Url__023D5A04]  DEFAULT (''),
    [access] [int] NOT NULL CONSTRAINT [DF__Knigi__access__0B91BA14]  DEFAULT ((0)),
    [file_ext] [nvarchar](10) NULL CONSTRAINT [DF__Knigi__file_ext__0C85DE4D]  DEFAULT (NULL),
    [file_active] [int] NOT NULL CONSTRAINT [DF__Knigi__file_acti__0D7A0286]  DEFAULT ((0)),
 CONSTRAINT [PK_Knigi] PRIMARY KEY CLUSTERED
(
    [KID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OPTIONS]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OPTIONS](
    [OptionID] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__OPTIONS__name__634EBE90]  DEFAULT (''),
    [value] [ntext] NOT NULL CONSTRAINT [DF__OPTIONS__value__0425A276]  DEFAULT (''),
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
    [password] [nvarchar](255) NOT NULL CONSTRAINT [DF__password__7DEDA635]  DEFAULT (('')),
    [change_date] [datetime]
) ON [PRIMARY]    
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[People]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[People](
    [MID] [int] IDENTITY(1,1) NOT NULL,
    [mid_external] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__mid_exte__6FB49575]  DEFAULT (''),
    [LastName] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__LastName__70A8B9AE]  DEFAULT (''),
    [FirstName] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__FirstNam__719CDDE7]  DEFAULT (''),
    [LastNameLat] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__LastNameLat__70A8B9AE]  DEFAULT (''),
    [FirstNameLat] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__FirstNamLat__719CDDE7]  DEFAULT (''),
    [Patronymic] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__Patronym__72910220]  DEFAULT (''),
    [Registered] [datetime],
    [Course] [int] NOT NULL CONSTRAINT [DF__People__Course__74794A92]  DEFAULT ((0)),
    [EMail] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__EMail__756D6ECB]  DEFAULT (''),
    [Phone] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__Phone__76619304]  DEFAULT (''),
    [Information] [ntext] NOT NULL CONSTRAINT [DF__People__Informat__060DEAE8]  DEFAULT (''),
    [Address] [ntext] NOT NULL CONSTRAINT [DF__People__Address__07020F21]  DEFAULT (''),
    [Fax] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__Fax__7755B73D]  DEFAULT (''),
    [Login] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__Login__7849DB76]  DEFAULT (''),
    [Password] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__Password__793DFFAF]  DEFAULT (''),
    [javapassword] [nvarchar](20) NOT NULL CONSTRAINT [DF__People__javapass__7A3223E8]  DEFAULT (''),
    [BirthDate] [datetime] NOT NULL CONSTRAINT [DF__People__BirthDat__7B264821]  DEFAULT ((0)),
    [CellularNumber] [nvarchar](255) NOT NULL CONSTRAINT [DF__People__Cellular__7C1A6C5A]  DEFAULT (''),
    [ICQNumber] [int] NOT NULL CONSTRAINT [DF__People__ICQNumbe__7D0E9093]  DEFAULT ((0)),
    [Gender] [int] NOT NULL CONSTRAINT [DF__People__Age__7E02B4CC]  DEFAULT ((0)),
    [last] [bigint] NOT NULL CONSTRAINT [DF__People__last__7EF6D905]  DEFAULT ((0)),
    [countlogin] [int] NOT NULL CONSTRAINT [DF__People__countlog__7FEAFD3E]  DEFAULT ((0)),
    [rnid] [int] NOT NULL CONSTRAINT [DF__People__rnid__00DF2177]  DEFAULT ((0)),
    [Position] [nvarchar](128) NOT NULL CONSTRAINT [DF__People__Position__01D345B0]  DEFAULT (''),
    [PositionDate] [datetime] NOT NULL CONSTRAINT [DF__People__Position__02C769E9]  DEFAULT ((0)),
    [PositionPrev] [nvarchar](128) NOT NULL CONSTRAINT [DF__People__Position__03BB8E22]  DEFAULT (''),
    [invalid_login] [int] NOT NULL CONSTRAINT [DF__People__invalid___04AFB25B]  DEFAULT ((0)),
    [isAD] [int] NULL CONSTRAINT [DF__People__isAD__05A3D694]  DEFAULT ((0)),
    [polls] [image] NULL,
    [Access_Level] [int] NOT NULL CONSTRAINT [DF__People__Access_L__0697FACD]  DEFAULT ('1'),
    [rang] [int] NOT NULL CONSTRAINT [DF__People__rang__078C1F06]  DEFAULT ((0)),
    [preferred_lang] [int] NOT NULL CONSTRAINT [DF__People__preferre__0880433F]  DEFAULT ((0)),
    [blocked] [int] NOT NULL CONSTRAINT [DF__People__blocked__0880433F]  DEFAULT ((0)),
    [block_message] [ntext] NULL,
    [head_mid] [int] NULL CONSTRAINT [DF__People__mid_head__05A3D694]  DEFAULT ((0)),
    [head_mid_external] [nvarchar](255) NULL CONSTRAINT [DF__People__mid_head_external__05A3D694]  DEFAULT ((0)),
    [force_password] [int] NULL CONSTRAINT [DF__People__force_password__05A3D694]  DEFAULT ((0)),
    [lang] [nvarchar](3) NOT NULL CONSTRAINT [DF_People_lang_05A3D694] DEFAULT ('rus'),    
    [need_edit] [int] NOT NULL CONSTRAINT [DF__People__need_edit__0880433F]  DEFAULT ((0)),
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
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__processes__name__7908F585]  DEFAULT (''),
	[chain] [ntext] NOT NULL CONSTRAINT [DF__processes__chain__07020F21]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__processes__type__7720AD13]  DEFAULT ((0)),
 CONSTRAINT [PK_processes] PRIMARY KEY CLUSTERED
(
    [process_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO



/****** Object:  Table [dbo].[programm]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm](
    [programm_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__programm__name__7908F585]  DEFAULT (''),
 CONSTRAINT [PK_programm] PRIMARY KEY CLUSTERED
(
    [programm_id] ASC
) ON [PRIMARY]
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
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__programm_events__name__7908F585]  DEFAULT (''),
	[type] [int] NOT NULL CONSTRAINT [DF__programm_events_type__7814D14C]  DEFAULT ('0'),
	[item_id] [int] NOT NULL CONSTRAINT [DF__programm_events_item_id__7814D14C]  DEFAULT ('0'),
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
    [programm_event_id] NOT NULL CONSTRAINT [DF__programm_events_users_event_id__7814D14C]  DEFAULT ('0'),
	[user_id] [int] NOT NULL CONSTRAINT [DF__programm_events_user_id__7814D14C]  DEFAULT ('0'),
	[begin_date] [datetime] NULL,
	[end_date] [datetime] NULL,
	[status] [int] NOT NULL CONSTRAINT [DF__programm_events_status__7814D14C]  DEFAULT ('0'),
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[programm_users]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[programm_users](
    [programm_id] [int] NOT NULL CONSTRAINT [DF__programm_users_programm_id__7814D14C]  DEFAULT ('0'),
	[user_id] [int] NOT NULL CONSTRAINT [DF__programm_users_user_id_7814D14C]  DEFAULT ('0'),
    [assign_date] [datetime] NULL,
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
    [offline_course_path] [nvarchar](255) NOT NULL CONSTRAINT [DF__Students__offlin__7908F585]  DEFAULT (''),
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
    [current_state] [nvarchar](255) NOT NULL CONSTRAINT [DF__state_of_process__current_state__7908F585]  DEFAULT (''),
	[status] [int] NOT NULL CONSTRAINT [DF__state_of_process__status__7720AD13]  DEFAULT ((0)),
	[params] [ntext] NOT NULL CONSTRAINT [DF__state_of_process__params__07020F21]  DEFAULT (''),
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
    [message] [nvarchar](255) NULL CONSTRAINT [DF__webinar_c__messa__7EE1CA6C]  DEFAULT (''),
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
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__webinars__name__7DEDA635]  DEFAULT (('')),
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
/****** Object:  Table [dbo].[TestContent]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TestContent](
    [QID] [int] IDENTITY(1,1) NOT NULL,
    [TID] [int] NOT NULL CONSTRAINT [DF__TestContent__TID__1D4655FB]  DEFAULT ((0)),
    [xmlQ] [ntext] NOT NULL CONSTRAINT [DF__TestConten__xmlQ__0AD2A005]  DEFAULT (''),
    [questiotext] [ntext] NOT NULL CONSTRAINT [DF__TestConte__quest__0BC6C43E]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__TestConten__type__1E3A7A34]  DEFAULT ((0)),
    [attachFileName] [nvarchar](255) NULL CONSTRAINT [DF__TestConte__attac__1F2E9E6D]  DEFAULT (NULL),
    [attachExt] [nvarchar](255) NULL CONSTRAINT [DF__TestConte__attac__2022C2A6]  DEFAULT (NULL),
    [theme] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestConte__theme__2116E6DF]  DEFAULT (''),
    [isObligatory] [int] NOT NULL CONSTRAINT [DF__TestConte__isObl__220B0B18]  DEFAULT ((0)),
 CONSTRAINT [PK_TestContent] PRIMARY KEY CLUSTERED
(
    [QID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TestTitle]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TestTitle](
    [TID] [int] IDENTITY(1,1) NOT NULL,
    [Title] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestTitle__Title__2F650636]  DEFAULT (''),
    [CID] [int] NOT NULL CONSTRAINT [DF__TestTitle__CID__30592A6F]  DEFAULT ((0)),
    [timelim] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestTitle__timel__314D4EA8]  DEFAULT (''),
    [blockQ] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestTitle__block__324172E1]  DEFAULT (''),
    [trylimit] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestTitle__tryli__3335971A]  DEFAULT (''),
    [orderQ] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestTitle__order__3429BB53]  DEFAULT (''),
    [questlim] [nvarchar](255) NOT NULL CONSTRAINT [DF__TestTitle__quest__351DDF8C]  DEFAULT (''),
 CONSTRAINT [PK_TestTitle] PRIMARY KEY CLUSTERED
(
    [TID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[aaa]    Script Date: 04/20/2010 17:50:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[aaa](
    [aaa] [int] NOT NULL CONSTRAINT [DF__aaa__aaa__76CBA758]  DEFAULT ((0))
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[actions]    Script Date: 04/20/2010 17:50:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[actions](
    [acid] [nvarchar](8) NOT NULL,
    [name] [nvarchar](32) NOT NULL CONSTRAINT [DF__actions__name__78B3EFCA]  DEFAULT (''),
    [url] [nvarchar](64) NOT NULL CONSTRAINT [DF__actions__url__79A81403]  DEFAULT (''),
    [title] [nvarchar](128) NOT NULL CONSTRAINT [DF__actions__title__7A9C383C]  DEFAULT (''),
    [sequence] [int] NOT NULL CONSTRAINT [DF__actions__sequenc__7B905C75]  DEFAULT ((0)),
    [type] [nvarchar](255) NULL CONSTRAINT [DF__actions__type__7C8480AE]  DEFAULT ('dean'),
 CONSTRAINT [PK_actions] PRIMARY KEY CLUSTERED
(
    [acid] ASC
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
/****** Object:  Table [dbo].[alt_mark]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[alt_mark](
    [id] [int] NULL CONSTRAINT [DF__alt_mark__id__00551192]  DEFAULT (NULL),
    [int] [int] NULL CONSTRAINT [DF__alt_mark__int__014935CB]  DEFAULT (NULL),
    [char] [nchar](2) NULL CONSTRAINT [DF__alt_mark__char__023D5A04]  DEFAULT (NULL)
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO

/****** Object:  Table [dbo].[at_managers]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[at_managers](
    [at_manager_id] [int] NOT NULL CONSTRAINT [DF__at_managers__mid__45544755]  DEFAULT ((0)),
    [user_id] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_at_managers] PRIMARY KEY CLUSTERED
(
    [at_manager_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[blog]
(
  [id]         INT IDENTITY(1,1) NOT NULL,
  [title]      [nvarchar](255),
  [body]       [ntext] NOT NULL,
  [created]    [DATETIME] NOT NULL,
  [created_by] [INT] NOT NULL,
  [subject_name] [nvarchar](255),
  [subject_id] [INT] NOT NULL,
  CONSTRAINT blog_pk PRIMARY KEY (id)
)
GO
/****** Object:  Table [dbo].[cam_casting]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cam_casting](
    [castID] [int] IDENTITY(1,1) NOT NULL,
    [cam_key] [nvarchar](32) NOT NULL CONSTRAINT [DF__cam_casti__cam_k__0425A276]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__cam_casting__CID__0519C6AF]  DEFAULT ((0)),
    [MID] [int] NOT NULL CONSTRAINT [DF__cam_casting__MID__060DEAE8]  DEFAULT ((0)),
    [SHEID] [int] NOT NULL CONSTRAINT [DF__cam_casti__SHEID__07020F21]  DEFAULT ((0)),
    [FILE] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_cam_casting] ON [dbo].[cam_casting]
(
    [castID] ASC,
    [cam_key] ASC
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_cam_casting_1] ON [dbo].[cam_casting]
(
    [castID] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[cgname]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cgname](
    [cgid] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__cgname__name__08EA5793]  DEFAULT ('')
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_cgname] ON [dbo].[cgname]
(
    [cgid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[chain]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chain](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__chain__name__24B26D99]  DEFAULT (''),
    [order] [int] NOT NULL CONSTRAINT [DF__chain__order__25A691D2]  DEFAULT ((0)),
 CONSTRAINT [PK_chain] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[chain_agreement]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chain_agreement](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [cid] [int] NOT NULL CONSTRAINT [DF__chain_agree__cid__2C538F61]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__chain_agree__mid__2D47B39A]  DEFAULT ((0)),
    [subject] [int] NOT NULL CONSTRAINT [DF__chain_agr__subje__2E3BD7D3]  DEFAULT ((0)),
    [object] [int] NOT NULL CONSTRAINT [DF__chain_agr__objec__2F2FFC0C]  DEFAULT ((0)),
    [place] [int] NOT NULL CONSTRAINT [DF__chain_agr__place__30242045]  DEFAULT ((0)),
    [comment] [nvarchar](255) NOT NULL CONSTRAINT [DF__chain_agr__comme__3118447E]  DEFAULT (''),
    [date] [datetime] NOT NULL CONSTRAINT [DF__chain_agre__date__320C68B7]  DEFAULT ((0)),
 CONSTRAINT [PK_chain_agreement] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[chain_item]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chain_item](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [chain] [int] NOT NULL CONSTRAINT [DF__chain_ite__chain__278EDA44]  DEFAULT ((0)),
    [item] [int] NOT NULL CONSTRAINT [DF__chain_item__item__2882FE7D]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__chain_item__type__297722B6]  DEFAULT ((0)),
    [place] [int] NOT NULL CONSTRAINT [DF__chain_ite__place__2A6B46EF]  DEFAULT ((0)),
 CONSTRAINT [PK_chain_item] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[chat]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat](
    [CHID] [int] IDENTITY(1,1) NOT NULL,
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF__chat__title__0AD2A005]  DEFAULT (''),
    [type] [int] NULL CONSTRAINT [DF__chat__type__0BC6C43E]  DEFAULT ((0)),
    [CID] [int] NULL CONSTRAINT [DF__chat__CID__0CBAE877]  DEFAULT ((0)),
 CONSTRAINT [PK_chat] PRIMARY KEY CLUSTERED
(
    [CHID] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[chat_messages]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat_messages](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [rid] [nvarchar](255) NOT NULL CONSTRAINT [DF__chat_messag__rid__0EA330E9]  DEFAULT (''),
    [cid] [int] NOT NULL CONSTRAINT [DF__chat_messag__cid__0F975522]  DEFAULT ((0)),
    [uid] [int] NOT NULL CONSTRAINT [DF__chat_messag__uid__108B795B]  DEFAULT ((0)),
    [message] [ntext] NULL,
    [posted] [int] NOT NULL CONSTRAINT [DF__chat_mess__poste__117F9D94]  DEFAULT ((0)),
    [user] [nvarchar](255) NULL CONSTRAINT [DF__chat_messa__user__1273C1CD]  DEFAULT (NULL),
    [sendto] [int] NULL,
    [sheid] [int] NOT NULL CONSTRAINT [DF__chat_messag_sheid] DEFAULT ((0)),
 CONSTRAINT [PK_chat_messages] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[chat_users]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat_users](
    [uid] [int] NULL CONSTRAINT [DF__chat_users__uid__145C0A3F]  DEFAULT (NULL),
    [rid] [nvarchar](255) NOT NULL CONSTRAINT [DF__chat_users__rid__15502E78]  DEFAULT (''),
    [cid] [int] NOT NULL CONSTRAINT [DF__chat_users__cid__164452B1]  DEFAULT ((0)),
    [joined] [int] NULL CONSTRAINT [DF__chat_user__joine__173876EA]  DEFAULT (NULL),
    [user] [nvarchar](255) NULL CONSTRAINT [DF__chat_users__user__182C9B23]  DEFAULT (NULL)
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_chat_users] ON [dbo].[chat_users]
(
    [rid] ASC,
    [uid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[comp2course]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[comp2course](
    [ccoid] [int] IDENTITY(1,1) NOT NULL,
    [cid] [int] NULL CONSTRAINT [DF__comp2course__cid__21B6055D]  DEFAULT (NULL),
    [tid] [int] NULL CONSTRAINT [DF__comp2course__tid__22AA2996]  DEFAULT (NULL),
    [coid] [int] NULL CONSTRAINT [DF__comp2cours__coid__239E4DCF]  DEFAULT (NULL),
    [level] [int] NULL CONSTRAINT [DF__comp2cour__level__24927208]  DEFAULT (NULL),
 CONSTRAINT [PK_comp2course] PRIMARY KEY CLUSTERED
(
    [ccoid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[competence]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[competence](
    [coid] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__competence__name__267ABA7A]  DEFAULT (NULL),
    [type] [int] NULL CONSTRAINT [DF__competence__type__276EDEB3]  DEFAULT (NULL),
    [level] [int] NULL CONSTRAINT [DF__competenc__level__286302EC]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__competenc__statu__29572725]  DEFAULT (NULL),
    [info] [ntext] NULL,
 CONSTRAINT [PK_competence] PRIMARY KEY CLUSTERED
(
    [coid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[competence_roles]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[competence_roles](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__competence__name__451F3D2B]  DEFAULT (''),
    [formula] [int] NOT NULL CONSTRAINT [DF__competenc__formu__46136164]  DEFAULT ((0)),
    [dynamic] [int] NOT NULL CONSTRAINT [DF__competenc__dyn__46136164]  DEFAULT ((0)),
    [courses] [int] NOT NULL CONSTRAINT [DF__competenc__course__46136164]  DEFAULT ((0)),
 CONSTRAINT [PK_competence_roles] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[competence_roles_competences]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[competence_roles_competences](
    [role] [int] NOT NULL CONSTRAINT [DF__competence__role__47FBA9D6]  DEFAULT ((0)),
    [competence] [int] NOT NULL CONSTRAINT [DF__competenc__compe__48EFCE0F]  DEFAULT ((0)),
    [threshold] [float] NOT NULL CONSTRAINT [DF__competenc__thres__49E3F248]  DEFAULT ((0)),
    [task] [int] NOT NULL CONSTRAINT [DF__competenc__task__46136164]  DEFAULT ((0)),
 CONSTRAINT [PK_competence_roles_competences] PRIMARY KEY CLUSTERED
(
    [role] ASC,
    [competence] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[conf_cid]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[conf_cid](
    [cid] [int] NOT NULL CONSTRAINT [DF__conf_cid__cid__2B3F6F97]  DEFAULT ((0)),
    [autoindex] [int] NOT NULL CONSTRAINT [DF__conf_cid__autoin__2C3393D0]  DEFAULT ((0)),
 CONSTRAINT [PK_conf_cid] PRIMARY KEY CLUSTERED
(
    [cid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[course2group]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[course2group](
    [cid] [int] NULL CONSTRAINT [DF__course2grou__cid__2E1BDC42]  DEFAULT (NULL),
    [gid] [int] NULL CONSTRAINT [DF__course2grou__gid__2F10007B]  DEFAULT (NULL),
    [cgid] [int] NULL CONSTRAINT [DF__course2gro__cgid__300424B4]  DEFAULT (NULL)
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[courses_marks]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[courses_marks](
    [cid] [int] NOT NULL CONSTRAINT [DF__courses_mar__cid__1D114BD1]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__courses_mar__mid__1E05700A]  DEFAULT ((0)),
    [mark] [nvarchar](255) NOT NULL CONSTRAINT [DF__courses_ma__mark__1EF99443]  DEFAULT (''),
    [alias] [nvarchar](255) NOT NULL CONSTRAINT [DF__courses_m__alias__1FEDB87C]  DEFAULT (''),
    [comments] [ntext] NOT NULL CONSTRAINT [DF__courses__mark_comment___2B3F6F97]  DEFAULT (''),
 CONSTRAINT [PK_courses_marks] PRIMARY KEY CLUSTERED
(
    [cid] ASC,
    [mid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[cycles]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[cycles](
    [cycle_id]  [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__cycles__name__47DBAE44]  DEFAULT (NULL),
	[begin_date] [date] NOT NULL,
	[end_date] [date] NOT NULL,
 CONSTRAINT [PK_cycles] PRIMARY KEY CLUSTERED
(
    [cycle_id] ASC
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
CREATE NONCLUSTERED INDEX [IX_deans_MID] ON [dbo].[deans]
(
    [MID] ASC
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

/****** Object:  Table [dbo].[departments]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments](
    [did] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__department__name__47DBAE45]  DEFAULT (NULL),
    [mid] [int] NOT NULL CONSTRAINT [DF__departments__mid__48CFD27E]  DEFAULT ((0)),
    [info] [image] NULL,
    [color] [nvarchar](255) NULL CONSTRAINT [DF__departmen__color__49C3F6B7]  DEFAULT (NULL),
    [owner_did] [int] NULL CONSTRAINT [DF__departmen__owner__4AB81AF0]  DEFAULT (NULL),
    [not_in] [int] NOT NULL CONSTRAINT [DF__departmen__not_i__4BAC3F29]  DEFAULT ((0)),
    [application] [int] NOT NULL CONSTRAINT [DF__departmen__app__4BAC3F29]  DEFAULT ((0)),
 CONSTRAINT [PK_departments] PRIMARY KEY CLUSTERED
(
    [did] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_departments_app] ON [dbo].[departments]
(
    [application] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_departments_mid] ON [dbo].[departments]
(
    [mid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[departments_courses]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments_courses](
    [did] [int] NOT NULL,
    [cid] [int] NOT NULL,
 CONSTRAINT [PK_departments_courses] PRIMARY KEY CLUSTERED
(
    [did] ASC,
    [cid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[departments_groups]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments_groups](
    [did] [int] NOT NULL CONSTRAINT [DF__departments__did__21D600EE]  DEFAULT ((0)),
    [gid] [int] NOT NULL CONSTRAINT [DF__departments__gid__22CA2527]  DEFAULT ((0)),
 CONSTRAINT [PK_departments_groups] PRIMARY KEY CLUSTERED
(
    [did] ASC,
    [gid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[departments_soids]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments_soids](
    [did] [int] NOT NULL CONSTRAINT [DF__departments__did__1A34DF26]  DEFAULT ((0)),
    [soid] [int] NOT NULL CONSTRAINT [DF__department__soid__1B29035F]  DEFAULT ((0)),
 CONSTRAINT [PK_departments_soids] PRIMARY KEY CLUSTERED
(
    [did] ASC,
    [soid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[departments_tracks]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments_tracks](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [did] [int] NOT NULL CONSTRAINT [DF__departments__did__37C5420D]  DEFAULT ((0)),
    [track] [int] NOT NULL CONSTRAINT [DF__departmen__track__38B96646]  DEFAULT ((0)),
 CONSTRAINT [PK_departments_tracks] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[eventtools_weight]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[eventtools_weight](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [event] [int] NOT NULL CONSTRAINT [DF__eventtool__event__33F4B129]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__eventtools___cid__34E8D562]  DEFAULT ((0)),
    [weight] [float] NOT NULL CONSTRAINT [DF__eventtool__weigh__35DCF99B]  DEFAULT ((0)),
 CONSTRAINT [PK_eventtools_weight] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[faq](
    [faq_id] [int] IDENTITY(1,1) NOT NULL,
    [question] [ntext] NOT NULL CONSTRAINT [DF__faq__question__33F4B129]  DEFAULT (''),
    [answer] [ntext] NOT NULL CONSTRAINT [DF__faq___answer__34E8D562]  DEFAULT (''),
    [roles] [nvarchar](255) NOT NULL CONSTRAINT [DF__faq__roles__35DCF99B]  DEFAULT (''),
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
    [kod] [nvarchar](100) NOT NULL CONSTRAINT [DF__file__kod__5629CD9C]  DEFAULT (''),
    [fnum] [int] NOT NULL CONSTRAINT [DF__file__fnum__571DF1D5]  DEFAULT ((0)),
    [ftype] [int] NOT NULL CONSTRAINT [DF__file__ftype__5812160E]  DEFAULT ((0)),
    [fname] [nvarchar](100) NOT NULL CONSTRAINT [DF__file__fname__59063A47]  DEFAULT (''),
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
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__files__name]  DEFAULT (''),
    [path] [nvarchar](255) NOT NULL CONSTRAINT [DF__files__path]  DEFAULT (''),
    [file_size] [int] NOT NULL CONSTRAINT [DF__files__fy]  DEFAULT ((0)),
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
    [file_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [varchar](255) NOT NULL CONSTRAINT [DF__videoblock__name]  DEFAULT (''),
 CONSTRAINT [PK_videoblock] PRIMARY KEY CLUSTERED
(
    [file_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[file_tranfer]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[file_tranfer](
    [ftID] [int] IDENTITY(1,1) NOT NULL,
    [ft_key] [nvarchar](32) NOT NULL CONSTRAINT [DF__file_tran__ft_ke__5DCAEF64]  DEFAULT (''),
    [ModID] [int] NOT NULL CONSTRAINT [DF__file_tran__ModID__5EBF139D]  DEFAULT ((0)),
    [t_date] [datetime] NOT NULL CONSTRAINT [DF__file_tran__t_dat__5FB337D6]  DEFAULT ((0)),
    [MID] [int] NOT NULL CONSTRAINT [DF__file_tranfe__MID__60A75C0F]  DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_file_tranfer] ON [dbo].[file_tranfer]
(
    [ft_key] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_file_tranfer_1] ON [dbo].[file_tranfer]
(
    [ftID] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[filefoto]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[filefoto](
    [mid] [int] NOT NULL CONSTRAINT [DF__filefoto__mid__628FA481]  DEFAULT ((0)),
    [foto] [image] NOT NULL,
    [last] [int] NOT NULL CONSTRAINT [DF__filefoto__last__6383C8BA]  DEFAULT ((0)),
    [fx] [int] NOT NULL CONSTRAINT [DF__filefoto__fx__6477ECF3]  DEFAULT ((0)),
    [fy] [int] NOT NULL CONSTRAINT [DF__filefoto__fy__656C112C]  DEFAULT ((0)),
 CONSTRAINT [PK_filefoto] PRIMARY KEY CLUSTERED
(
    [mid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[formula]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[formula](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__formula__name__6754599E]  DEFAULT (''),
    [formula] [ntext] NOT NULL CONSTRAINT [DF__formula__formula__2B3F6F97]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__formula__type__68487DD7]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__formula__CID__693CA210]  DEFAULT ((0)),
 CONSTRAINT [PK_formula] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[forumcategories]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forumcategories](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__forumcateg__name__6B24EA82]  DEFAULT (''),
    [cid] [int] NOT NULL CONSTRAINT [DF__forumcate__cid__6C190EBB]  DEFAULT ((0)),
    [create_by] [int] NOT NULL CONSTRAINT [DF__forumcate__creat__6D0D32F4]  DEFAULT ((0)),
    [create_date] [datetime] NULL CONSTRAINT [DF__forumcate__creat__6E01572D]  DEFAULT (NULL),
    [cms] [int] CONSTRAINT [DF__forumcategories_cms] DEFAULT (0) NOT NULL,
 CONSTRAINT [PK_forumcategories] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_forumcat_cms] ON [dbo].[forumcategories]
(
    [cms] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[forummessages]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forummessages](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [thread] [int] NULL CONSTRAINT [DF__forummess__threa__6FE99F9F]  DEFAULT (NULL),
    [posted] [nvarchar](15) NULL CONSTRAINT [DF__forummess__poste__70DDC3D8]  DEFAULT (NULL),
    [icon] [int] NULL CONSTRAINT [DF__forummessa__icon__71D1E811]  DEFAULT (NULL),
    [name] [nvarchar](255) NULL CONSTRAINT [DF__forummessa__name__72C60C4A]  DEFAULT (NULL),
    [email] [nvarchar](255) NULL CONSTRAINT [DF__forummess__email__73BA3083]  DEFAULT (NULL),
    [sendmail] [int] NULL CONSTRAINT [DF__forummess__sendm__74AE54BC]  DEFAULT (NULL),
    [message] [ntext] NULL,
    [is_topic] [int] NULL CONSTRAINT [DF__forummess__is_to__75A278F5]  DEFAULT (NULL),
    [mid] [int] NOT NULL CONSTRAINT [DF__forummessag__mid__76969D2E]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__forummessa__type__778AC167]  DEFAULT ((0)),
    [oid] [int] NOT NULL CONSTRAINT [DF__forummessag__oid__787EE5A0]  DEFAULT ((0)),
    [parent] [int] NOT NULL CONSTRAINT [DF__forummessag__par__787EE5A1]  DEFAULT ((0)),
 CONSTRAINT [PK_forummessages] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[forumthreads]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forumthreads](
    [thread] [int] IDENTITY(1,1) NOT NULL,
    [category] [int] NOT NULL CONSTRAINT [DF__forumthre__categ__7A672E12]  DEFAULT ((0)),
    [course] [nvarchar](100) NULL CONSTRAINT [DF__forumthre__cours__7B5B524B]  DEFAULT (NULL),
    [lastpost] [nvarchar](15) NULL CONSTRAINT [DF__forumthre__lastp__7C4F7684]  DEFAULT (NULL),
    [answers] [int] NULL CONSTRAINT [DF__forumthre__answe__7D439ABD]  DEFAULT ((0)),
    [private] [int] NOT NULL CONSTRAINT [DF__forumthre__priva__7E37BEF6]  DEFAULT ((0)),
 CONSTRAINT [PK_forumthreads] PRIMARY KEY CLUSTERED
(
    [thread] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[glossary]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[glossary](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL,
    [cid] [int] NOT NULL,
    [description] [ntext] NOT NULL CONSTRAINT [DF__glossary__descri__300424B4]  DEFAULT (''),
PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
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
    [score] [nvarchar](200) NULL,
    [progress] [int] NOT NULL CONSTRAINT [DF__graduated__progress__00200768]  DEFAULT ((0)),
    [is_lookable] INT NULL   DEFAULT ((0)),
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
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__groupname__name__03F0984C]  DEFAULT (''),
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
    [message] [ntext] NOT NULL CONSTRAINT [DF__hacp_debu__messa__34C8D9D1]  DEFAULT (''),
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
    [role] [nvarchar](255),
	[app_module] [nvarchar](25),
    [module] [nvarchar](255),
    [controller] [nvarchar](255),
    [action] [nvarchar](255),
    [link_subject] [int] NOT NULL CONSTRAINT [DF__help_link_subject__4336F4B9]  DEFAULT ((0)),
    [link] [nvarchar](255),
    [title] [nvarchar](255),
    [text] [ntext] NOT NULL CONSTRAINT [DF__help_text__34C8D9D1]  DEFAULT (''),
    [lang] [nvarchar](3) NOT NULL CONSTRAINT [DF_help_lang] DEFAULT(''),
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
    [name] [nvarchar](255),
    [text] [ntext] NOT NULL CONSTRAINT [DF__htmlpage_text__34C8D9D1]  DEFAULT (''),
	[url] [nvarchar](255) NOT NULL DEFAULT (''),
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
    [title] [nvarchar](255),
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
    [name] [nvarchar](255),
    [role] [nvarchar](255)
 CONSTRAINT [PK_htmlpage_groups] PRIMARY KEY CLUSTERED
(
    [group_id] ASC
) ON [PRIMARY]
)
GO
/****** Object:  Table [dbo].[laws]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[laws](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [parent] [int] NOT NULL CONSTRAINT [DF__laws__parent__0169315C]  DEFAULT ((0)),
    [categories] [ntext] NOT NULL CONSTRAINT [DF__laws__cateries__36B12243]  DEFAULT (''),
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws__title__025D5595]  DEFAULT (''),
    [initiator] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws__initiator__035179CE]  DEFAULT (''),
    [author] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws__author__04459E07]  DEFAULT (''),
    [annotation] [ntext] NOT NULL CONSTRAINT [DF__laws__annotation__37A5467C]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__laws__type__0539C240]  DEFAULT ((0)),
    [region] [int] NOT NULL CONSTRAINT [DF__laws__region__062DE679]  DEFAULT ((0)),
    [area_of_application] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws__area_of_ap__07220AB2]  DEFAULT (''),
    [create_date] [datetime] NOT NULL CONSTRAINT [DF__laws__create_dat__08162EEB]  DEFAULT ((0)),
    [expire] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws__expire__090A5324]  DEFAULT (''),
    [modify_date] [datetime] NOT NULL CONSTRAINT [DF__laws__modify_dat__09FE775D]  DEFAULT ((0)),
    [edit_reason] [ntext] NOT NULL CONSTRAINT [DF__laws__edit_reaso__38996AB5]  DEFAULT (''),
    [current_version] [int] NOT NULL CONSTRAINT [DF__laws__current_ve__0AF29B96]  DEFAULT ((0)),
    [filename] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws__filename__0BE6BFCF]  DEFAULT (''),
    [upload_date] [datetime] NOT NULL CONSTRAINT [DF__laws__upload_dat__0CDAE408]  DEFAULT ((0)),
    [uploaded_by] [int] NOT NULL CONSTRAINT [DF__laws__uploaded_b__0DCF0841]  DEFAULT ((0)),
    [access_level] [int] NOT NULL CONSTRAINT [DF__laws__access_lev__0EC32C7A]  DEFAULT ((0)),
 CONSTRAINT [PK_laws] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[laws_categories]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[laws_categories](
    [catid] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws_cate__catid__10AB74EC]  DEFAULT (''),
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws_categ__name__119F9925]  DEFAULT (''),
    [parent] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws_cate__paren__1293BD5E]  DEFAULT (''),
 CONSTRAINT [PK_laws_cateries] PRIMARY KEY CLUSTERED
(
    [catid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[laws_index]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[laws_index](
    [id] [int] NOT NULL CONSTRAINT [DF__laws_index__id__147C05D0]  DEFAULT ((0)),
    [word] [int] NOT NULL CONSTRAINT [DF__laws_index__word__15702A09]  DEFAULT ((0)),
    [count] [int] NOT NULL CONSTRAINT [DF__laws_inde__count__16644E42]  DEFAULT ((0)),
 CONSTRAINT [PK_laws_index] PRIMARY KEY CLUSTERED
(
    [id] ASC,
    [word] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[laws_index_words]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[laws_index_words](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [word] [nvarchar](255) NOT NULL CONSTRAINT [DF__laws_index__word__184C96B4]  DEFAULT (''),
 CONSTRAINT [PK_laws_index_words] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
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
    [cats] [ntext] NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__library__mid__6991A7CB]  DEFAULT ((0)),
    [uid] [nvarchar](255) NOT NULL CONSTRAINT [DF__library__uid__6A85CC04]  DEFAULT (''),
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF__library__title__6B79F03D]  DEFAULT (''),
    [author] [nvarchar](255) NOT NULL CONSTRAINT [DF__library__author__6C6E1476]  DEFAULT (''),
    [publisher] [nvarchar](255) NOT NULL CONSTRAINT [DF__library__publish__6D6238AF]  DEFAULT (''),
    [publish_date] [nvarchar](4) NOT NULL CONSTRAINT [DF__library__publish__6E565CE8]  DEFAULT (''),
    [description] [ntext] NOT NULL CONSTRAINT [DF__library__descrip__3D5E1FD2]  DEFAULT (''),
    [keywords] [ntext] NOT NULL CONSTRAINT [DF__library__keyword__3E52440B]  DEFAULT (''),
    [filename] [nvarchar](255) NOT NULL CONSTRAINT [DF__library__filenam__6F4A8121]  DEFAULT (''),
    [location] [nvarchar](255) NOT NULL CONSTRAINT [DF__library__locatio__703EA55A]  DEFAULT (''),
    [metadata] [ntext] NOT NULL CONSTRAINT [DF__library__metadata__75F77EB0]  DEFAULT (''),
    [need_access_level] [int] NOT NULL CONSTRAINT [DF__library__need_ac__7132C993]  DEFAULT ('5'),
    [upload_date] [datetime] NOT NULL CONSTRAINT [DF__library__upload___7226EDCC]  DEFAULT ((0)),
    [is_active_version] [int] NOT NULL CONSTRAINT [DF__library__is_acti__731B1205]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__library__type__740F363E]  DEFAULT ((0)),
    [is_package] [int] NOT NULL CONSTRAINT [DF__library__is_pack__75035A77]  DEFAULT ((0)),
    [quantity] [int] NOT NULL CONSTRAINT [DF__library__quantit__75F77EB0]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__library__cid__75F77EB0]  DEFAULT ((0)),
    [content] [nvarchar](255) NOT NULL CONSTRAINT [DF_library_content]  DEFAULT (''),
    [scorm_params] [ntext] NOT NULL CONSTRAINT [DF_library_scorm_params]  DEFAULT (''),
    [cms] [tinyint] NOT NULL CONSTRAINT [DF_library_cms]  DEFAULT ((0)),
    [pointId] [int] NOT NULL CONSTRAINT [DF_library_pointId]  DEFAULT ((0)),
    [courses] [nvarchar](255) NOT NULL CONSTRAINT [DF_library_courses]  DEFAULT (''),
    [lms] [int] NOT NULL CONSTRAINT [DF_library_lms]  DEFAULT ((0)),
    [place] [nvarchar](255) NOT NULL CONSTRAINT [DF_library_place]  DEFAULT (''),
    [not_moderated] [bit] NOT NULL CONSTRAINT [DF_library_not_moder]  DEFAULT ((0)),
 CONSTRAINT [PK_library] PRIMARY KEY CLUSTERED
(
    [bid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
CREATE NONCLUSTERED INDEX [IX_library_cid] ON [dbo].[library]
(
    [cid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[library_assign]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[library_assign](
    [assid] [int] IDENTITY(1,1) NOT NULL,
    [bid] [int] NOT NULL CONSTRAINT [DF__library_ass__bid__77DFC722]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__library_ass__mid__78D3EB5B]  DEFAULT ((0)),
    [start] [datetime] NOT NULL CONSTRAINT [DF__library_a__start__79C80F94]  DEFAULT ((0)),
    [stop] [datetime] NOT NULL CONSTRAINT [DF__library_as__stop__7ABC33CD]  DEFAULT ((0)),
    [closed] [int] NOT NULL CONSTRAINT [DF__library_a__close__7BB05806]  DEFAULT ((0)),
    [number] [int] NOT NULL CONSTRAINT [DF__library_a__number__7BB05806]  DEFAULT ((1)),
    [type] [tinyint] NOT NULL CONSTRAINT [DF__library_a__type__7BB05806]  DEFAULT ((0)),
 CONSTRAINT [PK_library_assign] PRIMARY KEY CLUSTERED
(
    [assid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[library_categories]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[library_categories](
    [catid] [nvarchar](255) NOT NULL CONSTRAINT [DF__library_c__catid__7D98A078]  DEFAULT (''),
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__library_ca__name__7E8CC4B1]  DEFAULT (''),
    [parent] [nvarchar](255) NOT NULL CONSTRAINT [DF__library_c__paren__7F80E8EA]  DEFAULT (''),
 CONSTRAINT [PK_library_categories] PRIMARY KEY CLUSTERED
(
    [catid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[library_index]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[library_index](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [module] [int] NOT NULL CONSTRAINT [DF__library_index__mod__45544755]  DEFAULT ((0)),
    [file] [nvarchar](255) NOT NULL CONSTRAINT [DF__library_index__file__45544755]  DEFAULT (''),
    [keywords] [ntext] NOT NULL,
 CONSTRAINT [PK_library_index] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_library_index_mod] ON [dbo].[library_index]
(
    [module] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[list]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[list](
    [kod] [nvarchar](100) NOT NULL CONSTRAINT [DF__list__kod__0F624AF8]  DEFAULT (''),
    [qtype] [int] NOT NULL CONSTRAINT [DF__list__qtype__10566F31]  DEFAULT ((0)),
    [qdata] [ntext] NOT NULL CONSTRAINT [DF__list__qdata__4222D4EF]  DEFAULT (''),
    [qtema] [nvarchar](255) NOT NULL CONSTRAINT [DF__list__qtema__114A936A]  DEFAULT (''),
    [qmoder] [int] NOT NULL CONSTRAINT [DF__list__qmoder__123EB7A3]  DEFAULT ((0)),
    [adata] [ntext] NOT NULL CONSTRAINT [DF__list__adata__4316F928]  DEFAULT (''),
    [balmax] [float] NOT NULL CONSTRAINT [DF__list__balmax__1332DBDC]  DEFAULT ((0)),
    [balmin] [float] NOT NULL CONSTRAINT [DF__list__balmin__14270015]  DEFAULT ((0)),
    [url] [ntext] NOT NULL CONSTRAINT [DF__list__url__440B1D61]  DEFAULT (''),
    [last] [int] NOT NULL CONSTRAINT [DF__list__last__151B244E]  DEFAULT ((0)),
    [timelimit] [int] NULL CONSTRAINT [DF__list__timelimit__160F4887]  DEFAULT (NULL),
    [weight] [ntext] NULL,
    [is_shuffled] [int] NULL CONSTRAINT [DF__list__is_shuffle__17036CC0]  DEFAULT ((1)),
    [created_by] [int] NOT NULL CONSTRAINT [DF__list__created_by__17F790F9]  DEFAULT ((0)),
    [timetoanswer] [int] NOT NULL CONSTRAINT [DF__list__timetoans__17F790F9]  DEFAULT ((0)),
    [prepend_test] [nvarchar](255) NOT NULL CONSTRAINT [DF__list__prepent_test__17F790F9]  DEFAULT (''),
    [is_poll] [int] NOT NULL CONSTRAINT [DF__list__ispoll__17F790F9]  DEFAULT ((0)),
    [ordr] [int] NOT NULL CONSTRAINT [DF__list__ordr__17F790F9]  DEFAULT ((10)),    
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
    [kod] [nvarchar](255) NOT NULL CONSTRAINT [DF__logseance__kod__1DB06A4F]  DEFAULT (''),
    [number] [int] NOT NULL CONSTRAINT [DF__logseance__numbe__1EA48E88]  DEFAULT ((0)),
    [time] [int] NOT NULL CONSTRAINT [DF__logseance__time__1F98B2C1]  DEFAULT ((0)),
    [bal] [float] NOT NULL CONSTRAINT [DF__logseance__bal__208CD6FA]  DEFAULT ((0)),
    [balmax] [float] NOT NULL CONSTRAINT [DF__logseance__balma__2180FB33]  DEFAULT ((0)),
    [balmin] [float] NOT NULL CONSTRAINT [DF__logseance__balmi__22751F6C]  DEFAULT ((0)),
    [good] [int] NOT NULL CONSTRAINT [DF__logseance__od__236943A5]  DEFAULT ((0)),
    [vopros] [ntext] NOT NULL CONSTRAINT [DF__logseance__vopros__245D67DE]  DEFAULT (''),
    [otvet] [ntext] NOT NULL CONSTRAINT [DF__logseance__otvet__245D67DE]  DEFAULT (''),
    [attach] [image] NOT NULL,
    [filename] [nvarchar](255) NOT NULL CONSTRAINT [DF__logseance__filen__245D67DE]  DEFAULT (''),
    [text] [ntext] NOT NULL,
    [sheid] [int] NOT NULL CONSTRAINT [DF__logseance__sheid__25518C17]  DEFAULT ((0)),
    [comments] [ntext] NULL,
    [review] [image] NULL,
    [review_filename] [nvarchar](255) DEFAULT (''),
    [qtema] [nvarchar](255) NOT NULL CONSTRAINT [DF__logseance__qtema__114A936A]  DEFAULT (''),
 CONSTRAINT [PK_logseance] PRIMARY KEY CLUSTERED
(
    [stid] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_logseance] ON [dbo].[logseance]
(
    [mid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_logseance_1] ON [dbo].[logseance]
(
    [cid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_logseance_2] ON [dbo].[logseance]
(
    [kod] ASC
) ON [PRIMARY]
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
CREATE NONCLUSTERED INDEX [IX_loguser] ON [dbo].[loguser]
(
    [mid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_loguser_1] ON [dbo].[loguser]
(
    [cid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[like]  ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[like](
    [like_id] [int] IDENTITY(1,1) NOT NULL,
    [item_type] [int] NOT NULL,
    [item_id] [int] NOT NULL,
    [count_like] [int] NOT NULL  DEFAULT ((0)),
    [count_dislike] [int] NOT NULL DEFAULT ((0)),
 CONSTRAINT [PK_like] PRIMARY KEY CLUSTERED
(
    [like_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[like_user]  ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[like_user](
    [like_user_id] [int] IDENTITY(1,1) NOT NULL,
    [item_type] [int] NOT NULL,
    [item_id] [int] NOT NULL,
    [user_id] [int] NOT NULL  DEFAULT ((0)),
    [value] [int] NOT NULL DEFAULT ((0)),
    [date] [datetime] NOT NULL,
 CONSTRAINT [PK_like_user] PRIMARY KEY CLUSTERED
(
    [like_user_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[mod_attempts]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mod_attempts](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [ModID] [int] NOT NULL,
    [mid] [int] NOT NULL,
    [start] [datetime] NOT NULL,
 CONSTRAINT [PK_mod_attempts] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[mod_content]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mod_content](
    [McID] [int] IDENTITY(1,1) NOT NULL,
    [Title] [nvarchar](255) NOT NULL CONSTRAINT [DF__mod_conte__Title__3D2915A8]  DEFAULT (''),
    [ModID] [int] NOT NULL CONSTRAINT [DF__mod_conte__ModID__3E1D39E1]  DEFAULT ((0)),
    [mod_l] [nvarchar](255) NULL CONSTRAINT [DF__mod_conte__mod_l__3F115E1A]  DEFAULT (NULL),
    [type] [nvarchar](40) NOT NULL CONSTRAINT [DF__mod_conten__type__40058253]  DEFAULT ((0)),
    [conttype] [nvarchar](80) NULL CONSTRAINT [DF__mod_conte__contt__40F9A68C]  DEFAULT (NULL),
    [scorm_params] [ntext] NOT NULL CONSTRAINT [DF__mod_conte__scorm__49C3F6B7]  DEFAULT ('')
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_mod_content] ON [dbo].[mod_content]
(
    [McID] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[mod_list]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[mod_list](
    [ModID] [int] IDENTITY(1,1) NOT NULL,
    [Title] [nvarchar](255) NOT NULL CONSTRAINT [DF__mod_list__Title__42E1EEFE]  DEFAULT (''),
    [Num] [nvarchar](40) NOT NULL CONSTRAINT [DF__mod_list__Num__43D61337]  DEFAULT ((0)),
    [Descript] [nvarchar](255) NULL CONSTRAINT [DF__mod_list__Descri__44CA3770]  DEFAULT (NULL),
    [Pub] [int] NOT NULL CONSTRAINT [DF__mod_list__Pub__45BE5BA9]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__mod_list__CID__46B27FE2]  DEFAULT ((0)),
    [PID] [int] NOT NULL CONSTRAINT [DF__mod_list__PID__47A6A41B]  DEFAULT ((0)),
    [forum_id] [int] NULL CONSTRAINT [DF__mod_list__forum___489AC854]  DEFAULT (NULL),
    [test_id] [nvarchar](255) NOT NULL CONSTRAINT [DF__mod_list__test_i__498EEC8D]  DEFAULT (''),
    [run_id] [nvarchar](255) NOT NULL CONSTRAINT [DF__mod_list__run_id__4A8310C6]  DEFAULT ('')
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_mod_list] ON [dbo].[mod_list]
(
    [ModID] ASC
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

/****** Object:  Table [dbo].[money]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[money](
    [moneyid] [int] IDENTITY(1,1) NOT NULL,
    [sum] [int] NULL CONSTRAINT [DF__money__sum__4C6B5938]  DEFAULT (NULL),
    [mid] [int] NULL CONSTRAINT [DF__money__mid__4D5F7D71]  DEFAULT (NULL),
    [trid] [int] NULL CONSTRAINT [DF__money__trid__4E53A1AA]  DEFAULT (NULL),
    [date] [int] NULL CONSTRAINT [DF__money__date__4F47C5E3]  DEFAULT (NULL),
    [type] [int] NULL CONSTRAINT [DF__money__type__503BEA1C]  DEFAULT (NULL),
    [sign] [int] NULL CONSTRAINT [DF__money__sign__51300E55]  DEFAULT (NULL),
    [info] [nvarchar](255) NULL CONSTRAINT [DF__money__info__5224328E]  DEFAULT (NULL),
 CONSTRAINT [PK_money] PRIMARY KEY CLUSTERED
(
    [moneyid] ASC
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
    [event] [nvarchar](255) NULL CONSTRAINT [DF__notice__event__4C6B5938]  DEFAULT (NULL),
    [receiver] [int] NULL CONSTRAINT [DF__notice__receiver__4D5F7D71]  DEFAULT (NULL),
    [title] [nvarchar](255) NULL CONSTRAINT [DF__notice__title__4E53A1AA]  DEFAULT (NULL),
    [message] [ntext] NULL CONSTRAINT [DF__notice__message__4F47C5E3]  DEFAULT (NULL),
    [type] [int],
    [enabled] NOT NULL CONSTRAINT [DF__notice__enabled__540C7B00]  DEFAULT ((1)),
 CONSTRAINT [PK_notice] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[new_news]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[new_news](
    [nID] [int] IDENTITY(1,1) NOT NULL,
    [date] [datetime] NOT NULL CONSTRAINT [DF__new_news__date__540C7B00]  DEFAULT ((0)),
    [autor] [nvarchar](40) NOT NULL CONSTRAINT [DF__new_news__autor__55009F39]  DEFAULT (''),
    [email] [nvarchar](60) NOT NULL CONSTRAINT [DF__new_news__email__55F4C372]  DEFAULT (''),
    [Title] [nvarchar](120) NOT NULL CONSTRAINT [DF__new_news__Title__56E8E7AB]  DEFAULT (''),
    [news] [ntext] NOT NULL CONSTRAINT [DF__new_news__news__4D94879B]  DEFAULT (''),
    [perm] [int] NOT NULL CONSTRAINT [DF__new_news__perm__57DD0BE4]  DEFAULT ((0)),
    [inarhiv] [int] NOT NULL CONSTRAINT [DF__new_news__inarhi__58D1301D]  DEFAULT ((0)),
    [image] [image] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_new_news] ON [dbo].[new_news]
(
    [nID] ASC
) ON [PRIMARY]
GO
/****** Object:  UserDefinedFunction [dbo].[CONCAT]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[CONCAT](@str0 nvarchar(255) , @str1 nvarchar(255) = '123')
RETURNS nvarchar(255)
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
    [date] [nvarchar](50) NULL CONSTRAINT [DF__news__date__5AB9788F]  DEFAULT (NULL),
    [author] [nvarchar](50) NULL CONSTRAINT [DF__news__author__5BAD9CC8]  DEFAULT (NULL),
    [created] [datetime] NULL,
    [created_by] [int] NULL,
    [announce] text,
    [message] [ntext] NOT NULL CONSTRAINT [DF__news__message__4F7CD00D]  DEFAULT (''),
    [subject_name] varchar(255) NULL,
    [subject_id] int NULL,
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
    [Title] [nvarchar](255) NOT NULL CONSTRAINT [DF__news2__Title__5D95E53A]  DEFAULT (''),
    [author] [nvarchar](50) NULL CONSTRAINT [DF__news2__author__5E8A0973]  DEFAULT (NULL),
    [message] [ntext] NOT NULL CONSTRAINT [DF__news2__message__5165187F]  DEFAULT (''),
    [lang] [nchar](3) NOT NULL CONSTRAINT [DF__news2__lang__5F7E2DAC]  DEFAULT (''),
    [show] [int] NOT NULL CONSTRAINT [DF__news2__show__607251E5]  DEFAULT ((0)),
    [standalone] [int] NOT NULL CONSTRAINT [DF__news2__standalon__6166761E]  DEFAULT ((0)),
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_title] DEFAULT (''),
    [description] [ntext] NOT NULL CONSTRAINT [DF_oauth_apps_description] DEFAULT (''),
    [created] [datetime] NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_oauth_apps_created_by] DEFAULT ((0)),
    [callback_url] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_callback_url] DEFAULT (''),
    [api_key] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_api_key] DEFAULT (''),
    [consumer_key] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_consumer_key] DEFAULT (''),
    [consumer_secret] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_apps_consumer_secret] DEFAULT (''),
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
    [token] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_tokens_token] DEFAULT (''),
    [token_secret] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_tokens_token_secret] DEFAULT (''),
    [state] [int] NOT NULL CONSTRAINT [DF_oauth_tokens_state] DEFAULT ((0)),
    [verify] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_tokens_token_verify] DEFAULT (''),
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
    [nonce] [nvarchar](255) NOT NULL CONSTRAINT [DF_oauth_nonces_nonce] DEFAULT (''),
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
    [title] [nvarchar](255) NULL CONSTRAINT [DF__organizat__title__65370702]  DEFAULT (NULL),
    [cid] [int] NULL CONSTRAINT [DF__organizatio__cid__662B2B3B]  DEFAULT (NULL),
    [root_ref] [int] NULL CONSTRAINT [DF__organizat__root___671F4F74]  DEFAULT (NULL),
    [level] [int] NULL CONSTRAINT [DF__organizat__level__681373AD]  DEFAULT (NULL),
    [next_ref] [int] NULL CONSTRAINT [DF__organizat__next___690797E6]  DEFAULT (NULL),
    [prev_ref] [int] NULL CONSTRAINT [DF__organizat__prev___69FBBC1F]  DEFAULT (NULL),
    [mod_ref] [int] NULL CONSTRAINT [DF__organizat__mod_r__6AEFE058]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__organizat__statu__6BE40491]  DEFAULT (NULL),
    [vol1] [int] NULL CONSTRAINT [DF__organizati__vol1__6CD828CA]  DEFAULT (NULL),
    [vol2] [int] NULL CONSTRAINT [DF__organizati__vol2__6DCC4D03]  DEFAULT (NULL),
    [metadata] [ntext] NULL,
    [module] [int] NOT NULL CONSTRAINT [DF__organizati__mod__6DCC4D03]  DEFAULT ((0)),
 CONSTRAINT [PK_organizations] PRIMARY KEY CLUSTERED
(
    [oid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_organizations_cid] ON [dbo].[organizations]
(
    [cid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_organizations_mod] ON [dbo].[organizations]
(
    [module] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_organizations_pre] ON [dbo].[organizations]
(
    [prev_ref] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_organizations_vol1] ON [dbo].[organizations]
(
    [vol1] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_organizations_vol2] ON [dbo].[organizations]
(
    [vol2] ASC
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
    [name] [nvarchar](255) NULL CONSTRAINT [DF__periods__name__0C50D423]  DEFAULT (NULL),
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
ALTER FUNCTION [dbo].[PASSWORD] (@pass nvarchar(255))
RETURNS nvarchar(255)
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
    [acid] [nvarchar](8) NOT NULL CONSTRAINT [DF__permission__acid__10216507]  DEFAULT (''),
    [type] [nvarchar](255) NOT NULL CONSTRAINT [DF__permission__type__11158940]  DEFAULT ('dean'),
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
/****** Object:  Table [dbo].[personal]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[personal](
    [PID] [int] IDENTITY(1,1) NOT NULL,
    [FIO] [nvarchar](60) NOT NULL CONSTRAINT [DF__personal__FIO__1A9EF37A]  DEFAULT (''),
    [work] [nvarchar](120) NOT NULL CONSTRAINT [DF__personal__work__1B9317B3]  DEFAULT (''),
    [tel] [nvarchar](40) NOT NULL CONSTRAINT [DF__personal__tel__1C873BEC]  DEFAULT (''),
    [email] [nvarchar](40) NOT NULL CONSTRAINT [DF__personal__email__1D7B6025]  DEFAULT (''),
    [type] [nvarchar](255) NOT NULL CONSTRAINT [DF__personal__type__1E6F845E]  DEFAULT ((0))
) ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_personal] ON [dbo].[personal]
(
    [PID] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[permission_groups]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[permission_groups](
    [pmid] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](64) NULL CONSTRAINT [DF__permission__name__15DA3E5D]  DEFAULT (NULL),
    [default] [int] NULL CONSTRAINT [DF__permissio__defau__16CE6296]  DEFAULT ((0)),
    [type] [nvarchar](255) NULL CONSTRAINT [DF__permission__type__17C286CF]  DEFAULT ('dean'),
    [rang] [int] NOT NULL CONSTRAINT [DF__permission__rang__18B6AB08]  DEFAULT ((0)),
    [application] [int] NOT NULL CONSTRAINT [DF__permission__app__18B6AB08]  DEFAULT ((0)),
 CONSTRAINT [PK_permission_groups] PRIMARY KEY CLUSTERED
(
    [pmid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[polls]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[polls](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__polls__name__4CC05EF3]  DEFAULT (''),
    [description] [ntext] NULL CONSTRAINT [DF__polls__desc__51851410]  DEFAULT (NULL),
    [begin] [datetime] NOT NULL CONSTRAINT [DF__polls__begin__4DB4832C]  DEFAULT ((0)),
    [end] [datetime] NOT NULL CONSTRAINT [DF__polls__end__4EA8A765]  DEFAULT ((0)),
    [event] [int] NOT NULL CONSTRAINT [DF__polls__event__4F9CCB9E]  DEFAULT ((0)),
    [formula] [int] NOT NULL CONSTRAINT [DF__polls__formula__5090EFD7]  DEFAULT ((0)),
    [data] [ntext] NULL,
    [deleted] [int] NOT NULL CONSTRAINT [DF__polls__deleted__51851410]  DEFAULT ((0)),
 CONSTRAINT [PK_polls] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[polls_people]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[polls_people](
    [poll] [int] NOT NULL,
    [mid] [int] NOT NULL,
    [soid] [int] NOT NULL,
    [role] [int] NOT NULL,
    [kod] [nvarchar](100) NOT NULL,
 CONSTRAINT [PK_polls_people] PRIMARY KEY CLUSTERED
(
    [poll] ASC,
    [mid] ASC,
    [soid] ASC,
    [role] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[posts]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[posts](
    [posted] [nvarchar](15) NOT NULL CONSTRAINT [DF__posts__posted__2057CCD0]  DEFAULT (''),
    [name] [nvarchar](255) NULL CONSTRAINT [DF__posts__name__214BF109]  DEFAULT (NULL),
    [course] [nvarchar](100) NULL CONSTRAINT [DF__posts__course__22401542]  DEFAULT (NULL),
    [email] [nvarchar](255) NULL CONSTRAINT [DF__posts__email__2334397B]  DEFAULT (NULL),
    [text] [ntext] NULL,
 CONSTRAINT [PK_posts] PRIMARY KEY CLUSTERED
(
    [posted] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[posts3]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[posts3](
    [PostID] [int] IDENTITY(1,1) NOT NULL,
    [posted] [datetime] NULL,
    [name] [nvarchar](120) NOT NULL CONSTRAINT [DF__posts3__name__251C81ED]  DEFAULT (''),
    [CID] [int] NOT NULL CONSTRAINT [DF__posts3__CID__2610A626]  DEFAULT ((0)),
    [email] [nvarchar](120) NOT NULL CONSTRAINT [DF__posts3__email__2704CA5F]  DEFAULT (''),
    [text] [ntext] NOT NULL,
    [mid] [int] NOT NULL CONSTRAINT [DF__posts3__mid__27F8EE98]  DEFAULT ((0)),
    [startday] [int] NOT NULL CONSTRAINT [DF__posts3__sta__27F8EE99]  DEFAULT ((0)),
    [stopday] [int] NOT NULL CONSTRAINT [DF__posts3__sto__27F8EE00]  DEFAULT ((0))
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_posts3] ON [dbo].[posts3]
(
    [PostID] ASC
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
    [url] [nvarchar](255) NOT NULL DEFAULT (''),
    [webinar_id] [int] NOT NULL DEFAULT ((0)),
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[posts3_mids]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[posts3_mids](
    [postid] [int] NOT NULL,
    [mid] [int] NOT NULL,
 CONSTRAINT [PK_posts3_mids] PRIMARY KEY CLUSTERED
(
    [postid] ASC,
    [mid] ASC
) ON [PRIMARY]
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[quizzes](
    [quiz_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_quizzes_status]  DEFAULT ((0)),
    [description] [ntext] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_quizzes_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_quizzes_questions]  DEFAULT ((0)),
    [data] [ntext] NOT NULL CONSTRAINT [DF_quizzes_data] DEFAULT (''),
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
    [place] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback] DEFAULT (''),
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback_title] DEFAULT (''),
    [subject_name] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback_subject_name] DEFAULT (''),
    [trainer] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_feedback_trainer] DEFAULT (''),
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
    [question_id] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_question_id] DEFAULT (''),
    [question_title] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_question_title]  DEFAULT (''),
    [theme] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_theme]  DEFAULT (''),
    [answer_id] [int] NOT NULL CONSTRAINT [DF_quizzes_answers_answer_id] DEFAULT ((0)),
    [answer_title] [nvarchar](255) NOT NULL CONSTRAINT [DF_quizzes_answers_answer_title]  DEFAULT (''),
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_tasks_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_tasks_status]  DEFAULT ((0)),
    [description] [ntext] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_tasks_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_tasks_questions]  DEFAULT ((0)),
    [data] [ntext] NOT NULL CONSTRAINT [DF_tasks_data] DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_tasks_subject_id]  DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF_tasks_location]  DEFAULT ((0)),
 CONSTRAINT [PK_tasks] PRIMARY KEY CLUSTERED
(
    [task_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


CREATE TABLE [dbo].[quizzes_results](
    [user_id] [int] NULL,
    [lesson_id] [int] NULL,
    [question_id] [nvarchar](255),
    [answer_id] [int] NOT NULL,
    [freeanswer_data] [ntext] NOT NULL CONSTRAINT [DF_quizzes_results_freeanswer_data]  DEFAULT (''),
    [quiz_id] [int] NOT NULL,
    [subject_id] [int] NULL CONSTRAINT [DF_quizzes_results_subject_id]  DEFAULT ((0)),
    [junior_id] [int] NOT NULL CONSTRAINT [DF_quizzes_results_junior_id] DEFAULT ((0)),
    [link_id] [int] NOT NULL CONSTRAINT [DF_quizzes_results_link_id] DEFAULT ((0))
) ON [PRIMARY]

GO


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[quizzes_links](
    [link_id] [int] IDENTITY(1,1) NOT NULL,
    [quiz_id] [int] NULL,
    [item_id] [int] NULL,
    [item_type] [int] NULL,
    [item_page] [nvarchar](255),
    CONSTRAINT [PK_quizzes_link] PRIMARY KEY CLUSTERED
    (
        [link_id] ASC
    )WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[rank]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rank](
    [rnid] [int] IDENTITY(1,1) NOT NULL,
    [Title] [nvarchar](64) NULL CONSTRAINT [DF__rank__Title__29E1370A]  DEFAULT (NULL),
 CONSTRAINT [PK_rank] PRIMARY KEY CLUSTERED
(
    [rnid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[reckoning_courses]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[reckoning_courses](
    [trid] [int] NULL CONSTRAINT [DF__reckoning___trid__2BC97F7C]  DEFAULT (NULL),
    [cid] [int] NULL CONSTRAINT [DF__reckoning_c__cid__2CBDA3B5]  DEFAULT (NULL),
    [mid] [int] NULL CONSTRAINT [DF__reckoning_c__mid__2DB1C7EE]  DEFAULT (NULL),
    [mark] [nvarchar](255) NOT NULL CONSTRAINT [DF__reckoning___mark__2EA5EC27]  DEFAULT ((0))
) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[reports](
    [report_id] [int] IDENTITY(1,1) NOT NULL,
    [domain] [nvarchar](255) NULL CONSTRAINT [DF__reports__domain__36470DEF]  DEFAULT (NULL),
    [name] [nvarchar](255) NULL CONSTRAINT [DF__reports__name__36470DEF]  DEFAULT (NULL),
    [fields] [ntext] NULL,
    [created] [datetime],
    [created_by] [int] NOT NULL CONSTRAINT [DF__reports__created_by__40C49C62]  DEFAULT ((0)),
    [status] [int] NULL CONSTRAINT [DF__reports__status__29572725]  DEFAULT ((0)),
 CONSTRAINT [PK_reports] PRIMARY KEY CLUSTERED
(
    [report_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


/****** Object:  Table [dbo].[room]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[room](
    [rid] [int] NOT NULL CONSTRAINT [DF__room__rid__308E3499]  DEFAULT ((0)),
    [name] [nvarchar](255) NULL,
    [descript] [nvarchar](255) NULL,
    [typ] [nchar](1) NULL CONSTRAINT [DF__room__typ__336AA144]  DEFAULT ('N'),
    [adminid] [int] NULL CONSTRAINT [DF__room__adminid__345EC57D]  DEFAULT ((0))
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[rooms]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rooms](
    [rid] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__rooms__name__36470DEF]  DEFAULT (NULL),
    [volume] [int] NULL CONSTRAINT [DF__rooms__volume__373B3228]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__rooms__status__382F5661]  DEFAULT (NULL),
    [type] [int] NULL CONSTRAINT [DF__rooms__type__39237A9A]  DEFAULT (NULL),
    [description] [ntext] NULL,
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF__schedule__title__3DE82FB7]  DEFAULT (''),
    [url] [ntext] NULL,
    [descript] [ntext] NOT NULL CONSTRAINT [DF__schedule__descri__6383C8BA]  DEFAULT (''),
    [begin] [datetime] NOT NULL CONSTRAINT [DF__schedule__begin__3EDC53F0]  DEFAULT ((0)),
    [end] [datetime] NOT NULL CONSTRAINT [DF__schedule__end__3FD07829]  DEFAULT ((0)),
    [createID] [int] NOT NULL CONSTRAINT [DF__schedule__create__40C49C62]  DEFAULT ((0)),
    [typeID] [int] NOT NULL CONSTRAINT [DF__schedule__typeID__41B8C09B]  DEFAULT ((0)),
    [vedomost] [int] NULL CONSTRAINT [DF__schedule__vedomo__42ACE4D4]  DEFAULT ((0)),
    [CID] [int] NOT NULL CONSTRAINT [DF__schedule__CID__43A1090D]  DEFAULT ((0)),
    [CHID] [int] NULL CONSTRAINT [DF__schedule__CHID__44952D46]  DEFAULT (NULL),
    [startday] [int] NOT NULL CONSTRAINT [DF__schedule__startd__4589517F]  DEFAULT ((0)),
    [stopday] [int] NOT NULL CONSTRAINT [DF__schedule__stopda__467D75B8]  DEFAULT ((0)),
    [timetype] [int] NOT NULL CONSTRAINT [DF__schedule__timety__477199F1]  DEFAULT ((0)),
    [isgroup] [int] NULL CONSTRAINT [DF__schedule__isgrou__4865BE2A]  DEFAULT ((0)),
    [cond_sheid] [nvarchar](255) NULL CONSTRAINT [DF__schedule__cond_s__4959E263]  DEFAULT ('-1'),
    [cond_mark] [nvarchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_m__4A4E069C]  DEFAULT ('-'),
    [cond_progress] [nvarchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_p__4B422AD5]  DEFAULT ((0)),
    [cond_avgbal] [nvarchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_a__4C364F0E]  DEFAULT ((0)),
    [cond_sumbal] [nvarchar](255) NOT NULL CONSTRAINT [DF__schedule__cond_s__4D2A7347]  DEFAULT ((0)),
    [cond_operation] [int] NOT NULL CONSTRAINT [DF__schedule__cond_o__4E1E9780]  DEFAULT ((0)),
    [period] [nvarchar](255) NOT NULL CONSTRAINT [DF__schedule__period__4F12BBB9]  DEFAULT ('-1'),
    [rid] [int] NOT NULL CONSTRAINT [DF__schedule__rid__5006DFF2]  DEFAULT ((0)),
    [gid] [int] NOT NULL CONSTRAINT [DF__schedule__gid__5006DFF2]  DEFAULT ((0)),
    [teacher] [int] NOT NULL CONSTRAINT [DF__schedule__teache__50FB042B]  DEFAULT ((0)),
    [moderator] [int] NOT NULL CONSTRAINT [DF__schedule__moderator]  DEFAULT ((0)),
    [pub] [int] NOT NULL CONSTRAINT [DF__schedule__pub__50FB042B]  DEFAULT ((0)),
    [sharepointId] [int] NOT NULL CONSTRAINT [DF_schedule_sharepointId]  DEFAULT ((0)),
    [connectId] [nvarchar](255) NOT NULL CONSTRAINT [DF_schedule_connectId]  DEFAULT (''),
    [recommend] [bit] NOT NULL CONSTRAINT [DF_schedule_recommend]  DEFAULT ((0)),
    [notice] [int] NOT NULL CONSTRAINT [DF_schedule_notice]  DEFAULT ((0)),
    [notice_days] [int] NOT NULL CONSTRAINT [DF_schedule_notice_days]  DEFAULT ((0)),
    [all] [bit] NOT NULL CONSTRAINT [DF_schedule_all]  DEFAULT ((0)),
    [params] [ntext],
    [activities] [ntext],
	[order] [int] NOT NULL DEFAULT ((0)),
    [tool] [nvarchar](255) NOT NULL CONSTRAINT [DF_schedule_tool]  DEFAULT (''),
	[isfree] [tinyint] NOT NULL CONSTRAINT [DF__schedule__isfree__5006DFF2]  DEFAULT ((0)),	
    [section_id] [int] NULL,
 CONSTRAINT [PK_schedule] PRIMARY KEY CLUSTERED
(
    [SHEID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[scheduleID]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[scheduleID](
    [SSID] [int] IDENTITY(1,1) NOT NULL,
    [SHEID] [int] NOT NULL CONSTRAINT [DF__scheduleI__SHEID__52E34C9D]  DEFAULT ((0)),
    [MID] [int] NOT NULL CONSTRAINT [DF__scheduleID__MID__53D770D6]  DEFAULT ((0)),
    [beginPersonal] [datetime] NOT NULL CONSTRAINT [DF__scheduleID__beginPersonal__3EDC53F0]  DEFAULT ((0)),
    [endPersonal] [datetime] NOT NULL CONSTRAINT [DF__scheduleID__endPersonal__3FD07829]  DEFAULT ((0)),	
    [gid] [int] NULL CONSTRAINT [DF__scheduleID__gid__54CB950F]  DEFAULT (NULL),
    [isgroup] [int] NULL CONSTRAINT [DF__scheduleI__isgro__55BFB948]  DEFAULT ((0)),
    [V_STATUS] [float] NOT NULL CONSTRAINT [DF__scheduleI__V_STA__56B3DD81]  DEFAULT ((-1)),
	[V_DONE]  [int] NOT NULL CONSTRAINT [DF__scheduleI__V_DONE__56B3DD81]  DEFAULT ((0)),
    [V_DESCRIPTION] [nvarchar](255) NOT NULL CONSTRAINT [DF__scheduleI__V_DES__57A801BA]  DEFAULT (''),
    [DESCR] [ntext] NULL,
    [SMSremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__SMSre__589C25F3]  DEFAULT ((0)),
    [ICQremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__ICQre__59904A2C]  DEFAULT ((0)),
    [EMAILremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__EMAIL__5A846E65]  DEFAULT ((0)),
    [ISTUDremind] [int] NOT NULL CONSTRAINT [DF__scheduleI__ISTUD__5B78929E]  DEFAULT ((0)),
    [test_corr] [int] NOT NULL CONSTRAINT [DF__scheduleI__test___5C6CB6D7]  DEFAULT ((0)),
    [test_wrong] [int] NOT NULL CONSTRAINT [DF__scheduleI__test___5D60DB10]  DEFAULT ((0)),
    [test_date] [datetime] NOT NULL CONSTRAINT [DF__scheduleI__test___5E54FF49]  DEFAULT ((0)),
    [test_answers] [ntext] NULL,
    [test_tries] [int] NULL CONSTRAINT [DF__scheduleI__test___5F492382]  DEFAULT ((0)),
    [toolParams] [ntext] NULL,
    [comments] [ntext] NULL,
    [chief] [int] NOT NULL CONSTRAINT [DF__scheduleI__chie___5F492383]  DEFAULT ((0)),
    [created] [datetime] NULL,
    [updated] [datetime] NULL,
    [launched] [datetime] NULL,
 CONSTRAINT [PK_scheduleID] PRIMARY KEY CLUSTERED
(
    [SSID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

/****** Object:  Table [dbo].[schedule_marks_history]    Script Date: 11/29/2011 16:00:00 ******/
CREATE TABLE [dbo].[schedule_marks_history]  ( 
	[MID]    	[int] NOT NULL,
	[SSID]  	[int] NOT NULL,
	[mark]   	[int] NOT NULL DEFAULT ((0)),
	[updated]	[datetime] NOT NULL 
)
GO 

/****** Object:  Table [dbo].[schedule_locations]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[schedule_locations](
    [sheid] [int] NOT NULL CONSTRAINT [DF__schedule___sheid__3D7E1B63]  DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF__schedule___locat__3E723F9C]  DEFAULT ((0)),
    [teacher] [int] NOT NULL CONSTRAINT [DF__schedule___teach__3F6663D5]  DEFAULT ((0)),
 CONSTRAINT [PK_schedule_locations] PRIMARY KEY CLUSTERED
(
    [sheid] ASC,
    [location] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[departments_specs]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments_specs](
    [did] [int] NOT NULL,
    [spid] [int] NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[schedulecount]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[schedulecount](
    [mid] [int] NOT NULL CONSTRAINT [DF__schedulecou__mid__52AE4273]  DEFAULT ((0)),
    [sheid] [int] NOT NULL CONSTRAINT [DF__schedulec__sheid__53A266AC]  DEFAULT ((0)),
    [qty] [int] NOT NULL CONSTRAINT [DF__schedulecou__qty__54968AE5]  DEFAULT ((0)),
    [last] [int] NOT NULL CONSTRAINT [DF__scheduleco__last__558AAF1E]  DEFAULT ((0)),
 CONSTRAINT [PK_schedulecount] PRIMARY KEY CLUSTERED
(
    [mid] ASC,
    [sheid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_providers_title]  DEFAULT (''),
    [address] [ntext] NOT NULL CONSTRAINT [DF_providers_address]  DEFAULT (''),
    [contacts] [ntext] NOT NULL CONSTRAINT [DF_providers_contacts]  DEFAULT (''),
    [description] [ntext] NOT NULL CONSTRAINT [DF_providers_description]  DEFAULT (''),
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_suppliers_title]  DEFAULT (''),
    [address] [ntext] NOT NULL CONSTRAINT [DF_suppliers_address]  DEFAULT (''),
    [contacts] [ntext] NOT NULL CONSTRAINT [DF_suppliers_contacts]  DEFAULT (''),
    [description] [ntext] NOT NULL CONSTRAINT [DF_suppliers_description]  DEFAULT (''),
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
    [trackdata] [ntext] NOT NULL,
    [stop] [datetime] NOT NULL CONSTRAINT [DF__scorm_trac__stop__5B438874]  DEFAULT ((0)),
    [start] [datetime] NOT NULL CONSTRAINT [DF__scorm_tra__start__5C37ACAD]  DEFAULT ((0)),
    [score] [float] NOT NULL CONSTRAINT [DF__scorm_tra__score__5D2BD0E6]  DEFAULT ((0)),
    [scoremax] [float] NOT NULL CONSTRAINT [DF__scorm_tra__score__5E1FF51F]  DEFAULT ((0)),
    [scoremin] [float] NOT NULL CONSTRAINT [DF__scorm_tra__score__5F141958]  DEFAULT ((0)),
    [status] [nvarchar](15) NOT NULL CONSTRAINT [DF__scorm_tra__statu__60083D91]  DEFAULT (''),
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
    [kod] [nvarchar](255) NOT NULL CONSTRAINT [DF__seance__kod__6501FCD8]  DEFAULT (''),
    [attach] [image] NOT NULL,
    [filename] [nvarchar](255) NOT NULL CONSTRAINT [DF__seance__filename__65F62111]  DEFAULT (''),
    [text] [ntext] NOT NULL CONSTRAINT [DF__seance__text__6501FCD8]  DEFAULT (''),
    [time] [datetime] NULL,
    [bal] [float] NULL CONSTRAINT [DF__seance__bal__66EA454A]  DEFAULT (NULL),
    [lastbal] [float] NULL CONSTRAINT [DF__seance__lastbal__67DE6983]  DEFAULT (NULL),
    [comments] [ntext] NULL,
    [review] [image] NULL,
    [review_filename] [nvarchar](255) NOT NULL CONSTRAINT [DF_seance_review_filename]  DEFAULT (''),
 CONSTRAINT [PK_seance] PRIMARY KEY CLUSTERED
(
    [stid] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
CREATE NONCLUSTERED INDEX [IX_seance] ON [dbo].[seance]
(
    [stid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_seance_1] ON [dbo].[seance]
(
    [mid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[sessions]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[sessions](
    [sessid] [int] IDENTITY(1,1) NOT NULL,
    [sesskey] [nvarchar](32) NOT NULL CONSTRAINT [DF__sessions__sesske__61F08603]  DEFAULT (''),
    [mid] [int] NOT NULL CONSTRAINT [DF__sessions__mid__62E4AA3C]  DEFAULT ((0)),
    [start] [datetime] NOT NULL CONSTRAINT [DF__sessions__start__63D8CE75]  DEFAULT ((0)),
    [stop] [datetime] NOT NULL CONSTRAINT [DF__sessions__stop__64CCF2AE]  DEFAULT ((0)),
    [ip] [nvarchar](16) NOT NULL CONSTRAINT [DF__sessions__ip__65C116E7]  DEFAULT (''),
    [logout] [int] NOT NULL CONSTRAINT [DF__sessions__logout__66B53B20]  DEFAULT ((0)),
    [browser_name] [nvarchar](64) NULL,
    [browser_version] [nvarchar](64) NULL,
    [flash_version] [nvarchar](64) NULL,
    [os] [nvarchar](64) NULL,
    [screen] [nvarchar](64) NULL,
    [cookie] [smallint] NULL,
    [js] [smallint] NULL,
    [java_version] [nvarchar](64) NULL,
    [silverlight_version] [nvarchar](64) NULL,
    [acrobat_reader_version] [nvarchar](64) NULL,
    [msxml_version] [nvarchar](64) NULL,
 CONSTRAINT [PK_sessions] PRIMARY KEY CLUSTERED
(
    [sessid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[str_of_organ2competence]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[str_of_organ2competence](
    [coid] [int] NOT NULL CONSTRAINT [DF__str_of_org__coid__69C6B1F5]  DEFAULT ((0)),
    [soid] [int] NOT NULL CONSTRAINT [DF__str_of_org__soid__6ABAD62E]  DEFAULT ((0)),
    [percent] [int] NULL CONSTRAINT [DF__str_of_or__perce__6BAEFA67]  DEFAULT ((0)),
 CONSTRAINT [PK_str_of_organ2competence] PRIMARY KEY CLUSTERED
(
    [coid] ASC,
    [soid] ASC
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
    [soid_external] [nvarchar](255) NULL CONSTRAINT [DF__structure__soid___6D9742D9]  DEFAULT (NULL),
    [name] [nvarchar](255) NULL CONSTRAINT [DF__structure___name__6E8B6712]  DEFAULT (NULL),
    [code] [nvarchar](16) NULL CONSTRAINT [DF__structure___code__6F7F8B4B]  DEFAULT (NULL),
    [mid] [int] NULL CONSTRAINT [DF__structure_o__mid__7073AF84]  DEFAULT ((0)),
    [info] [ntext] NULL,
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
 CONSTRAINT [PK_structure_of_organ] PRIMARY KEY CLUSTERED
(
    [soid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_structure_of_organ] ON [dbo].[structure_of_organ]
(
    [mid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_structure_of_organ_1] ON [dbo].[structure_of_organ]
(
    [owner_soid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_structure_of_organ_2] ON [dbo].[structure_of_organ]
(
    [type] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[structure_of_organ_roles]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[structure_of_organ_roles](
    [soid] [int] NOT NULL,
    [role] [int] NOT NULL,
 CONSTRAINT [PK_structure_of_organ_roles] PRIMARY KEY CLUSTERED
(
    [soid] ASC,
    [role] ASC
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
    [title]       [nvarchar](255) NULL,
    [link]        [nvarchar](255) NULL,
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
    [activity_name] [nvarchar](45) NULL,
    [subject_name]  [nvarchar](45) NULL,
    [subject_id]    [int] NOT NULL CONSTRAINT [DF__subscript__subje__01741E54]  DEFAULT ((0)),
    [lesson_id]     [int] NOT NULL CONSTRAINT [DF__subscript__lesso__0268428D]  DEFAULT ((0)),
    [title]         [nvarchar](255) NULL,
    [description]   [text] NULL,
    [link]          [nvarchar](255) NULL,
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
    [body] [nvarchar](255) NULL
CONSTRAINT [PK_tag] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
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
/****** Object:  Table [dbo].[teachnotes]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[teachnotes](
    [NOTID] [int] IDENTITY(1,1) NOT NULL,
    [SHEID] [int] NOT NULL CONSTRAINT [DF__teachnote__SHEID__7DCDAAA2]  DEFAULT ((0)),
    [MID] [int] NOT NULL CONSTRAINT [DF__teachnotes__MID__7EC1CEDB]  DEFAULT ((0)),
    [noteText] [ntext] NULL,
    [ISTUDremind] [int] NOT NULL CONSTRAINT [DF__teachnote__ISTUD__7FB5F314]  DEFAULT ((0)),
    [SMSremind] [int] NOT NULL CONSTRAINT [DF__teachnote__SMSre__00AA174D]  DEFAULT ((0)),
    [EMAILremind] [int] NOT NULL CONSTRAINT [DF__teachnote__EMAIL__019E3B86]  DEFAULT ((0)),
    [ICQremind] [int] NOT NULL CONSTRAINT [DF__teachnote__ICQre__02925FBF]  DEFAULT ((0))
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF__test__title__0662F0A3]  DEFAULT (''),
    [datatype] [int] NOT NULL CONSTRAINT [DF__test__datatype__075714DC]  DEFAULT ((0)),
    [data] [ntext] NOT NULL CONSTRAINT [DF__test__data__6EF57B66]  DEFAULT (''),
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
    [random_vars] [ntext] NULL,
    [allow_view_log] [int] NOT NULL CONSTRAINT [DF__test__allow_view__1975C517]  DEFAULT ((1)),
    [created_by] [int] NOT NULL CONSTRAINT [DF__test__created_by__1A69E950]  DEFAULT ((0)),
    [comments] [ntext] NULL,
    [mode] [int] NOT NULL CONSTRAINT [DF__test__mode__1B5E0D89]  DEFAULT ((0)),
    [is_poll] [int] NOT NULL CONSTRAINT [DF__test__ispoll__1B5E0D89]  DEFAULT ((0)),
    [poll_mid] [int] NOT NULL CONSTRAINT [DF__test__pollmid__1B5E0D89]  DEFAULT ((0)),
    [test_id] [int] NOT NULL CONSTRAINT [DF__test__test_id__1B5E0D89]  DEFAULT ((0)),
    [lesson_id] [int] NOT NULL CONSTRAINT [DF__test__lesson_id__1B5E0D89]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__test__type__1B5E0D89]  DEFAULT ((0)),
	[threshold] [int] NOT NULL CONSTRAINT [DF__test__threshold__1B5E0D89]  DEFAULT ((75)),
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
    [kod] [nvarchar](100) NOT NULL CONSTRAINT [DF__tests_questioons__kod]  DEFAULT (''),
 CONSTRAINT [PK_tests_questions] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [test_id] ASC,
    [kod] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_tests_questions_kod] ON [dbo].[tests_questions]
(
    [kod] ASC
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_tests_questions_test_id] ON [dbo].[tests_questions]
(
    [test_id] ASC
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_tests_questions_subject_id] ON [dbo].[tests_questions]
(
    [subject_id] ASC
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[ratings]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ratings](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL,
    [cid] [int] NOT NULL,
    [teacher] [int] NOT NULL,
    [rating] [float] NOT NULL,
 CONSTRAINT [PK_ratings] PRIMARY KEY CLUSTERED
(
    [id] ASC
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
/****** Object:  Table [dbo].[testneed]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[testneed](
    [tid] [int] NOT NULL CONSTRAINT [DF__testneed__tid__29AC2CE0]  DEFAULT ((0)),
    [kod] [nvarchar](100) NOT NULL CONSTRAINT [DF__testneed__kod__2AA05119]  DEFAULT ('')
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[reviews]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[reviews](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL,
    [module] [int] NOT NULL,
    [date] [datetime] NOT NULL,
    [review] [ntext] NOT NULL CONSTRAINT [DF_reviwes_review]  DEFAULT (''),
 CONSTRAINT [PK_reviwes] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[testquestions]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[testquestions](
    [tid] [int] NOT NULL CONSTRAINT [DF__testquestio__tid__2C88998B]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__testquestio__cid__2D7CBDC4]  DEFAULT ((0)),
    [questions] [ntext] NULL,
 CONSTRAINT [PK_testquestions] PRIMARY KEY CLUSTERED
(
    [tid] ASC,
    [cid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tracks]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tracks](
    [trid] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__tracks__name__370627FE]  DEFAULT (NULL),
    [id] [nvarchar](255) NULL CONSTRAINT [DF__tracks__id__37FA4C37]  DEFAULT (NULL),
    [volume] [int] NULL CONSTRAINT [DF__tracks__volume__38EE7070]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__tracks__status__39E294A9]  DEFAULT (NULL),
    [type] [int] NULL CONSTRAINT [DF__tracks__type__3AD6B8E2]  DEFAULT (NULL),
    [owner] [nvarchar](255) NULL CONSTRAINT [DF__tracks__owner__3BCADD1B]  DEFAULT (NULL),
    [totalcost] [int] NULL CONSTRAINT [DF__tracks__totalcos__3CBF0154]  DEFAULT (NULL),
    [currency] [int] NULL CONSTRAINT [DF__tracks__currency__3DB3258D]  DEFAULT (NULL),
    [description] [ntext] NULL,
    [credits_free] [int] NOT NULL CONSTRAINT [DF__tracks__credits___3EA749C6]  DEFAULT ((0)),
    [threshold] [int] NOT NULL CONSTRAINT [DF__tracks__threshold___3EA749C6]  DEFAULT ((0)),
    [number_of_levels] [int] NOT NULL CONSTRAINT [DF__tracks__n_of_lev___3EA749C6]  DEFAULT ((0)),
    [year] [int] NOT NULL CONSTRAINT [DF__tracks__year___3EA749C6]  DEFAULT ((0)),
    [locked] [bit] NOT NULL CONSTRAINT [DF__tracks__locked___3EA749C6]  DEFAULT ((0)),
 CONSTRAINT [PK_tracks] PRIMARY KEY CLUSTERED
(
    [trid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tracks2course]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tracks2course](
    [trid] [int] NULL CONSTRAINT [DF__tracks2cou__trid__408F9238]  DEFAULT (NULL),
    [cid] [int] NULL CONSTRAINT [DF__tracks2cour__cid__4183B671]  DEFAULT (NULL),
    [level] [int] NULL CONSTRAINT [DF__tracks2co__level__4277DAAA]  DEFAULT (NULL),
    [name] [nvarchar](255) NULL CONSTRAINT [DF__tracks2cou__name__436BFEE3]  DEFAULT (NULL),
    [hours_samost] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_sam___3EA749C6]  DEFAULT ((0)),
    [hours_lecture] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_lec___3EA749C6]  DEFAULT ((0)),
    [hours_lab] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_lab___3EA749C6]  DEFAULT ((0)),
    [hours_practice] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_prac___3EA749C6]  DEFAULT ((0)),
    [hours_seminar] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_sem___3EA749C6]  DEFAULT ((0)),
    [hours_kurs] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_kurs___3EA749C6]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_type___3EA749C6]  DEFAULT ((0)),
    [control] [int] NOT NULL CONSTRAINT [DF__tracks2co__h_ctrl___3EA749C6]  DEFAULT ((0)),
    ) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[exam_types]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[exam_types](
    [etid] [int] IDENTITY(1,1) NOT NULL,
    [title] [nvarchar](255) NULL,
 CONSTRAINT [PK_exam_types] PRIMARY KEY CLUSTERED
(
    [etid] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[tracks2mid]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tracks2mid](
    [trmid] [int] IDENTITY(1,1) NOT NULL,
    [trid] [int] NULL CONSTRAINT [DF__tracks2mid__trid__45544755]  DEFAULT (NULL),
    [mid] [int] NULL CONSTRAINT [DF__tracks2mid__mid__46486B8E]  DEFAULT (NULL),
    [level] [int] NULL CONSTRAINT [DF__tracks2mi__level__473C8FC7]  DEFAULT (NULL),
    [started] [int] NULL CONSTRAINT [DF__tracks2mi__start__4830B400]  DEFAULT (NULL),
    [changed] [int] NULL CONSTRAINT [DF__tracks2mi__chang__4924D839]  DEFAULT (NULL),
    [stoped] [int] NULL CONSTRAINT [DF__tracks2mi__stope__4A18FC72]  DEFAULT (NULL),
    [status] [int] NULL CONSTRAINT [DF__tracks2mi__statu__4B0D20AB]  DEFAULT (NULL),
    [sign] [int] NULL CONSTRAINT [DF__tracks2mid__sign__4C0144E4]  DEFAULT (NULL),
    [info] [ntext] NULL,
    [do_next_level] [int] NOT NULL CONSTRAINT [DF__tracks2mid__donext__4C0144E4]  DEFAULT ((0)),
 CONSTRAINT [PK_tracks2mid] PRIMARY KEY CLUSTERED
(
    [trmid] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_tracks2mid] ON [dbo].[tracks2mid]
(
    [trid] ASC,
    [mid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[training]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[training](
    [trid] [int] IDENTITY(1,1) NOT NULL,
    [title] [nvarchar](128) NULL CONSTRAINT [DF__training__title__4DE98D56]  DEFAULT (NULL),
    [cid] [int] NULL CONSTRAINT [DF__training__cid__4EDDB18F]  DEFAULT (NULL),
 CONSTRAINT [PK_training] PRIMARY KEY CLUSTERED
(
    [trid] ASC
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
    [filename] [nvarchar](255) NULL,
    [created] [int] NOT NULL CONSTRAINT [DF_video_created]  DEFAULT ((0)),
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_video_title]  DEFAULT (''),
    [main_video] [int] NOT NULL CONSTRAINT [DF_video_main_video]  DEFAULT ((0)),
 CONSTRAINT [PK_video] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[training_run]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[training_run](
    [run_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__training_r__name__50C5FA01]  DEFAULT (NULL),
    [path] [ntext] NULL,
    [cid] [int] NOT NULL CONSTRAINT [DF__training_r__cid__50C5FA01]  DEFAULT ((0)),
 CONSTRAINT [PK_training_run] PRIMARY KEY CLUSTERED
(
    [run_id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
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
    [login] [nvarchar](255) NULL CONSTRAINT [DF__user_login_log_login__50C5FA01]  DEFAULT (NULL),
    [date] [datetime] NULL CONSTRAINT [DF__user_login_log__date__50C5FA01]  DEFAULT (NULL),
    [event_type] [int] NOT NULL CONSTRAINT [DF__user_login_log__event_type__50C5FA01]  DEFAULT ((0)),
    [status] [int] NOT NULL CONSTRAINT [DF__user_login_log__status__50C5FA01]  DEFAULT ((0)),
    [comments] [nvarchar](255) NULL,
    [ip] [int] NOT NULL CONSTRAINT [DF__user_login_log__ip__50C5FA01]  DEFAULT ((0))
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[crontask]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[crontask](
    [crontask_id] [nvarchar](255) NOT NULL CONSTRAINT [DF_crontask_crontask_id]  DEFAULT (''),
    [crontask_runtime] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[options_at]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[options_at](
    [OptionID] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__OPTIONS_at__name__634EBE90]  DEFAULT (''),
    [value] [ntext] NOT NULL CONSTRAINT [DF__OPTIONS_at__value__0425A276]  DEFAULT (''),
 CONSTRAINT [PK_options_at] PRIMARY KEY CLUSTERED
(
    [OptionID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[options_cms]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[options_cms](
    [OptionID] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__OPTIONS_cms__name__634EBE90]  DEFAULT (''),
    [value] [ntext] NOT NULL CONSTRAINT [DF__OPTIONS_cms__value__0425A276]  DEFAULT (''),
 CONSTRAINT [PK_options_cms] PRIMARY KEY CLUSTERED
(
    [OptionID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[courses_groups]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[courses_groups](
    [did] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NULL CONSTRAINT [DF__courses_groups__name__45544755]  DEFAULT (NULL),
    [mid] [int] NOT NULL CONSTRAINT [DF__courses_groups__mid__45544755]  DEFAULT ((0)),
    [info] [image] NULL,
    [color] [nvarchar](255) NULL CONSTRAINT [DF__courses_groups__color__45544755]  DEFAULT (NULL),
    [owner_did] [int] NOT NULL CONSTRAINT [DF__courses_groups__owner_did__45544755]  DEFAULT ((0)),
    [not_in] [tinyint] NOT NULL CONSTRAINT [DF__courses_groups__not_in__45544755]  DEFAULT ((0)),
    [file_image] [nvarchar](255) NULL CONSTRAINT [DF__courses_groups__file_image__45544755]  DEFAULT (NULL),
 CONSTRAINT [PK_courses_groups] PRIMARY KEY CLUSTERED
(
    [did] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_courses_groups_mid] ON [dbo].[courses_groups]
(
    [mid] ASC
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[library_cms_index]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[library_cms_index](
    [id] [int] NOT NULL CONSTRAINT [DF__library_cms_index__id__45544755]  DEFAULT ((0)),
    [word] [int] NOT NULL CONSTRAINT [DF__library_cms_index__word__45544755]  DEFAULT ((0)),
    [count] [int] NOT NULL CONSTRAINT [DF__library_cms_index__count__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_library_cms_index] PRIMARY KEY CLUSTERED
(
    [id] ASC,
    [word] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[library_cms_index_words]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[library_cms_index_words](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [word] [nvarchar](255) NOT NULL CONSTRAINT [DF__library_cms_index_words_word__45544755]  DEFAULT (''),
 CONSTRAINT [PK_library_cms_index_words] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[polls_criteries]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[polls_criteries](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF__polls_criteries_name__45544755]  DEFAULT ((0)),
    [poll] [int] NOT NULL CONSTRAINT [DF__polls_criteries_poll__45544755]  DEFAULT ((0)),
    [mid] [int] NOT NULL CONSTRAINT [DF__polls_criteries_mid__45544755]  DEFAULT ((0)),
    [soid] [int] NOT NULL CONSTRAINT [DF__polls_criteries_soid__45544755]  DEFAULT ((0)),
    [role] [int] NOT NULL CONSTRAINT [DF__polls_criteries_role__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_polls_criteries] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_polls_criteries_mid] ON [dbo].[polls_criteries]
(
    [mid] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_polls_criteries_poll] ON [dbo].[polls_criteries]
(
    [poll] ASC
) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IX_polls_criteries_soid] ON [dbo].[polls_criteries]
(
    [soid] ASC
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
    [current] [nvarchar](255) NOT NULL CONSTRAINT [DF__sequence_current__current__45544755]  DEFAULT (''),
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
    [item] [nvarchar](255) NOT NULL CONSTRAINT [DF__sequence_history_item__45544755]  DEFAULT (''),
    [date] [datetime] NOT NULL,
    [subject_id] [int] NOT NULL CONSTRAINT [DF_sequence_history_subject_id] DEFAULT ((0)),
	[lesson_id] [int] NOT NULL CONSTRAINT [DF__sequence_history__lesson_id]  DEFAULT ((0)),
 CONSTRAINT [PK_sequence_history] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[personallog]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[personallog](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [mid] [int] NOT NULL,
    [cid] [int] NOT NULL,
    [item] [int] NOT NULL,
    [session] [nvarchar](255) NOT NULL,
    [datetime] [datetime] NOT NULL,
 CONSTRAINT [PK_personallog] PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[courses_links]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[courses_links](
    [cid] [int] NOT NULL CONSTRAINT [DF__courses_links_cid__45544755]  DEFAULT ((0)),
    [with] [int] NOT NULL CONSTRAINT [DF__courses_links_with__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_courses_links] PRIMARY KEY CLUSTERED
(
    [cid] ASC,
    [with] ASC
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
/****** Object:  Table [dbo].[reviewers]    Script Date: 04/20/2010 17:50:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[reviewers](
    [mid] [int] NOT NULL CONSTRAINT [DF__reviewers_mid__45544755]  DEFAULT ((0)),
    [cid] [int] NOT NULL CONSTRAINT [DF__reviewers_cid__45544755]  DEFAULT ((0)),
 CONSTRAINT [PK_reviewers] PRIMARY KEY CLUSTERED
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
    [href] [nvarchar](255) NOT NULL CONSTRAINT [DF__webinar_pl__href__5C8CB268]  DEFAULT (''),
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_webinar_plan_title]  DEFAULT (''),
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
/****** Object:  Table [dbo].[CHATHISTORY]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CHATHISTORY](
    [HISTORYID] [int] IDENTITY(1,1) NOT NULL,
    [CHATID] [int] NOT NULL CONSTRAINT [DF__CHATHISTO__CHATI__1A14E395]  DEFAULT ((0)),
    [Login] [nvarchar](255) NOT NULL CONSTRAINT [DF__CHATHISTO__Login__1B0907CE]  DEFAULT (''),
    [TimeDate] [datetime] NULL CONSTRAINT [DF__CHATHISTO__TimeD__1BFD2C07]  DEFAULT (NULL),
    [Msgtext] [ntext] NULL,
    [Params] [ntext] NULL,
 CONSTRAINT [PK_CHATHISTORY] PRIMARY KEY CLUSTERED
(
    [HISTORYID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Courses]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Courses](
    [CID] [int] IDENTITY(1,1) NOT NULL,
    [Title] [nvarchar](255) NOT NULL CONSTRAINT [DF__Courses__Title__31EC6D26]  DEFAULT (''),
    [Description] [ntext] NOT NULL CONSTRAINT [DF__Courses__Descrip__77BFCB91]  DEFAULT (''),
    [TypeDes] [int] NOT NULL CONSTRAINT [DF__Courses__TypeDes__32E0915F]  DEFAULT ((0)),
    [CD] [ntext] NOT NULL CONSTRAINT [DF__Courses__CD__78B3EFCA]  DEFAULT (''),
    [cBegin] [datetime] NOT NULL CONSTRAINT [DF__Courses__cBegin__33D4B598]  DEFAULT ((0)),
    [cEnd] [datetime] NOT NULL CONSTRAINT [DF__Courses__cEnd__34C8D9D1]  DEFAULT ((0)),
    [Fee] [float] NOT NULL CONSTRAINT [DF__Courses__Fee__35BCFE0A]  DEFAULT ((0)),
    [valuta] [int] NOT NULL CONSTRAINT [DF__Courses__valuta__36B12243]  DEFAULT ((0)),
    [Status] [nvarchar](25) NOT NULL CONSTRAINT [DF__Courses__Status__37A5467C]  DEFAULT (''),
    [createby] [nvarchar](50) NOT NULL CONSTRAINT [DF__Courses__createb__38996AB5]  DEFAULT (''),
    [createdate] [datetime] NOT NULL CONSTRAINT [DF__Courses__created__398D8EEE]  DEFAULT ((0)),
    [longtime] [int] NOT NULL CONSTRAINT [DF__Courses__longtim__3A81B327]  DEFAULT ((0)),
    [did] [ntext] NULL,
    [credits_student] [int] NOT NULL CONSTRAINT [DF__Courses__credits__3C69FB99]  DEFAULT ((0)),
    [credits_teacher] [int] NOT NULL CONSTRAINT [DF__Courses__credits__3D5E1FD2]  DEFAULT ((0)),
    [locked] [int] NOT NULL CONSTRAINT [DF__Courses__locked__3E52440B]  DEFAULT ((0)),
    [chain] [int] NOT NULL CONSTRAINT [DF__Courses__chain__3F466844]  DEFAULT ((0)),
    [is_poll] [int] NOT NULL CONSTRAINT [DF__Courses__is_poll__403A8C7D]  DEFAULT ((0)),
    [is_module_need_check] [int] NOT NULL CONSTRAINT [DF__Courses__is_module_need_check__403A8C7E]  DEFAULT ((0)),
    [type] [int] NOT NULL CONSTRAINT [DF__Courses__type__403A8C7E]  DEFAULT ((0)),
    [tree] [ntext] NOT NULL CONSTRAINT [DF__Courses__tree__403A8C7E]  DEFAULT (''),
    [progress] [int] NOT NULL CONSTRAINT [DF__Courses__progress__403A8C7E]  DEFAULT ((0)),
    [sequence] [int] NOT NULL CONSTRAINT [DF_Courses_sequence]  DEFAULT ((0)),
    [provider] [int] NOT NULL CONSTRAINT [DF_Courses_provider]  DEFAULT ((0)),
    [provider_options] [nvarchar](255) NOT NULL CONSTRAINT [DF_Courses_provider_options]  DEFAULT (''),
    [planDate] [datetime] NULL,
    [developStatus] [nvarchar](45) NULL,
    [lastUpdateDate] datetime NULL,
    [archiveDate] datetime NULL,
    [services] [int] NOT NULL CONSTRAINT [DF_Courses_services]  DEFAULT ((0)),
    [has_tree] [int] NOT NULL CONSTRAINT [DF_Courses_hastree]  DEFAULT ((0)),
    [new_window] [int] NOT NULL CONSTRAINT [DF_Courses_new_window]  DEFAULT ((0)),
    [emulate] [int] NOT NULL CONSTRAINT [DF_Courses_emulate]  DEFAULT ((0)),
    [format] [int] NOT NULL CONSTRAINT [DF_Courses_format] DEFAULT((0)),
    [author] [int] NOT NULL CONSTRAINT [DF_Courses_author] DEFAULT((0)),
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
    [action] [nvarchar](255) NOT NULL CONSTRAINT [DF__webinar_h__actio__7A1D154F]  DEFAULT (''),
    [item] [nvarchar](255) NOT NULL CONSTRAINT [DF__webinar_hi__item__7B113988]  DEFAULT (''),
    [datetime] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
    [id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Table [dbo].[Courses_stat]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
CREATE TABLE [dbo].[Courses_stat](
    [CID] [int] NOT NULL CONSTRAINT [DF__Courses_sta__CID__4222D4EF]  DEFAULT ((0)),
    [last_access] [datetime] NULL,
    [MID] [int] NOT NULL CONSTRAINT [DF__Courses_sta__MID__4316F928]  DEFAULT ((0)),
    [teacher] [nchar](1) NULL CONSTRAINT [DF__Courses_s__teach__440B1D61]  DEFAULT (NULL)
) ON [PRIMARY]
GO
SET ANSI_PADDING OFF
GO
CREATE NONCLUSTERED INDEX [IX_Courses_stat] ON [dbo].[Courses_stat]
(
    [CID] ASC
) ON [PRIMARY]
GO

/****** Object:  Table [dbo].[events]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[events](
    [event_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF__events__event_id__4D94879B]  DEFAULT (''),
    [tool] [nvarchar](255) NOT NULL CONSTRAINT [DF__events__tool__4D94879B]  DEFAULT (''),
 CONSTRAINT [PK_events] PRIMARY KEY CLUSTERED
(
    [event_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
GO


/****** Object:  Table [dbo].[EventTools]    Script Date: 04/20/2010 17:50:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EventTools](
    [TypeID] [int] IDENTITY(1,1) NOT NULL,
    [TypeName] [nvarchar](255) NOT NULL CONSTRAINT [DF__EventTool__TypeN__4D94879B]  DEFAULT (''),
    [Student] [int] NOT NULL CONSTRAINT [DF__EventTool__Stude__4E88ABD4]  DEFAULT ((0)),
    [Teacher] [int] NOT NULL CONSTRAINT [DF__EventTool__Teach__4F7CD00D]  DEFAULT ((0)),
    [Deen] [int] NOT NULL CONSTRAINT [DF__EventTools__Deen__5070F446]  DEFAULT ((0)),
    [Icon] [nvarchar](255) NOT NULL CONSTRAINT [DF__EventTools__Icon__5165187F]  DEFAULT (''),
    [XSL] [nvarchar](255) NOT NULL CONSTRAINT [DF__EventTools__XSL__52593CB8]  DEFAULT (''),
    [Description] [ntext] NOT NULL CONSTRAINT [DF__EventTool__Descr__7B905C75]  DEFAULT (''),
    [tools] [ntext] NOT NULL CONSTRAINT [DF__EventTool__tools__7C8480AE]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF__EventTools__type__534D60F1]  DEFAULT ((0)),
    [weight] [float] NOT NULL CONSTRAINT [DF__EventTool__weigh__5441852A]  DEFAULT ((0)),
 CONSTRAINT [PK_EventTools] PRIMARY KEY CLUSTERED
(
    [TypeID] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[webinar_answers](
    [aid] [int] IDENTITY(1,1) NOT NULL,
    [qid] [int] NOT NULL,
    [text] [nvarchar](255) NULL,
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
    [text] [nvarchar](255) NULL,
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
    [actionType] [nvarchar](255) NULL,
    [dateTime] [datetime] NULL,
    [color] [int] NULL,
    [tool] [int] NULL,
    [text] [ntext] NULL,
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
    [name] [nvarchar](255) NULL,
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
CREATE TABLE [dbo].[metadata_groups](
    [group_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF_metadata_goups_name]  DEFAULT (''),
    [roles] [ntext] NULL,
    [locked] [bit] NOT NULL CONSTRAINT [DF_metadata_goups_locked]  DEFAULT ((0)),
 CONSTRAINT [PK_metadata_goups] PRIMARY KEY CLUSTERED
(
    [group_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[metadata_items](
    [item_id] [int] IDENTITY(1,1) NOT NULL,
    [group_id] [int] NOT NULL CONSTRAINT [DF_metadata_items_group_id]  DEFAULT ((0)),
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF_metadata_items_name]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF_metadata_items_type]  DEFAULT ((0)),
    [value] [ntext] NULL,
    [public] [bit] NOT NULL CONSTRAINT [DF_metadata_items_public]  DEFAULT ((0)),
    [required] [bit] NOT NULL CONSTRAINT [DF_metadata_items_required]  DEFAULT ((0)),
    [order] [int] NOT NULL CONSTRAINT [DF_metadata_items_order]  DEFAULT ((0)),
    [editable] [bit] NOT NULL CONSTRAINT [DF_metadata_items_editable]  DEFAULT ((0)),
 CONSTRAINT [PK_metadata_items] PRIMARY KEY CLUSTERED
(
    [item_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tracks2group](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [trid] [int] NOT NULL CONSTRAINT [DF_tracks2group_trid]  DEFAULT ((0)),
    [level] [int] NOT NULL CONSTRAINT [DF_tracks2group_level]  DEFAULT ((0)),
    [gid] [int] NOT NULL CONSTRAINT [DF_tracks2group_gid]  DEFAULT ((0)),
    [updated] [datetime] NULL,
 CONSTRAINT [PK_tracks2group] PRIMARY KEY CLUSTERED
(
    [id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

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
    [mid_external] [nvarchar](255) NOT NULL CONSTRAINT [DF_claimants_mid_external]  DEFAULT (''),
    [lastname] [nvarchar](255) NOT NULL CONSTRAINT [DF_claimants_lastname]  DEFAULT (''),
    [firstname] [nvarchar](255) NOT NULL CONSTRAINT [DF_claimants_firstname]  DEFAULT (''),
    [patronymic] [nvarchar](255) NOT NULL CONSTRAINT [DF_claimants_patronymic]  DEFAULT (''),
    [comments] [nvarchar](255) NOT NULL CONSTRAINT [DF_claimants_comments]  DEFAULT (''),
 CONSTRAINT [PK_claimants] PRIMARY KEY CLUSTERED
(
    [SID] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

CREATE NONCLUSTERED INDEX [IX_claimants_cid] ON [dbo].[claimants]
(
    [CID] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_claimants_mid] ON [dbo].[claimants]
(
    [MID] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_claimants_midcid] ON [dbo].[claimants]
(
    [MID] ASC,
    [CID] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
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
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF_classifiers_name]  DEFAULT (''),
 CONSTRAINT [PK_classifiers] PRIMARY KEY CLUSTERED
(
    [classifier_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO
CREATE TABLE [dbo].[classifiers_types](
    [type_id] [int] IDENTITY(1,1) NOT NULL,
    [name] [nvarchar](255) NOT NULL CONSTRAINT [DF_classifiers_types_name]  DEFAULT (''),
    [link_types] [nvarchar](255) NOT NULL CONSTRAINT [DF_classifiers_types_link_name]  DEFAULT (''),
 CONSTRAINT [PK_classifiers_types] PRIMARY KEY CLUSTERED
(
    [type_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

CREATE NONCLUSTERED INDEX [IX_classifiers_lft] ON [dbo].[classifiers]
(
    [lft] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]

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
    [activity_name] [nvarchar](255) NOT NULL CONSTRAINT [DF_comments_activity_name]  DEFAULT (''),
    [subject_name] [nvarchar](255) NOT NULL CONSTRAINT [DF_comments_subject_name]  DEFAULT (''),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_comments_subject_id]  DEFAULT ((0)),
    [user_id] [int] NOT NULL CONSTRAINT [DF_comments_user_id]  DEFAULT ((0)),
    [item_id] [int] NOT NULL CONSTRAINT [DF_comments_item_id]  DEFAULT ((0)),
    [message] [ntext] NULL,
    [created] [datetime] NULL,
 CONSTRAINT [PK_comments] PRIMARY KEY CLUSTERED
(
    [id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

CREATE NONCLUSTERED INDEX [IX_comments_activity_name] ON [dbo].[comments]
(
    [activity_name] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_comments_item_id] ON [dbo].[comments]
(
    [item_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_comments_subject_id] ON [dbo].[comments]
(
    [subject_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_comments_subject_name] ON [dbo].[comments]
(
    [subject_name] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_comments_user_id] ON [dbo].[comments]
(
    [user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[test_abstract](
    [test_id] [int] IDENTITY(1,1) NOT NULL,
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_test_abstract_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_test_abstract_status]  DEFAULT ((0)),
    [description] [ntext] NULL,
	[keywords] [ntext] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_test_abstract_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_test_abstract_questions]  DEFAULT ((0)),
    [data] [ntext] NOT NULL CONSTRAINT [DF__test_abstract_data] DEFAULT (''),
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_test_feedback_title]  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF_test_feedback_type]  DEFAULT ((0)),
    [text] [ntext] NULL,
	[parent] [int] NOT NULL CONSTRAINT [DF_test_feedback_parent]  DEFAULT ((0)),
    [treshold_min] [int] NOT NULL CONSTRAINT [DF_test_feedback_treshold_min]  DEFAULT ((0)),
    [treshold_max] [int] NOT NULL CONSTRAINT [DF_test_feedback_treshold_max]  DEFAULT ((0)),
    [test_id] [int] NOT NULL CONSTRAINT [DF_test_feedback_test_id]  DEFAULT ((0)),
    [question_id] [nvarchar](255) NOT NULL CONSTRAINT [DF_test_feedback_question_id]  DEFAULT (''),
    [answer_id] [nvarchar](255) NOT NULL CONSTRAINT [DF_test_feedback_answer_id]  DEFAULT (''),
    [show_event] [int] NOT NULL CONSTRAINT [DF_test_feedback_show_event]  DEFAULT ((0)),
    [show_on_values] [ntext] NULL,
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
    [title] [nvarchar](255) NOT NULL CONSTRAINT [DF_exercises_title]  DEFAULT (''),
    [status] [int] NOT NULL CONSTRAINT [DF_exercises_status]  DEFAULT ((0)),
    [description] [ntext] NULL,
    [created] [datetime] NOT NULL,
    [updated] [datetime] NOT NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_exercises_created_by]  DEFAULT ((0)),
    [questions] [int] NOT NULL CONSTRAINT [DF_exercises_questions]  DEFAULT ((0)),
    [data] [ntext] NOT NULL CONSTRAINT [DF_exercises_data] DEFAULT (''),
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
    [resource_id_external] [nvarchar](255) NULL,
    [title] [nvarchar](255) NULL,
    [url] [nvarchar](255) NOT NULL CONSTRAINT [DF_resources_url]  DEFAULT (''),
    [volume] [nvarchar](255) NOT NULL CONSTRAINT [DF_resources_volume]  DEFAULT ('0'),
    [filename] [nvarchar](255) NOT NULL  DEFAULT (''),
    [type] [int] NOT NULL CONSTRAINT [DF_resources_type]  DEFAULT ((0)),
    [filetype] [int] NOT NULL CONSTRAINT [DF_resources_filetype]  DEFAULT ((0)),
    [description] [ntext] NOT NULL CONSTRAINT [DF_resources_description]  DEFAULT (''),
    [content] [ntext] NULL CONSTRAINT [DF_resources_content]  DEFAULT (''),
    [created] [datetime] NULL ,
    [updated] [datetime] NULL,
    [created_by] [int] NOT NULL CONSTRAINT [DF_resources_created_by]  DEFAULT ((0)),
    [services] [int] NOT NULL CONSTRAINT [DF_resources_services]  DEFAULT ((0)),
    [subject] [varchar](50) NOT NULL CONSTRAINT [DF_resources_subject] DEFAULT ('subject'),
    [subject_id] [int] NOT NULL CONSTRAINT [DF_resources_subject_id] DEFAULT ((0)),
    [status] [int] NOT NULL CONSTRAINT [DF_resources_sstatus] DEFAULT ((0)),
    [location] [int] NOT NULL CONSTRAINT [DF_resources_location] DEFAULT ((0)),
    [parent_id] [int] NOT NULL CONSTRAINT [DF_resources_parent_id] DEFAULT ((0)),
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
CREATE TABLE [dbo].[subjects](
    [subid] [int] IDENTITY(1,1) NOT NULL,
    [external_id] [nvarchar](45) NULL,
    [code] [nvarchar](255) NULL,
    [name] [nvarchar](255) NULL,
    [shortname] [nvarchar](32) NULL,
    [supplier_id] [int] NULL,
    [description] [ntext] NULL,
    [type] [nvarchar](45) NULL,
    [reg_type] [nvarchar](45) NULL,
    [begin] [datetime] NULL,
    [end] [datetime] NULL,
    [longtime] [int] NULL,	
    [price] [float] NULL,
	[price_currency] [nvarchar](25) NULL,
    [plan_users] [int] NULL,
    [services] [int] NOT NULL CONSTRAINT [DF_subjects_services]  DEFAULT ((0)),
    [period] [int] NOT NULL CONSTRAINT [DF_subjects_period] DEFAULT((0)),
    [created] [datetime] NULL,
	[last_updated] [datetime] NULL,
	[access_mode] [int] NOT NULL CONSTRAINT [DF_subjects_access_mode] DEFAULT((0)),
	[access_elements] [int] NULL,
	[mode_free_limit] [int] NULL,
	[auto_done] [int] NOT NULL DEFAULT((0)),
	[base] [int] NOT NULL DEFAULT((0)),
	[base_id] [int] NOT NULL DEFAULT((0)),
	[base_color] [nvarchar](45) NULL,
	[claimant_process_id] [int] NOT NULL DEFAULT((0)),
 CONSTRAINT [PK_subjects] PRIMARY KEY CLUSTERED
(
    [subid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

CREATE NONCLUSTERED INDEX [IX_subjects_begin] ON [dbo].[subjects]
(
    [begin] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_subjects_end] ON [dbo].[subjects]
(
    [end] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_subjects_regtype] ON [dbo].[subjects]
(
    [reg_type] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_subjects_type] ON [dbo].[subjects]
(
    [type] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
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
CREATE TABLE [dbo].[subjects_tests](
    [subject_id] [int] NOT NULL,
    [test_id] [int] NOT NULL,
 CONSTRAINT [PK_subjects_tests] PRIMARY KEY CLUSTERED
(
    [subject_id] ASC,
    [test_id] ASC
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
    [subject] [varchar](50) NOT NULL DEFAULT ('subject')),
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
CREATE TABLE [dbo].[messages](
    [message_id] [int] IDENTITY(1,1) NOT NULL,
    [from] [int] NOT NULL CONSTRAINT [DF_messages_from]  DEFAULT ((0)),
    [to] [int] NOT NULL CONSTRAINT [DF_messages_to]  DEFAULT ((0)),
    [subject] [nvarchar](255) NULL,
    [subject_id] [int] NULL,
    [message] [ntext] NULL,
    [created] [datetime] NULL,
 CONSTRAINT [PK_messages] PRIMARY KEY CLUSTERED
(
    [message_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

CREATE NONCLUSTERED INDEX [IX_messages_from] ON [dbo].[messages]
(
    [from] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_messages_to] ON [dbo].[messages]
(
    [to] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[interface](
    [interface_id] [int] IDENTITY(1,1) NOT NULL,
    [role] [nvarchar](255) NOT NULL CONSTRAINT [DF_interface_role]  DEFAULT (''),
    [user_id] [int] NOT NULL CONSTRAINT [DF_interface_user_id]  DEFAULT ((0)),
    [block] [nvarchar](255) NOT NULL CONSTRAINT [DF_interface_block]  DEFAULT (''),
    [nessity] [int] NULL CONSTRAINT [DF_interface_nessity]  DEFAULT ((0)),
    [x] [int] NOT NULL CONSTRAINT [DF_interface_x]  DEFAULT ((1)),
    [y] [int] NOT NULL CONSTRAINT [DF_interface_y]  DEFAULT ((1)),
    [param_id] [nvarchar](255) NOT NULL DEFAULT (''),
 CONSTRAINT [PK_interface] PRIMARY KEY CLUSTERED
(
    [interface_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

CREATE NONCLUSTERED INDEX [IX_interface_role] ON [dbo].[interface]
(
    [role] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_interface_userid] ON [dbo].[interface]
(
    [user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[interview] (
  [interview_id] [int] IDENTITY(1, 1) NOT NULL,
  [title] [text] NULL,
  [lesson_id] [int] NULL,
  [user_id] [int] NULL,
  [to_whom] [int] NULL,
  [type] [int] NULL,
  [question_id] [nvarchar](255) NULL,
  [message] [text]  NULL,
  [date] [datetime] NULL,
  [interview_hash] [int] NULL,
  CONSTRAINT [INTERVIEW_ID] PRIMARY KEY CLUSTERED ([interview_id])
)
ON [PRIMARY]
TEXTIMAGE_ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_interview_lesson_id] ON [dbo].[interview]
(
    [lesson_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_interview_user_id] ON [dbo].[interview]
(
    [user_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE TABLE [dbo].[interview_files] (
  [interview_id] [int] NOT NULL,
  [file_id] [nvarchar](45) NOT NULL
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[captcha](
    [login] [nvarchar](255) NOT NULL,
    [attempts] [int] NOT NULL CONSTRAINT [DF_captcha_attempts]  DEFAULT ((0)),
    [updated] [datetime] NOT NULL
) ON [PRIMARY]

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tracks_levels](
    [id] [int] IDENTITY(1,1) NOT NULL,
    [trid] [int] NOT NULL CONSTRAINT [DF_tracks_levels_trid]  DEFAULT ((0)),
    [level] [int] NOT NULL CONSTRAINT [DF_tracks_levels_level]  DEFAULT ((0)),
    [volume] [float] NOT NULL CONSTRAINT [DF_tracks_levels_volume]  DEFAULT ((0)),
    [date_begin] [datetime] NOT NULL,
    [date_end] [datetime] NOT NULL,
 CONSTRAINT [PK_tracks_levels] PRIMARY KEY CLUSTERED
(
    [id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]

GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[chat_channels]
(
  [id]         [INT] IDENTITY(1,1) NOT NULL,
  [subject_name] [nvarchar](255),
  [subject_id] [INT] NOT NULL,
  [lesson_id] [INT] NULL,
  [name]      [nvarchar](255),
  [start_date]    [DATETIME],
  [end_date]    [DATETIME],
  [show_history] [bit] NOT NULL DEFAULT ((1)),
  [start_time] [INT],
  [end_time] [INT],
  [is_general] [bit] NOT NULL DEFAULT ((0)),
  CONSTRAINT chat_channels_pk PRIMARY KEY (id)
)
GO

CREATE NONCLUSTERED INDEX [IX_chat_channels_subject_id] ON [dbo].[chat_channels]
(
    [subject_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_chat_channels_lesson_id] ON [dbo].[chat_channels]
(
    [lesson_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
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
  [receiver] [INT] NOT NULL,
  [message]       [ntext] NOT NULL,
  [created]    [DATETIME] NOT NULL
  CONSTRAINT chat_history_pk PRIMARY KEY (id)
)
GO

CREATE NONCLUSTERED INDEX [IX_chat_history_channel_id] ON [dbo].[chat_history]
(
    [channel_id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
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
CREATE TABLE [dbo].[storage_filesystem]
(
  [id]         [INT] IDENTITY(1,1) NOT NULL,
  [parent_id] [INT] NULL,
  [subject_name] [nvarchar](255),
  [subject_id] [INT] NOT NULL,
  [name]      [nvarchar](255),
  [alias]      [nvarchar](255),
  [is_file] [bit] NOT NULL,
  [description]      [nvarchar](255),
  [user_id] [INT] NULL,
  [created]    [DATETIME] NULL,
  [changed]    [DATETIME] NULL,
  CONSTRAINT storage_filesystem_pk PRIMARY KEY (id)
)
GO


CREATE NONCLUSTERED INDEX [IX_tracks_levels_level] ON [dbo].[tracks_levels]
(
    [level] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_tracks_levels_trid] ON [dbo].[tracks_levels]
(
    [trid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]



CREATE TABLE [dbo].[interesting_facts] (
  [interesting_facts_id] int IDENTITY(1, 1) NOT NULL,
  [title] text COLLATE Cyrillic_General_CI_AS NULL,
  [text] text COLLATE Cyrillic_General_CI_AS NULL,
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
  [subject_id] [INT] NOT NULL,
  [name] [nvarchar](255),
  [order] [INT] NULL,
  PRIMARY KEY CLUSTERED ([section_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] nvarchar(255) NULL,
  [cluster_id] int NULL, 
  [type] int NOT NULL default (0),
  [order] int NOT NULL DEFAULT (0),  
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_kpi] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] nvarchar(255) NULL,
  [order] int NOT NULL DEFAULT (0),  
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_criteria_test] (
  [criterion_id] int IDENTITY(1, 1) NOT NULL,
  [name] nvarchar(255) default NULL,
  [order] int NOT NULL DEFAULT 0,  
  PRIMARY KEY CLUSTERED ([criterion_id])
)
ON [PRIMARY]
GO

CREATE TABLE [dbo].[at_kpis] (
  [kpi_id] int IDENTITY(1, 1) NOT NULL,
  [name] nvarchar(255) NULL,    
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
  [value_plan] nvarchar(32) NULL,
  [value_fact] nvarchar(32) NULL,   
  PRIMARY KEY CLUSTERED ([user_kpi_id])
)
ON [PRIMARY]
GO





/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON


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
Declare @result varchar(255)

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
/****** Object:  UserDefinedFunction [dbo].[SUBSTRING_INDEX]    Script Date: 18/04/2012 ******/

/**/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON

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
/**/

/**/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON

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
/**/

INSERT INTO alt_mark (int, char) VALUES
  (-2,'+')
INSERT INTO alt_mark (int, char) VALUES
  (-3,'-');

SET IDENTITY_INSERT Teachers ON

INSERT INTO Teachers (PID, MID, CID) VALUES
  (1,1,1);

SET IDENTITY_INSERT Teachers OFF
SET IDENTITY_INSERT Students ON

INSERT INTO Students (SID, MID, CID, cgid, Registered) VALUES
  (1,1,1,0,1);
SET IDENTITY_INSERT Students OFF

SET IDENTITY_INSERT EventTools ON
INSERT INTO EventTools (TypeID, TypeName, Icon, XSL, tools) VALUES (1, 'Лекция', 'redmond_book.png', 'def.xsl', 'module');
INSERT INTO EventTools (TypeID, TypeName, Icon, XSL, tools) VALUES (2, 'Тест','redmond_test.png', 'def.xsl', 'tests');
INSERT INTO EventTools (TypeID, TypeName, Icon, XSL, tools) VALUES (3, 'Оценка курса','task.gif', 'def.xsl', 'tests');
INSERT INTO EventTools (TypeID, TypeName, Icon, XSL, tools) VALUES (4, 'Оценка тьютора','task.gif', 'def.xsl', 'tests');

SET IDENTITY_INSERT EventTools OFF




SET IDENTITY_INSERT Courses ON

INSERT INTO Courses (CID, Title, Description, TypeDes, CD, cBegin, cEnd, Fee, valuta, Status, createby, createdate, longtime) VALUES
  (1,'Пример курса','block=simple~name=description%END%type=fckeditor%END%title=%END%value=Пример описания курса%END%sub=%END%~[~~]',0,'','01.01.2009','01.01.2019',0,0,'2','elearn@hypermethod.com','01.11.2006',120);
SET IDENTITY_INSERT Courses OFF

INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('<пустой элемент>','1','-1', '0');


SET IDENTITY_INSERT admins ON


INSERT INTO admins (AID, MID) VALUES
  (1,1);

SET IDENTITY_INSERT admins OFF
SET IDENTITY_INSERT deans ON

INSERT INTO deans (DID, MID) VALUES
  (1, 1);
SET IDENTITY_INSERT deans OFF

INSERT INTO developers (mid, cid) VALUES
  (1, 0);
  
INSERT INTO managers (mid) VALUES
  (1);

SET IDENTITY_INSERT people ON

INSERT INTO People (MID, LastName, FirstName, Password, Login) VALUES (1, 'Администратор', 'Администратор', 'pass', 'admin');

SET IDENTITY_INSERT people OFF

SET IDENTITY_INSERT options ON

INSERT INTO OPTIONS (OptionID, name, value) VALUES
  (6,'dekanName','Учебная администрация')
INSERT INTO OPTIONS (OptionID, name, value) VALUES
  (5,'dekanEMail','some@e.mail')
INSERT INTO OPTIONS (OptionID, name, value) VALUES
  (11,'max_invalid_login','0')

SET IDENTITY_INSERT options OFF

INSERT INTO OPTIONS (name, value) VALUES ('chat_server_port', '50011');
INSERT INTO OPTIONS (name, value) VALUES ('drawboard_port', '50012');
INSERT INTO OPTIONS (name, value) VALUES ('import_ims_compatible', '1');
INSERT INTO OPTIONS (name, value) VALUES ('question_edit_additional_rows', '3');
INSERT INTO OPTIONS (name, value) VALUES ('answers_local_log_full', '1');
INSERT INTO OPTIONS (name, value) VALUES ('course_description_format', 'simple');
INSERT INTO OPTIONS (name, value) VALUES ('disable_copy_material', '0');
INSERT INTO OPTIONS (name, value) VALUES ('enable_check_session_exist', '0');
INSERT INTO OPTIONS (name, value) VALUES ('enable_eauthor_course_navigation', '0');
INSERT INTO OPTIONS (name, value) VALUES ('enable_forum_richtext', '1');
INSERT INTO OPTIONS (name, value) VALUES ('regform_email_required', '1');
INSERT INTO OPTIONS (name, value) VALUES ('regform_items', 'a:1:{i:0;s:8:"add_info";}');

INSERT INTO OPTIONS (name, value) VALUES ('version', '3.4');
INSERT INTO OPTIONS (name, value) VALUES ('build', '21.04.2010');
INSERT INTO OPTIONS (name, value) VALUES ('regnumber', '13984751');
INSERT INTO OPTIONS (name, value) VALUES ('headStructureUnitName', 'Организационная структура');
INSERT INTO OPTIONS (name, value) VALUES ('edo_subdivision_root_name', 'Учебная структура');

INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (1, 'Создание новой учетной записи пользователя', 'Вы зарегистрированы  в ИСДО', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (2, 'Назначение роли пользователю', 'Вам назначена роль [ROLE]', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (3, 'Назначение на учебный курс (в процессе обучения)', 'Вы назначены на обучение по курсу [COURSE]', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (4, 'Назначение на электронный курс (в процессе разработки электронного курса)', 'Вы назначены в группу  разработчиков электронного курса [URL_COURSE]', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (6, 'Перевод в пользователя прошедшие обучение по курсу', 'Вам назначена роль [ROLE]', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (7, 'Подача заявки на обучение по курсу', 'Новая заявка на обучение по курсу [URL_COURSE]', 1, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (8, 'Подача заявки на обучение по курсу', 'Ваша заявка зарегистрирована', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (9, 'Рассмотрение заявки на обучение по курсу: одобрение ', 'Ваша заявка на обучение по курсу [URL_COURSE] одобрена', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (10, 'Рассмотрение заявки на обучение по курсу: отклонение', 'Ваша заявка на обучение по курсу [URL_COURSE] отклонена', 0, ' ');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (11, 'Смена пароля пользователя', 'Ваш пароль изменён ', 0, 'Ваш пароль изменён. Новый пароль: [PASSWORD]')
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (12, 'Новое личное сообщение', '[SUBJECT]', 0, '[TEXT]');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (13, 'Обновление источника подписки', 'Подписка на источник [SOURCE]', 0, '[TEXT]');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (14,'Опрос слушателей', 'Опрос слушателей по курсу [URL_COURSE]', 0, 'Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (15,'Опрос тьюторов', 'Опрос тьюторов по курсу [URL_COURSE]', 0, 'Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (16,'Опрос руководителей', 'Опрос руководителей по курсу [URL_COURSE]', 0, 'Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n- ФИО сотрудников, прошедших обучение: [SLAVES]\n');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (17,'Шаблон сгруппированых сообщений', 'Тема этого шаблона будет заменена темой шаблона события', 0, 'Сообщение этого шаблона будет заменено объединенными сообщениями шаблона события');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (18,'Новое сообщение на форуме', 'Новое сообщение от пользователя [MESSAGE_USER_NAME]', 0, 'В теме "[SECTION_NAME]" форума "[FORUM_NAME]" оставлен новый комментарий. [MESSAGE_URL]');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (19,'Новый скрытый ответ на форуме', 'Вы получили скрытый комментарий от пользователя [MESSAGE_USER_NAME]', 0, 'На ваше сообщение в теме "[SECTION_NAME]" форума "[FORUM_NAME]" оставлен новый скрытый комментарий. [MESSAGE_URL]');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (20,'Оценка сообщения на форуме', 'Вашему сообщению на форуме выставлена оценка', 0, 'В теме "[SECTION_NAME]" форума "[FORUM_NAME]" выставлена оценка на ваш комментарий. [MESSAGE_URL]');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (22,'Учётная запись разблокирована', 'Учётная запись разблокирована', 0, 'Ваша учетная запись была разблокирована. Для входа на портал, перейдите по ссылке: [URL]');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (25,'Создание очного занятия', 'Новое занятие по дисциплине [DISCIPLINE] на дату [DATE]. СДО УрФУ (el.ustu.ru)', 0, '<p>Сообщаем Вам, что в системе дистанционного обучения Уральского федерального университета имени первого Президента России Б.Н.Ельцина в расписании по дисциплине [DISCIPLINE] произошли изменения. Добавлено новое занятие: [DATE], [TIME], [UNIT], [LESSON_FORM], [ROOM], [TEACHER].<strong>&nbsp;<br></strong><br>По всем вопросам, связанным с работой в системе, можно обращаться в Центр образовательных технологий УрФУ (cet.ustu.ru) и к администратору системы - Антону Черникову (as.chernikov@net-ustu.ru).</p>');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (26,'Изменение очного занятия', 'Изменение расписания занятий по дисциплине [DISCIPLINE] на дату [DATE]. СДО УрФУ (el.ustu.ru)', 0, '<p>Сообщаем Вам, что для Вас создана учетная запись в системе дистанционного обучения Уральского федерального университета имени первого Президента России Б.Н.Ельцина в<span>&nbsp;расписании занятий по дисциплине [DISCIPLINE] произошли изменения. Новые параметры занятия: [DATE], [TIME], [UNIT], [LESSON_FORM], [ROOM], [TEACHER]. Параметры до изменения: [DATE_OLD], [TIME_OLD], [UNIT], [LESSON_FORM], [ROOM_OLD], [TEACHER_OLD].</span><strong><br></strong><br>По всем вопросам, связанным с работой в системе, можно обращаться в Центр образовательных технологий УрФУ (cet.ustu.ru) и к администратору системы - Антону Черникову (as.chernikov@net-ustu.ru).</p>');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (27,'Отмена очного занятия', 'Отменено занятие по дисциплине [DISCIPLINE] на дату [DATE]. СДО УрФУ (el.ustu.ru)', 0, '<p>По всем вопросам, связанным с работой в системе, можно обращаться в Центр образовательных технологий УрФУ (cet.ustu.ru) и к администратору системы - Антону Черникову (as.chernikov@net-ustu.ru).</p>');
INSERT INTO NOTICE ([type], EVENT, TITLE, RECEIVER, MESSAGE) VALUES (28,'Публикация расписания очных занятий', 'Ссылка на календарь очных занятий. СДО УрФУ (el.ustu.ru)', 0, '<p>По всем вопросам, связанным с работой в системе, можно обращаться в Центр образовательных технологий УрФУ (cet.ustu.ru) и к администратору системы - Антону Черникову (as.chernikov@net-ustu.ru).</p>');


SET IDENTITY_INSERT providers ON
INSERT INTO providers (id, title, address, contacts, description) VALUES (1, 'ГиперМетод IBS', '','','');
INSERT INTO providers (id, title, address, contacts, description) VALUES (2, 'SkillSoft', '','','');
SET IDENTITY_INSERT providers OFF

SET IDENTITY_INSERT processes ON

/*** INSERT INTO processes ('1', 'Полное согласование шаблона курса', 'a:4:{s:18:"HM_Role_State_Dean";s:21:"HM_Role_State_Session";s:21:"HM_Role_State_Session";s:19:"HM_Role_State_Chief";s:19:"HM_Role_State_Chief";s:21:"HM_Role_State_Student";s:21:"HM_Role_State_Student";s:22:"HM_Role_State_Complete";}', '1');
INSERT INTO processes ('2', 'Частичное согласование шаблона курса', 'a:3:{s:18:"HM_Role_State_Dean";s:21:"HM_Role_State_Session";s:21:"HM_Role_State_Session";s:21:"HM_Role_State_Student";s:21:"HM_Role_State_Student";s:22:"HM_Role_State_Complete";}', '1');
INSERT INTO processes ('3', 'Полное согласование учебного курса', 'a:3:{s:18:"HM_Role_State_Dean";s:19:"HM_Role_State_Chief";s:19:"HM_Role_State_Chief";s:21:"HM_Role_State_Student";s:21:"HM_Role_State_Student";s:22:"HM_Role_State_Complete";}', '1');
INSERT INTO processes ('4', 'Частичное согласование учебного курса', 'a:2:{s:18:"HM_Role_State_Dean";s:21:"HM_Role_State_Student";s:21:"HM_Role_State_Student";s:22:"HM_Role_State_Complete";}', '1'); ***/
INSERT INTO processes (process_id, name, chain, [type]) VALUES('5', 'Согласование организатором обучения', 'a:1:{s:18:"HM_Role_State_Dean";s:22:"HM_Role_State_Complete";}', '1');
INSERT INTO processes (process_id, name, chain, [type]) VALUES('6', 'Согласование организатором обучения, с выбором сессии', 'a:1:{s:21:"HM_Role_State_Session";s:22:"HM_Role_State_Complete";}', '1');


SET IDENTITY_INSERT processes OFF

GO

