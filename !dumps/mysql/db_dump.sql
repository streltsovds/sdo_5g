CREATE TABLE `idea` (
  `idea_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `description` varchar(249) DEFAULT '' NOT NULL,
  `status` int(11) NOT NULL,
  `anonymous` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`idea_id`)
) ENGINE = MyISAM;

CREATE TABLE `idea_chat` (
  `idea_chat_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(249)  DEFAULT '' NOT NULL,
  `date_created` datetime NOT NULL,
  `parent_idea_chat_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idea_chat_id`)
) ENGINE = MyISAM;

CREATE TABLE `idea_like` (
  `idea_like_id` int(11) NOT NULL AUTO_INCREMENT,
  `idea_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`idea_like_id`)
) ENGINE = MyISAM;

CREATE TABLE `idea_url` (
  `idea_url_id` int(11) NOT NULL AUTO_INCREMENT,
  `idea_id` int(11) NOT NULL,
  `url` varchar(249) NOT NULL,
  PRIMARY KEY (`idea_url_id`)
) ENGINE = MyISAM;

CREATE TABLE `absence` (
  `absence_id`       int          NOT NULL auto_increment,
  `user_id`          int          NOT NULL,
  `user_external_id` varchar(249) NULL,
  `type`             int          NOT NULL,
  `absence_begin`    datetime     NULL,
  `absence_end`      datetime     NULL,
  PRIMARY KEY (`absence_id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `admins` (
  `AID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  UNIQUE KEY `AID` (`AID`),
  UNIQUE KEY `MID` (`MID`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `simple_admins` (
  `AID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  UNIQUE KEY `AID` (`AID`),
  UNIQUE KEY `MID` (`MID`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `agreements` (
  `agreement_id` INT NOT NULL auto_increment,
  `name` varchar(249),
  `item_type` INT NULL,
  `item_id` INT NULL,
  `agreement_type` INT NULL ,
  `position_id` INT NULL ,
  PRIMARY KEY (`agreement_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `description` TEXT NULL,
  `category_id_external` varchar(249),
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `cluster_id` int(11) NULL,
  `category_id` int(11) NULL,
  `type` int(11) NOT NULL default '0',
  `order` int NOT NULL DEFAULT 0,
  `status` int NOT NULL,
  `doubt` int NULL default 0,
  `description`  varchar(4096) NULL,
  PRIMARY KEY  (`criterion_id`),
	INDEX `cluster_id` (`cluster_id`),
	INDEX `category_id` (`category_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria_kpi` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `description` varchar(4000) default NULL,
  `order` int NOT NULL DEFAULT 0,
  PRIMARY KEY  (`criterion_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria_test` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `name` varchar(249) default NULL,
  `quest_id` int(11) NOT NULL default '0',
  `subject_id` int(11) NOT NULL default '0',
  `description` text NULL,
  `required`      int     NULL,
  `validity`      int     NULL,
  `employee_type` int     NULL,
  `status`        int     NULL,
  PRIMARY KEY  (`criterion_id`),
	INDEX `quest_id` (`quest_id`),
	INDEX `subject_id` (`subject_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria_personal` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `quest_id` int(11) NULL,
  `description` text NULL,
  PRIMARY KEY  (`criterion_id`),
	INDEX `quest_id` (`quest_id`)
) ENGINE=MyISAM;

CREATE TABLE `hold_mail` (
  `hold_mail_id` int(11) NOT NULL auto_increment,
  `receiver_MID` int(11) NOT NULL,
  `serialized_message` text NOT NULL,
  PRIMARY KEY (`hold_mail_id`),
  INDEX `receiver_MID` (`receiver_MID`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `at_criteria_scale_values` (
  `criterion_value_id` int(11) NOT NULL auto_increment,
  `criterion_id` int(11) NOT NULL,
  `value_id` int(11) DEFAULT NULL,
  `description` varchar(4000) default NULL,
  PRIMARY KEY (`criterion_value_id`),
	INDEX `criterion_id` (`criterion_id`),
	INDEX `value_id` (`value_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria_indicator_scale_values` (
  `criterion_indicator_value_id` int(11) NOT NULL auto_increment,
  `indicator_id` int(11) NOT NULL,
  `value_id` int(11) default NULL,
  `description` varchar(4000) default NULL,
  `description_questionnaire` varchar(4000) default NULL,
  PRIMARY KEY  (`criterion_indicator_value_id`),
  KEY `indicator_id` (`indicator_id`),
  KEY `value_id` (`value_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria_clusters` (
  `cluster_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `order` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`cluster_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_criteria_indicators` (
  `indicator_id` int(11) NOT NULL auto_increment,
  `criterion_id` int(11) DEFAULT NULL,
  `name` varchar(249) default NULL,
  `name_questionnaire` varchar(249) default NULL,
  `description_positive` varchar(4000) default NULL,
  `description_negative` varchar(4000) default NULL,
  `reverse` tinyint(4) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL DEFAULT 0,
  `doubt` int(11) NULL DEFAULT 0,
  PRIMARY KEY (`indicator_id`),
  KEY `criterion_id` (`criterion_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_kpi_units` (
  `kpi_unit_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  PRIMARY KEY (`kpi_unit_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_kpi_clusters` (
  `kpi_cluster_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  PRIMARY KEY (`kpi_cluster_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_kpis` (
  `kpi_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `kpi_cluster_id` int(1) NOT NULL default 0,
  `kpi_unit_id` int(1) NOT NULL default 0,
  `is_typical` tinyint(4) NOT NULL default 0,
  PRIMARY KEY (`kpi_id`),
	INDEX `kpi_cluster_id` (`kpi_cluster_id`),
	INDEX `kpi_unit_id` (`kpi_unit_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_user_kpis` (
  `user_kpi_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `cycle_id` int(11) NOT NULL,
  `kpi_id` int(11) default NULL,
  `weight` float(4,2) default NULL,
  `value_plan` varchar(32) default NULL,
  `value_fact` varchar(32) default NULL,
  `comments` text default NULL,
  `begin_date` DATE NULL,
  `end_date` DATE NULL,
  `value_type` int(11) default NULL,
  PRIMARY KEY (`user_kpi_id`),
	INDEX `user_id` (`user_id`),
	INDEX `cycle_id` (`cycle_id`),
	INDEX `kpi_id` (`kpi_id`)
) ENGINE=MyISAM;

CREATE TABLE `at_profile_education_requirement` (
  `education_id`   int NOT NULL DEFAULT 0,
  `profile_id`     int NOT NULL DEFAULT 0,
  `education_type` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`education_id`, `profile_id`, `education_type`)
) ENGINE = MyISAM;


CREATE TABLE IF NOT EXISTS `at_user_kpi_results` (
  `user_kpi_result_id` int(11) NOT NULL auto_increment,
  `user_kpi_id` int(11) NOT NULL,
  `user_id` int(11) NULL,
  `respondent_id` int(11) NULL,
  `relation_type` int(11) NULL,
  `value_fact` varchar(32) NULL,
  `comments` text NULL,
  `change_date` date NULL,
  PRIMARY KEY (`user_kpi_result_id`),
	INDEX `user_kpi_id` (`user_kpi_id`),
	INDEX `user_id` (`user_id`),
	INDEX `respondent_id` (`respondent_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_profile_kpis` (
  `profile_kpi_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NOT NULL,
  `kpi_id` int(11) default NULL,
  `weight` float(4,2) default NULL,
  `value_plan` varchar(32) default NULL,
  PRIMARY KEY (`profile_kpi_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `kpi_id` (`kpi_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_profile_skills` (
  `profile_skill_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NULL,
  `type` tinyint(0) default NULL,
  `skill` varchar(249) default NULL,
  PRIMARY KEY (`profile_skill_id`),
	INDEX `profile_id` (`profile_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_profile_function` (
	`profile_function_id` INT(11) NOT NULL AUTO_INCREMENT,
	`profile_id` INT(11) NOT NULL,
	`function_id` INT(11) NOT NULL,
	PRIMARY KEY (`profile_function_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `function_id` (`function_id`)
)
ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_profiles` (
  `profile_id` int(11) NOT NULL auto_increment,
  `profile_id_external` TEXT NULL,
  `position_id_external` TEXT NULL,
  `department_id_external` TEXT NOT NULL,
  `department_id` int(11) NOT NULL,
  `department_path` TEXT NOT NULL,
  `category_id` int(11) NULL,
  `programm_id` int(11) NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `name` TEXT NOT NULL,
  `shortname` TEXT NULL,
  `description` TEXT NULL,
  `requirements` text default NULL,
  `age_min` tinyint(4) NOT NULL default '0',
  `age_max` tinyint(4) NOT NULL default '0',
  `gender` tinyint(4) NOT NULL default '0',
  `education` tinyint(4) NOT NULL default '0',
  `additional_education` text ,
  `academic_degree` tinyint(4) NOT NULL default '0',
  `trips` tinyint(4) NOT NULL default '0',
  `trips_duration` varchar(249) default NULL,
  `mobility` tinyint(4) NOT NULL default '0',
  `experience` text,
  `comments` text,
  `progress` int(11) NULL,
  `double_time` TINYINT(1) NOT NULL DEFAULT 0,
  `blocked` tinyint(4) NOT NULL,
  `psk` varchar(16) NOT NULL,
  `base_id` int(11) NULL,
  `is_manager` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`profile_id`),
	INDEX `category_id` (`category_id`),
	INDEX `programm_id` (`programm_id`),
	INDEX `user_id` (`user_id`),
	INDEX `base_id` (`base_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_evaluation_criteria` (
  `evaluation_type_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`evaluation_type_id`,`criterion_id`),
	INDEX `quest_id` (`quest_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_profile_criterion_values` (
  `profile_criterion_value_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NOT NULL,
  `criterion_type` tinyint(4) NOT NULL default 1,
  `criterion_id` int(11) NOT NULL,
  `value_id` int(11) NULL,
  `value` int(11) NULL,
  `method` varchar(20) NULL,
  `importance` int NULL,
  `value_backup` int NULL,
  PRIMARY KEY  (`profile_criterion_value_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `criterion_id` (`criterion_id`),
	INDEX `value_id` (`value_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_category_criterion_values` (
  `category_criterion_value_id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `criterion_type` int(11) NOT NULL DEFAULT '0',
  `criterion_id` int(11) NOT NULL DEFAULT '0',
  `value_id` int(11) NULL,
  `value` int(11) NULL,
  `method` varchar(249),
  PRIMARY KEY (`category_criterion_value_id`),
  KEY (`category_id`),
  KEY (`criterion_id`),
  KEY (`value_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `at_evaluation_memos` (
  `evaluation_memo_id` int(11) NOT NULL auto_increment,
  `evaluation_type_id` int(11) NOT NULL,
  `name` varchar(249) default NULL,
  PRIMARY KEY  (`evaluation_memo_id`),
	INDEX `evaluation_type_id` (`evaluation_type_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_user_criterion_values` (
  `session_user_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `criterion_type` tinyint(4) NOT NULL DEFAULT 1,
  `value` float(9,2) DEFAULT NULL,
  PRIMARY KEY  (`session_user_id`,`criterion_id`, `criterion_type`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_evaluation_results` (
  `result_id` int(11) NOT NULL auto_increment,
  `criterion_id` int(11) NOT NULL,
  `session_event_id` int(11) NOT NULL,
  `session_user_id` int(11) NOT NULL,
  `relation_type` int(11) default NULL,
  `position_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  `value_weight` float NULL,
  `indicators_status` tinyint(4) NULL,
  `custom_criterion_name` varchar(249) DEFAULT NULL,
  `custom_criterion_parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY  (`result_id`),
	INDEX `criterion_id` (`criterion_id`),
	INDEX `session_event_id` (`session_event_id`),
	INDEX `session_user_id` (`session_user_id`),
	INDEX `position_id` (`position_id`),
	INDEX `value_id` (`value_id`),
	INDEX `custom_criterion_parent_id` (`custom_criterion_parent_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_evaluation_results_indicators` (
  `indicator_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `indicator_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `session_event_id` int(11) NOT NULL,
  `session_user_id` int(11) NOT NULL,
  `relation_type` int(11) default NULL,
  `position_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  PRIMARY KEY (`indicator_result_id`),
	INDEX `indicator_id` (`indicator_id`),
	INDEX `criterion_id` (`criterion_id`),
	INDEX `session_event_id` (`session_event_id`),
	INDEX `session_user_id` (`session_user_id`),
	INDEX `position_id` (`position_id`),
	INDEX `value_id` (`value_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_evaluation_memo_results` (
  `evaluation_memo_result_id` int(11) NOT NULL auto_increment,
  `evaluation_memo_id` int(11) NOT NULL,
  `value` text default NULL,
  `session_event_id` int(11) NOT NULL,
  PRIMARY KEY  (`evaluation_memo_result_id`),
	INDEX `evaluation_memo_id` (`evaluation_memo_id`),
	INDEX `session_event_id` (`session_event_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_evaluation_type` (
  `evaluation_type_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `comment` text default NULL,
  `scale_id` int(11) default NULL, #deprecated!!!
  `category_id` int(11) default NULL,
  `profile_id` int(11) default NULL,
  `vacancy_id` int(11) default NULL,
  `newcomer_id` int(11) default NULL,
  `reserve_id` int(11) default NULL,
  `method` varchar(20) NULL,
  `submethod` varchar(249) NULL,
  `methodData` text,
  `relation_type` int(11) default NULL,
  `programm_type` int(11) default '0' NOT NULL,
  PRIMARY KEY  (`evaluation_type_id`),
	INDEX `scale_id` (`scale_id`),
	INDEX `category_id` (`category_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `vacancy_id` (`vacancy_id`),
	INDEX `newcomer_id` (`newcomer_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_managers` (
  `atmanager_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`atmanager_id`),
	INDEX `user_id` (`user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_events` (
  `session_event_id` int(11) NOT NULL auto_increment,
  `session_id` int(11) NOT NULL default '0',
  `evaluation_id` int(11) NOT NULL default '0',
  `criterion_id` int(11) NOT NULL default '0',
  `criterion_type` int(11) NOT NULL default '0',
  `position_id` int(11) NOT NULL default '0',
  `session_user_id` int(11) NOT NULL default '0',
  `session_respondent_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `respondent_id` int(11) NOT NULL default '0',
  `programm_event_user_id` int(11) NOT NULL default '0',
  `quest_id` int(11) NOT NULL default '0',
  `method` varchar(249) default '',
  `name` varchar(249) default '',
  `description` text default NULL,
  `status` int(11) default '0' NOT NULL,
  `date_begin` date default NULL,
  `date_end` date default NULL,
  `date_filled` datetime default NULL,
  `is_empty_quest` int(11) NULL,
  PRIMARY KEY  (`session_event_id`),
	INDEX `session_id` (`session_id`),
	INDEX `evaluation_id` (`evaluation_id`),
	INDEX `criterion_id` (`criterion_id`),
	INDEX `position_id` (`position_id`),
	INDEX `session_user_id` (`session_user_id`),
	INDEX `session_respondent_id` (`session_respondent_id`),
	INDEX `user_id` (`user_id`),
	INDEX `respondent_id` (`respondent_id`),
	INDEX `programm_event_user_id` (`programm_event_user_id`),
	INDEX `quest_id` (`quest_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_pairs` (
  `session_pair_id` int(11) NOT NULL auto_increment,
  `session_event_id` int(11) NOT NULL default '0',
  `first_user_id` int(11) NOT NULL default '0',
  `second_user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`session_pair_id`),
	INDEX `session_event_id` (`session_event_id`),
	INDEX `first_user_id` (`first_user_id`),
	INDEX `second_user_id` (`second_user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_pair_results` (
  `session_pair_id` int(11) NOT NULL default 0,
  `session_event_id` int(11) NOT NULL default 0,
  `criterion_id` int(11) NOT NULL default 0,
  `user_id` int(11) NOT NULL default 0,
  `parent_soid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`session_pair_id`, `criterion_id`),
	INDEX `session_event_id` (`session_event_id`),
	INDEX `user_id` (`user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_pair_ratings` (
  `session_id` int(11) NOT NULL default 0,
  `criterion_id` int(11) NOT NULL default 0,
  `session_user_id` int(11) NOT NULL default 0,
  `user_id` int(11) NOT NULL default 0,
  `rating` tinyint(3) NOT NULL default 0,
  `ratio` int(11) NOT NULL default 0,
  `parent_soid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`session_id`, `criterion_id`, `user_id`),
	INDEX `session_user_id` (`session_user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_event_lessons` (
  `session_event_id` int(11) NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  `criteria` text default NULL,
  PRIMARY KEY  (`session_event_id`, `lesson_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_event_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_event_id` int(11) DEFAULT NULL,
  `method` varchar(249) default '',
  `date_begin` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
	INDEX `user_id` (`user_id`),
	INDEX `session_event_id` (`session_event_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_users` (
  `session_user_id` int(11) NOT NULL auto_increment,
  `session_id` int(11) default NULL,
  `position_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `profile_id` int(11) default NULL,
  `process_id` int(11) default NULL,
  `vacancy_candidate_id` int(11) default NULL,
  `newcomer_id` int(11) default NULL,
  `reserve_id` int(11) default NULL,
  `status` tinyint(4) NOT NULL default 0,
  `total_competence` float(4,2) default NULL,
  `total_kpi` float(4,2) default NULL,
  `result_category` tinyint(4) NULL,
  PRIMARY KEY  (`session_user_id`),
	INDEX `session_id` (`session_id`),
	INDEX `position_id` (`position_id`),
	INDEX `user_id` (`user_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `process_id` (`process_id`),
	INDEX `vacancy_candidate_id` (`vacancy_candidate_id`),
	INDEX `newcomer_id` (`newcomer_id`),
	INDEX `reserve_id` (`reserve_id`)
)  ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_relations` (
  `relation_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `respondents` text default NULL,
  `relation_type` varchar(249) default '',
  PRIMARY KEY  (`relation_id`),
	INDEX `user_id` (`user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_session_respondents` (
  `session_respondent_id` int(11) NOT NULL auto_increment,
  `session_id` int(11) default NULL,
  `position_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `progress` tinyint(4) NOT NULL default 0,
  PRIMARY KEY  (`session_respondent_id`),
	INDEX `session_id` (`session_id`),
	INDEX `position_id` (`position_id`),
	INDEX `user_id` (`user_id`)
)  ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_sessions` (
  `session_id` int(11) NOT NULL auto_increment,
  `programm_type` tinyint(4) default NULL,
  `name` TEXT NULL,
  `shortname` TEXT NULL,
  `description` text default NULL,
  `report_comment` text default NULL,
  `cycle_id` int(11) default NULL,
  `begin_date` datetime default NULL,
  `end_date` datetime default NULL,
  `initiator_id` int(11) default NULL,
  `checked_soids` text default NULL,
  `state` tinyint(4) NOT NULL default 0,
  `base_color` varchar(32) default NULL,
  `goal` varchar(249) NULL,
  PRIMARY KEY  (`session_id`),
	INDEX `cycle_id` (`cycle_id`),
	INDEX `initiator_id` (`initiator_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_vacancies` (
  `vacancy_id` int not null auto_increment,
  `vacancy_external_id` varchar(249) null,
  `name` varchar(249) null,
  `position_id` int null,
  `user_id` int null,
  `parent_position_id` int null,
  `parent_top_position_id` int null,
  `department_path` TEXT null,
  `created_by` int null,
  `profile_id` int null,
  `reason` int null,
  `create_date` DATETIME NULL,
  `open_date` datetime null,
  `close_date` datetime null,
  `complete_date` datetime default NULL,
  `complete_year` int(11) default NULL,
  `complete_month` int(11) default NULL,
  `work_place` text null,
  `work_mode` int null,
  `trip_mode` int null,
  `salary` varchar(249) null,
  `bonus` varchar(249) null,
  `subordinates` int null,
  `subordinates_count` int null,
  `subordinates_categories` text null,
  `tasks` text null,
  `status` int default 0 not null,
  `age_min` varchar(249) null,
  `age_max` varchar(249) null,
  `gender` varchar(249) null,
  `education` text null,
  `requirements` text null,
  `search_channels_corporate_site` int null,
  `search_channels_recruit_sites` int null,
  `search_channels_papers` int null,
  `search_channels_papers_list` text null,
  `search_channels_universities` int null,
  `search_channels_universities_list` text null,
  `search_channels_workplace` int null,
  `search_channels_email` int null,
  `search_channels_inner` int null,
  `search_channels_outer` int null,
  `experience` int null,
  `experience_other` text null,
  `experience_companies` text null,
  `workflow` text null ,
  `session_id` int(11) null,
  `hh_vacancy_id` int(11) null,
  `superjob_vacancy_id` int(11) null,
  `recruit_application_id` INT(11) NULL DEFAULT NULL,
  `deleted` int NULL,
  PRIMARY KEY (`vacancy_id`),
	INDEX `position_id` (`position_id`),
	INDEX `parent_position_id` (`parent_position_id`),
	INDEX `parent_top_position_id` (`parent_top_position_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `session_id` (`session_id`),
	INDEX `hh_vacancy_id` (`hh_vacancy_id`),
	INDEX `superjob_vacancy_id` (`superjob_vacancy_id`),
	INDEX `recruit_application_id` (`recruit_application_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_vacancies_data_fields` (
  `data_field_id` int not null auto_increment,
  `item_type` int null,
  `item_id` int null,
  `create_date` datetime not null,
  `last_update_date` datetime not null,
  `soid` int NOT null,
  `user_id` int null,
  `vacancy_name` varchar(249) not null,
  `who_obeys` int null,
  `subordinates_count` int null,
  `work_mode` int null,
  `type_contract` int null,
  `work_place` varchar(249) null,
  `probationary_period` varchar(249) null,
  `salary` varchar(50) null,
  `career_prospects` varchar(1024) null,
  `reason` int null,
  `tasks` varchar(1024) null,
  `education` varchar(1024) null,
  `skills` varchar(1024) null,
  `additional_education` varchar(1024) null,
  `knowledge_of_computer_programs` varchar(1024) null,
  `knowledge_of_foreign_languages` varchar(1024) null,
  `work_experience` varchar(1024) null,
  `experience_other` varchar(1024) null,
  `personal_qualities` varchar(1024) null,
  `other_requirements` varchar(1024) null,
  `number_of_vacancies` varchar(1024) null,

PRIMARY KEY (`data_field_id`),
	INDEX `item_id` (`item_id`),
	INDEX `user_id` (`user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_vacancy_hh_resume_ignore` (
	`vacancy_hh_resume_ignore_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`vacancy_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`hh_resume_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`date` DATETIME NOT NULL,
	`create_user_id` INT(11) NOT NULL,
	PRIMARY KEY (`vacancy_hh_resume_ignore_id`),
	INDEX `hh_resume_id` (`vacancy_id`, `hh_resume_id`),
	INDEX `create_user_id` (`create_user_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `recruit_candidates` (
  `candidate_id` INT NOT NULL AUTO_INCREMENT ,
  `candidate_external_id` varchar(249) NULL DEFAULT NULL,
  `user_id` INT NULL ,

  `source` INT NULL ,
  `file_id` INT NULL ,
  `resume_external_url` varchar(249),
  `resume_external_id` varchar(249) NULL DEFAULT NULL,
  `resume_json` TEXT NULL,
  `resume_html` TEXT NULL,
  `resume_date` DATE NULL,

  `hh_area` varchar(249) NULL,
  `hh_metro` varchar(249) NULL,
  `hh_salary` varchar(249) NULL,
  `hh_total_experience` varchar(249) NULL,
  `hh_education` varchar(249) NULL,
  `hh_citizenship` varchar(249) NULL,
  `hh_age` INT NULL,
  `hh_gender` varchar(249) NULL,
  `hh_negotiation_id` varchar(249) NULL,
  `spot_id` int NULL,

  PRIMARY KEY (`candidate_id`),
	INDEX `candidate_external_id` (`candidate_external_id`),
	INDEX `user_id` (`user_id`),
	INDEX `file_id` (`file_id`),
	INDEX `resume_external_id` (`resume_external_id`),
	INDEX `hh_negotiation_id` (`hh_negotiation_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_candidate_hh_specializations` (
  `specialization_id` varchar(249) NOT NULL,
  `candidate_id` INT NOT NULL
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `recruit_vacancy_candidates` (
  `vacancy_candidate_id` INT NOT NULL AUTO_INCREMENT,
  `vacancy_id` INT NULL ,
  `candidate_id` INT NULL ,
  `user_id` INT NULL ,
  `process_id` INT NULL ,
  `status` INT NULL ,
  `result` INT NULL ,
  `reserve_position_id` INT NULL ,
  `external_status` varchar(249) NULL,
  PRIMARY KEY (`vacancy_candidate_id`),
	INDEX `vacancy_id` (`vacancy_id`),
	INDEX `candidate_id` (`candidate_id`),
	INDEX `user_id` (`user_id`),
	INDEX `process_id` (`process_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_vacancy_recruiters` (
	`vacancy_recruiter_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`vacancy_id` INT(11) UNSIGNED NOT NULL,
	`recruiter_id` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`vacancy_recruiter_id`),
	INDEX `vacancy_id` (`vacancy_id`),
	INDEX `recruiter_id` (`recruiter_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_newcomers` (
  `newcomer_id` INT NOT NULL AUTO_INCREMENT,
  `state` INT NULL,
  `state_change_date` DATETIME DEFAULT NULL,
  `name` varchar(249) null,
  `user_id` INT NULL ,
  `vacancy_candidate_id` INT NULL ,
  `profile_id` INT NULL ,
  `position_id` INT NULL ,
  `process_id` INT NULL ,
  `department_path` TEXT NOT NULL,
  `manager_id` INT NULL ,
  `session_id` INT NULL ,
  `created` date,
  `status` int default 0 not null,
  `result` INT NULL ,
  `evaluation_user_id` INT NULL,
  `evaluation_date` DATETIME NULL ,
  `evaluation_start_send` INT(4) NOT NULL DEFAULT 0,
  `extended_to` DATE DEFAULT NULL,
  `final_comment` TEXT,
  `welcome_training` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`newcomer_id`),
	INDEX `user_id` (`user_id`),
	INDEX `vacancy_candidate_id` (`vacancy_candidate_id`),
	INDEX `profile_id` (`profile_id`),
	INDEX `position_id` (`position_id`),
	INDEX `process_id` (`process_id`),
	INDEX `session_id` (`session_id`),
	INDEX `evaluation_user_id` (`evaluation_user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_newcomer_recruiters` (
	`newcomer_recruiter_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`newcomer_id` INT(11) UNSIGNED NULL,
	`recruiter_id` INT(11) UNSIGNED NULL,
	PRIMARY KEY (`newcomer_recruiter_id`),
	INDEX `newcomer_id` (`newcomer_id`),
	INDEX `recruiter_id` (`recruiter_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_newcomer_file` (
	`newcomer_file_id` INT NOT NULL AUTO_INCREMENT,
	`newcomer_id` INT NULL,
	`file_id` INT NULL,
	`state_type` INT NULL,
	PRIMARY KEY (`newcomer_file_id`),
	INDEX `newcomer_id` (`newcomer_id`),
	INDEX `file_id` (`file_id`)
)
ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `recruit_providers` (
    `provider_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(249),
    `status` INT NOT NULL DEFAULT '1',
    `locked` INT NOT NULL DEFAULT '0',
    `userform` INT NOT NULL DEFAULT '1',
    `cost` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`provider_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_actual_costs` (
    `actual_cost_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `month` int(11) null,
    `year` int(11) null,
    `provider_id` int(11) null,
	`cycle_id` int(11) null,
    `document_number` varchar(249) null,
    `pay_date_document` date null,
    `pay_date_actual` date null,
    `pay_amount` double(20,2) null,
    `payment_type` varchar(249) null,
    PRIMARY KEY (`actual_cost_id`),
    INDEX `provider_id` (`provider_id`)
) ENGINE=MyISAM;

CREATE TABLE `states` (
  `scope`    varchar(20) NOT NULL,
  `scope_id` int         NULL,
  `state`    int         NULL,
  `title`    varchar(64) NULL,
  PRIMARY KEY (`scope`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `subjects_actual_costs` (
    `actual_cost_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `month` int(11) null,
    `year` int(11) null,
    `provider_id` int(11) null,
    `cycle_id` int(11) null,
    `subject_id` int(11) null,
    `document_number` varchar(249) null,
    `pay_date_document` date null,
    `pay_date_actual` date null,
    `pay_amount` double(20,2) null,
    `payment_type` varchar(249) null,
    PRIMARY KEY (`actual_cost_id`),
    INDEX `provider_id` (`provider_id`)
) ENGINE=MyISAM;

CREATE TABLE `specializations` (
  `spid`        int          NOT NULL AUTO_INCREMENT,
  `name`        varchar(249) NOT NULL,
  `discription` text         NULL,
  PRIMARY KEY (`spid`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `recruit_planned_costs` (
    `planned_cost_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `month` int(11) null,
    `year` int(11) null,
    `provider_id` int(11) null,
    `base_sum` double(20,2),
    `corrected_sum` double(20,2),
    `status` VARCHAR(20) NOT NULL default 'new',
    PRIMARY KEY (`planned_cost_id`),
	INDEX `provider_id` (`provider_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_reserve_positions` (
  `reserve_position_id` int(10) NOT NULL auto_increment,
  `name` TEXT null,
  `position_id` INT NULL,
  `profile_id` INT NULL,
  `requirements` TEXT null,
  `formation_source` TEXT null,
  `description` varchar(249) null,
  `in_slider` int NOT NULL default '0',
  `app_gather_end_date` datetime null,
  `custom_respondents` text NULL,
  `recruiters` text NULL,
  PRIMARY KEY  (`reserve_position_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_reserve_requests` (
  `reserve_request_id` int(10) NOT NULL auto_increment,
  `user_id` INT NOT NULL,
  `position_id` INT NOT NULL,
  `reserve_id` INT NULL,
  `request_date` DATETIME NOT NULL,
  `status` TINYINT NOT NULL default '0',
  PRIMARY KEY(`reserve_request_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_reserves` (
  `reserve_id` int(10) NOT NULL auto_increment,
  `name` varchar(249) null,
  `user_id` INT NULL ,
  `state_id` INT NULL,
  `state_change_date` DATETIME DEFAULT NULL,
  `profile_id` INT NULL ,
  `position_id` INT NULL ,
  `reserve_position_id` INT NOT NULL DEFAULT '0',
  `manager_id` INT NULL ,
  `process_id` INT NULL ,
  `session_id` INT NULL ,
  `created` date,
  `result` INT NULL,
  `status` INT NOT NULL default '0',
  `evaluation_user_id` INT NULL ,
  `evaluation_date` DATETIME NULL ,
  `evaluation_start_send` INT NOT NULL DEFAULT '0',
  `report_notification_sent` tinyint NOT NULL DEFAULT '0',
  `extended_to` DATE DEFAULT NULL,
  `final_comment` TEXT null,
  `cycle_id` int NULL,
  PRIMARY KEY (`reserve_id`),
KEY (`user_id`),
KEY (`profile_id`),
KEY (`position_id`),
KEY (`process_id`),
KEY (`session_id`),
KEY (`evaluation_user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `hr_rotation_recruiters` (
  `rotation_recruiter_id` int(10) NOT NULL auto_increment,
  `rotation_id` INT NULL,
  `recruiter_id` INT NULL,
  PRIMARY KEY (`rotation_recruiter_id`),
KEY(`rotation_id`),
KEY (`recruiter_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_reserve_recruiters` (
  `reserve_recruiter_id` int(10) NOT NULL auto_increment,
  `reserve_id` INT NULL,
  `recruiter_id` INT NULL,
  PRIMARY KEY (`reserve_recruiter_id`),
KEY (`reserve_id`),
KEY (`recruiter_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_reserve_files` (
  `reserve_file_id` int(10) NOT NULL auto_increment,
  `reserve_id` int NULL,
  `file_id` int NULL,
  `state_type` int NULL,
  PRIMARY KEY (`reserve_file_id`),
KEY (`reserve_id`),
KEY (`file_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_rotations` (
  `rotation_id` int(10) NOT NULL auto_increment,
  `name` varchar(249) NULL,
  `user_id` INT NOT NULL,
  `position_id` INT NULL,
  `begin_date` DATE NULL,
  `end_date` DATE NULL,
  `state_change_date` DATE NULL,
  `state_id` INT NOT NULL,
  `status` INT NOT NULL DEFAULT '0',
  `result` INT NULL,
  `report_notification_sent` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`rotation_id`),
KEY (`user_id`),
KEY (`position_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hr_rotation_files` (
  `rotation_file_id` int(10) NOT NULL auto_increment,
	`rotation_id` int NULL,
	`file_id` int NULL,
	`state_type` int NULL,
  PRIMARY KEY (`rotation_file_id`),
KEY (`rotation_id`),
KEY (`file_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `recruit_application` (
	`recruit_application_id` INT NOT NULL AUTO_INCREMENT,
	`user_id` INT default NULL,
	`soid` INT default NULL,
	`department_path` varchar(4000) NULL,
	`created` datetime,
	`created_by` INT default NULL,
	`vacancy_name` varchar(249) NULL,
	`vacancy_description` TEXT NULL,
	`programm_name` varchar(249) NULL,
	`status` INT default NULL,
	`saved_status` INT(11) DEFAULT NULL,
	`recruiter_user_id` INT NULL,
	`vacancy_id` INT DEFAULT NULL,
    PRIMARY KEY (`recruit_application_id`),
	INDEX `user_id` (`user_id`),
	INDEX `recruiter_user_id` (`recruiter_user_id`),
	INDEX `vacancy_id` (`vacancy_id`)
) ENGINE=MyISAM;

CREATE TABLE `recruit_reservists` (
  `reservist_id`          int           NOT NULL AUTO_INCREMENT,
  `company`               varchar(249)  NULL,
  `department`            varchar(249)  NULL,
  `brigade`               varchar(249)  NULL,
  `position`              varchar(249)  NULL,
  `fio`                   varchar(249)  NULL,
  `gender`                varchar(249)  NULL,
  `snils`                 varchar(249)  NULL,
  `birthday`              DATE          NULL,
  `age`                   int           NULL,
  `region`                varchar(249)  NULL,
  `citizenship`           varchar(249)  NULL,
  `phone`                 varchar(249)  NULL,
  `phone_family`          varchar(249)  NULL,
  `email`                 varchar(249)  NULL,
  `position_experience`   varchar(249)  NULL,
  `sgc_experience`        varchar(249)  NULL,
  `education`             varchar(249)  NULL,
  `retraining`            varchar(249)  NULL,
  `training`              varchar(249)  NULL,
  `qualification_result`  text NULL,
  `rewards`               text NULL,
  `violations`            text NULL,
  `comments_dkz_pk`       text NULL,
#   `qualification_result`  varchar(4000) NULL,
#   `rewards`               varchar(4000) NULL,
#   `violations`            varchar(4000) NULL,
#   `comments_dkz_pk`       varchar(4000) NULL,
  `relocation_readiness`  varchar(249)  NULL,
  `evaluation_degree`     text NULL,
#   `evaluation_degree`     varchar(4000) NULL,
  `leadership`            varchar(249)  NULL,
  `productivity`          varchar(249)  NULL,
  `quality_information`   text NULL,
#   `quality_information`   varchar(4000) NULL,
  `salary`                varchar(249)  NULL,
  `hourly_rate`           varchar(249)  NULL,
  `annual_income_rks`     varchar(249)  NULL,
  `annual_income_no_rks`  varchar(249)  NULL,
  `monthly_income_rks`    varchar(249)  NULL,
  `monthly_income_no_rks` varchar(249)  NULL,
  `import_date`           DATE          NULL,
  `importer_id`           INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`reservist_id`)
) ENGINE = MyISAM;

CREATE TABLE `report_templates` (
  `rtid`          int           NOT NULL AUTO_INCREMENT,
  `template_name` varchar(249)  NULL,
  `report_name`   varchar(249)  NULL,
  `created`       int DEFAULT 0 NOT NULL,
  `creator`       int           NULL,
  `edited`        int           NULL,
  `editor`        int           NULL,
  `template`      text          NULL,
  PRIMARY KEY (`rtid`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `blog`
(
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      varchar(249),
  `body`       LONGTEXT NOT NULL,
  `created`    DATETIME NOT NULL,
  `created_by` INT(10) UNSIGNED NOT NULL,
  `subject_name` varchar(249),
  `subject_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `created_by` (`created_by`)
)
ENGINE=MyISAM;


#
# Structure for the `claimants` table :
#



CREATE TABLE IF NOT EXISTS `claimants` (
  `SID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  `base_subject` int(11) NOT NULL default '0',
  `Teacher` tinyint(1) NOT NULL default '0',
  `created` datetime,
  `created_by` int(11) NOT NULL default '0',
  `begin` datetime,
  `end` datetime,
  `status` tinyint(1) NOT NULL default 0,
  `type` tinyint(1) NOT NULL default 0,
  `mid_external` varchar(249) NOT NULL default '',
  `lastname` varchar(249) binary NOT NULL default '',
  `firstname` varchar(249) binary NOT NULL default '',
  `patronymic` varchar(249) NOT NULL default '',
  `comments` varchar(249) NOT NULL default '',
  `dublicate` int(11) NOT NULL default '0',
  `process_id` int(11) default NULL,
  PRIMARY KEY  (`SID`),
  KEY `MID_CID` (`MID`,`CID`),
  KEY `MID` (`MID`),
  KEY `CID` (`CID`),
  KEY `base_subject` (`base_subject`)
) ENGINE=MyISAM;


#
# Structure for the `classifiers` table :
#



CREATE TABLE IF NOT EXISTS `classifiers` (
  `classifier_id` int(11) NOT NULL auto_increment,
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `name` varchar(249) NOT NULL default '',
  `type` int(11) NOT NULL default '0',
  `classifier_id_external` int(11) NULL,
  PRIMARY KEY  (`classifier_id`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `classifiers_links` (
  `item_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY  (`item_id`,`classifier_id`, `type`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `classifiers_images` (
  `classifier_image_id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`classifier_image_id`)
) ENGINE=MyISAM;





CREATE TABLE IF NOT EXISTS `classifiers_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) NOT NULL default '',
  `link_types` varchar(249) NOT NULL default '',
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM;

#
# Structure for the `comments` table :
#


CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT(11) NOT NULL auto_increment,
    `activity_name` varchar(249) NOT NULL DEFAULT '',
    `subject_name` varchar(249) NOT NULL DEFAULT '',
    `subject_id` INT(11) NOT NULL DEFAULT 0,
    `user_id` INT(11) NOT NULL DEFAULT 0,
    `item_id` INT(11) NOT NULL DEFAULT 0,
    `message` TEXT,
    `created` DATETIME NULL default 0,
    `updated` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY `activity_name` (`activity_name`),
    KEY `subject_name` (`subject_name`),
    KEY `subject_id` (`subject_id`),
    KEY `user_id` (`user_id`),
    KEY `item_id` (`item_id`)
) ENGINE=MyISAM;

# Structure for the `Courses` table :
#



CREATE TABLE IF NOT EXISTS `Courses` (
  `CID` int(4) NOT NULL auto_increment,
  `Title` varchar(249) binary  NOT NULL default '',
  `Description` text NOT NULL,
  `TypeDes` tinyint(4) NOT NULL default '0',
  `CD` text NOT NULL,
  `cBegin` date NOT NULL default 0,
  `cEnd` date NOT NULL default 0,
  `Fee` float NOT NULL default '0',
  `valuta` tinyint(4) NOT NULL default '0',
  `Status` varchar(25) NOT NULL default '',
  `createby` varchar(50) NOT NULL default '',
  `createdate` datetime NOT NULL default 0,
  `longtime` int(11) NOT NULL default '0',
  `did` text,
  `credits_student` int(10) unsigned NOT NULL default '0',
  `credits_teacher` int(10) unsigned NOT NULL default '0',
  `locked` tinyint(3) unsigned NOT NULL default '0',
  `chain` int(10) unsigned NOT NULL default '0',
  `is_poll` tinyint(3) unsigned NOT NULL default '0',
  `is_module_need_check` tinyint(3) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `tree` longtext NOT NULL,
  `progress` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `provider` int(11) NOT NULL default '0',
  `provider_options` varchar(249) NOT NULL default '',
  `planDate` date default NULL,
  `developStatus` varchar(45) default NULL,
  `lastUpdateDate` date default NULL,
  `archiveDate` date default NULL,
  `services` int(11) default '0' NOT NULL,
  `has_tree` int(10) default '0' NOT NULL,
  `new_window` tinyint(3) NOT NULL default '0',
  `emulate` tinyint(3) NOT NULL default '0',
  `format` int(10) NOT NULL default 0,
  `author` tinyint(3) NOT NULL default 0,
  `emulate_scorm` tinyint(3) NOT NULL default '0',
  `extra_navigation` tinyint(3) NOT NULL default '0',
  `subject_id` int NOT NULL DEFAULT 0,
  `entry_point`     varchar(249)        NULL,
  `activity_id`     varchar(249)        NULL,
  PRIMARY KEY  (`CID`)
) ENGINE=MyISAM;

#
# Structure for the `cycles` table :
#



CREATE TABLE IF NOT EXISTS `cycles` (
  `cycle_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `begin_date` date default 0 NOT NULL,
  `end_date` date default 0 NOT NULL,
  `newcomer_id` int NULL,
  `reserve_id` int NULL,
  `type` varchar(32),
  `year` int NULL,
  `quarter` int NULL,
  `status` int NULL,
  `created_by` int NULL,
  PRIMARY KEY  (`cycle_id`)
) ENGINE=MyISAM;

#
# Structure for the `deans` table :
#



CREATE TABLE IF NOT EXISTS `curators` (
  `curator_id` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `project_id`  int(11) NOT NULL default '0',
  PRIMARY KEY `curator_id` (`curator_id`),
  KEY `MID` (`MID`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `curators_options` (
  `user_id` int(11) NOT NULL,
  `unlimited_projects` int(11) NOT NULL default '1',
  `unlimited_classifiers` int(11) NOT NULL default '1',
  `assign_new_projects`  int(11) NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `unlimited_projects` (`unlimited_projects`),
  KEY `unlimited_classifiers` (`unlimited_classifiers`),
  KEY `assign_new_projects` (`assign_new_projects`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `curators_responsibilities` (
  `user_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL
) ENGINE=MyISAM;




CREATE TABLE IF NOT EXISTS `deans` (
  `DID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `subject_id`  int(11) NOT NULL default '0',
  PRIMARY KEY `DID` (`DID`),
  KEY `MID` (`MID`)
) ENGINE=MyISAM;


#
# Structure for the `deans` table :
#



CREATE TABLE IF NOT EXISTS `dean_poll_users` (
  `lesson_id`  int(11) NOT NULL default '0',
  `head_mid`  int(11) NOT NULL default '0',
  `student_mid`  int(11) NOT NULL default '0',
  KEY `lesson_id` (`lesson_id`),
  KEY `head_mid` (`head_mid`),
  KEY `student_mid` (`student_mid`)
) ENGINE=MyISAM;


#
# Structure for the `deans_options` table :
#



CREATE TABLE IF NOT EXISTS `deans_options` (
  `user_id` int(11) NOT NULL,
  `unlimited_subjects` int(11) NOT NULL default '1',
  `unlimited_classifiers` int(11) NOT NULL default '1',
  `assign_new_subjects`  int(11) NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `unlimited_subjects` (`unlimited_subjects`),
  KEY `unlimited_classifiers` (`unlimited_classifiers`),
  KEY `assign_new_subjects` (`assign_new_subjects`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `deans_responsibilities` (
  `user_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(11) NOT NULL auto_increment,
  `title` varchar(249) NOT NULL default '',
  `tool` varchar(249) NOT NULL default '',
  `scale_id` int(11) NOT NULL default 1,
  `weight` tinyint(4) NOT NULL default 5,
  `external_id` int(11) NULL,
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `faq` (
    `faq_id` int NOT NULL auto_increment,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `roles` varchar(249) NOT NULL default '',
    `published` int(11) default '0' NOT NULL,
  PRIMARY KEY  (`faq_id`)
) ENGINE=MyISAM;

#
# Structure for the `file` table :
#



CREATE TABLE IF NOT EXISTS `file` (
  `kod` varchar(100) NOT NULL default '',
  `fnum` int(11) NOT NULL default '0',
  `ftype` int(11) NOT NULL default '0',
  `fname` varchar(100) NOT NULL default '',
  `fdata` mediumblob NOT NULL,
  `fdate` int(10) unsigned NOT NULL default '0',
  `fx` int(11) NOT NULL default '0',
  `fy` int(11) NOT NULL default '0',
  PRIMARY KEY  (`kod`,`fnum`)
) ENGINE=MyISAM;

#
# Structure for the `files` table :
#



CREATE TABLE IF NOT EXISTS `files` (
  `file_id` int(11)  NOT NULL auto_increment,
  `name` varchar(249) NOT NULL default '',
  `path` varchar(249) NOT NULL default '',
  `file_size` int(11) NOT NULL default '0',
  `item_type` int(11) NULL,
  `item_id` int(11) NULL,
  `created_by` int(11) NULL,
  `created` datetime NULL,
  PRIMARY KEY  (`file_id`)
) ENGINE=MyISAM;

#
# Structure for the `videoblock` table :
#



CREATE TABLE IF NOT EXISTS `videoblock` (
  `videoblock_id` int(11)  NOT NULL auto_increment,
  `file_id` int(11) NOT NULL DEFAULT 0,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(249) NOT NULL default '',
  `embedded_code` text,
  PRIMARY KEY  (`videoblock_id`)
) ENGINE=MyISAM;

#
#
# Structure for the `formula` table :
#



CREATE TABLE IF NOT EXISTS `formula` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(249) NOT NULL default '',
  `formula` text NOT NULL,
  `type` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

#

#
# Tables for the forum service
#



CREATE TABLE IF NOT EXISTS `forums_list`(
    `forum_id` int unsigned NOT NULL AUTO_INCREMENT,
    `subject_id` int unsigned NOT NULL DEFAULT 0,
    `subject` varchar(50) NOT NULL DEFAULT 'subject',
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(249) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `title` varchar(249) NOT NULL,
    `created` datetime NOT NULL,
    `updated` datetime NOT NULL,
    `flags` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY  (`forum_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `forums_sections`(
    `section_id` int unsigned NOT NULL AUTO_INCREMENT,
    `lesson_id` int unsigned NOT NULL DEFAULT 0,
    `subject` varchar(50) NOT NULL DEFAULT 'subject',
    `forum_id` int unsigned NOT NULL,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(249) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `parent_id` int unsigned NOT NULL DEFAULT 0,
    `title` varchar(249) NOT NULL,
    `text` mediumtext NOT NULL,
    `created` datetime NOT NULL default 0,
    `updated` datetime NOT NULL default 0,
    `last_msg` datetime NOT NULL default 0,
    `count_msg` int unsigned NOT NULL DEFAULT 0,
    `order` int NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0,
    `is_hidden` int unsigned NOT NULL DEFAULT 0,
    `deleted_by` int          NOT NULL DEFAULT 0,
    `deleted`    datetime     NOT NULL DEFAULT 0,
    `edited_by`  int          NOT NULL DEFAULT 0,
    `edited`     datetime     NOT NULL DEFAULT 0,
    PRIMARY KEY (`section_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `forums_messages`(
    `message_id` int unsigned NOT NULL AUTO_INCREMENT,
    `forum_id` int unsigned NOT NULL,
    `section_id` int unsigned NOT NULL DEFAULT 0,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(249) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `level` int unsigned NOT NULL,
    `answer_to` int unsigned NOT NULL DEFAULT 0,
    `title` varchar(249) NOT NULL,
    `text` mediumtext NOT NULL,
    `text_preview` varchar(249) NOT NULL,
    `text_size` int unsigned NOT NULL DEFAULT 0,
    `created` datetime NOT NULL,
    `updated` datetime NOT NULL,
    `delete_date` datetime NOT NULL,
    `deleted_by` int NOT NULL DEFAULT 0,
    `rating` int NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0,
    `is_hidden` int unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`message_id`),
    KEY `forum_id_section_id` (`section_id`, `forum_id`),
    KEY `user_id_forum_id` (`forum_id`, `user_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `forums_messages_showed`(
    `user_id` int unsigned NOT NULL,
    `message_id` int unsigned NOT NULL,
    `created` datetime NOT NULL,
    PRIMARY KEY(`user_id`, `message_id`)
) ENGINE=MyISAM;

#
# Structure for the `graduated` table :
#



CREATE TABLE IF NOT EXISTS `graduated` (
  `SID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  `begin` datetime,
  `end` datetime,
  `created` datetime,
  `certificate_id` int(11) NOT NULL default '0',
  `score` VARCHAR(200) NULL,
  `status` INT NULL,
  `progress` int(11) NOT NULL default '0',
  `is_lookable` INT NULL default '0',
  `effectivity` FLOAT NULL,
  `application_id` INT NULL,
  PRIMARY KEY  (`SID`),
  KEY `MID` (`MID`),
  KEY `CID` (`CID`),
  KEY `MID_CID` (`MID`,`CID`)
) ENGINE=MyISAM;

#
# Structure for the `certificates` table :
#



CREATE TABLE IF NOT EXISTS `certificates` (
  `certificate_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `subject_id` int(11) NOT NULL default '0',
  `created` datetime default NULL,
  `name` VARCHAR(50) NULL DEFAULT NULL,
  `description` varchar(249) NULL DEFAULT NULL,
  `organization` VARCHAR(50) NULL DEFAULT NULL,
  `startdate` DATE NULL DEFAULT NULL,
  `enddate` DATE NULL DEFAULT NULL,
  `filename` VARCHAR(50) NULL DEFAULT NULL,
  `type` int(10) NOT NULL DEFAULT '0',
  `number` varchar(249) NULL,
  PRIMARY KEY  (`certificate_id`),
  KEY `USERID` (`user_id`),
  KEY `SUBJECTID` (`subject_id`),
  KEY `USER_SUBJECT` (`user_id`,`subject_id`)
) ENGINE=MyISAM;

#
#
# Structure for the `groupname` table :
#



CREATE TABLE IF NOT EXISTS `groupname` (
  `gid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `name` varchar(249) NOT NULL default '',
  `owner_gid` INT (11) DEFAULT NULL,
  PRIMARY KEY  (`gid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;

#
# Structure for the `groupuser` table :
#



CREATE TABLE IF NOT EXISTS `groupuser` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`gid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM;# Structure for the `list` table :
#

CREATE TABLE `labor_safety_specs` (
  `labor_safety_spec_id` int NOT NULL auto_increment,
  `user_id`              int NOT NULL DEFAULT 0,
  PRIMARY KEY (`labor_safety_spec_id`)
) ENGINE = MyISAM;


CREATE TABLE IF NOT EXISTS `list` (
  `kod` varchar(100) NOT NULL default '',
  `qtype` int(11) NOT NULL default '0',
  `qdata` text NOT NULL,
  `qtema` varchar(249) NOT NULL default '',
  `qmoder` tinyint(4) NOT NULL default '0',
  `adata` text NOT NULL,
  `balmax` float NOT NULL default '0',
  `balmin` float NOT NULL default '0',
  `url` text NOT NULL,
  `last` int(11) NOT NULL default '0',
  `timelimit` int(6) default NULL,
  `weight` text,
  `is_shuffled` int(11) default '1',
  `created_by` int(11) unsigned NOT NULL default '0',
  `timetoanswer` int(10) unsigned NOT NULL default '0',
  `prepend_test` varchar(249) NOT NULL default '',
  `is_poll` tinyint(4) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
  `ordr` int(11) NOT NULL default '10',
  `name` varchar(249),
  PRIMARY KEY  (`kod`),
  KEY `id` (`id`),
  KEY `qtype` (`qtype`),
  KEY `is_poll` (`is_poll`)
) ENGINE=MyISAM;

#
# Structure for the `list_files` table :
#


CREATE TABLE IF NOT EXISTS `list_files` (
  `file_id` int(11) NOT NULL,
  `kod` varchar(249) NOT NULL,
  PRIMARY KEY  (`file_id`,`kod`)
) ENGINE=MyISAM;

#
# Structure for the `logseance` table :
#



CREATE TABLE IF NOT EXISTS `logseance` (
  `stid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL default '0',
  `kod` varchar(249) NOT NULL default '',
  `number` smallint(5) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `bal` float NOT NULL default '0',
  `balmax` float NOT NULL default '0',
  `balmin` float NOT NULL default '0',
  `good` int(11) NOT NULL default '0',
  `vopros` longblob NOT NULL,
  `otvet` longblob NOT NULL,
  `attach` longblob NOT NULL,
  `filename` varchar(249) NOT NULL default '',
  `text` text NOT NULL,
  `sheid` int(11) NOT NULL default '0',
  `comments` text,
  `review` longblob NULL,
  `review_filename` varchar(249) NULL,
  `qtema` varchar(249) NOT NULL default '',
  PRIMARY KEY  (`stid`,`kod`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`),
  KEY `kod` (`kod`),
  KEY `sheid` (`sheid`)
) ENGINE=MyISAM;

#
# Structure for the `loguser` table :
#



CREATE TABLE IF NOT EXISTS `loguser` (
  `stid` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL default '0',
  `balmax` float NOT NULL default '0',
  `balmin` float NOT NULL default '0',
  `balmax2` float NOT NULL default '0',
  `balmin2` float NOT NULL default '0',
  `bal` float NOT NULL default '0',
  `mark` float NOT NULL DEFAULT '0',
  `questdone` smallint(5) unsigned NOT NULL default '0',
  `questall` smallint(5) unsigned NOT NULL default '0',
  `qty` smallint(6) NOT NULL default '0',
  `free` tinyint(4) NOT NULL default '0',
  `skip` tinyint(4) NOT NULL default '0',
  `start` int(11) unsigned NOT NULL default '0',
  `stop` int(11) unsigned NOT NULL default '0',
  `fulltime` int(10) unsigned NOT NULL default '0',
  `moder` tinyint(4) NOT NULL default '0',
  `needmoder` tinyint(4) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `moderby` int(11) NOT NULL default '0',
  `modertime` int(10) unsigned NOT NULL default '0',
  `teachertest` tinyint(4) NOT NULL default '0',
  `log` blob NOT NULL,
  `sheid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`stid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`),
  KEY `tid` (`tid`),
  KEY `sheid` (`sheid`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `load` (
  `load_id` int(11) NOT NULL AUTO_INCREMENT,
  `load_date` date DEFAULT NULL,
  `sessions` int(11) DEFAULT NULL,
  `hdd` int(11) DEFAULT NULL,
  PRIMARY KEY (`load_id`)
) ENGINE=MyISAM;

#
# Structure for the `news` table :
#


CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL auto_increment,
  `date` varchar(50) default '',
  `created` datetime default NULL,
  `author` varchar(1024) default NULL,
  `created_by` int(11) default NULL,
  `announce` text NULL,
  `message` text NOT NULL,
  `subject_name` varchar(249) default NULL,
  `url` varchar(4000) NULL,
  `subject_id` int(11) default NULL,
  `icon_url`    varchar(255)     NULL,
  `mobile`     int(11)          NULL     DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=MyISAM;

#
# Structure for the `news2` table :
#



CREATE TABLE IF NOT EXISTS `news2` (
  `nID` int(11) NOT NULL auto_increment,
  `date` timestamp NULL,
  `Title` varchar(249) NOT NULL default '',
  `author` varchar(50) default NULL,
  `message` text NOT NULL,
  `lang` char(3) NOT NULL default '',
  `show` int(1) NOT NULL default '0',
  `standalone` int(1) NOT NULL default '0',
  `application` tinyint(4) default 0,
  `soid` varchar(16) default NULL,
  `resource_id` int(11) NOT NULL,
  `type` int(4) NOT NULL default '0',
  PRIMARY KEY  (`nID`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `oauth_apps`
(
  `app_id`     int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      varchar(249) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `created`    datetime,
  `created_by` int(11) UNSIGNED NOT NULL,
  `callback_url`      varchar(249) NOT NULL DEFAULT '',
  `api_key`      varchar(249) NOT NULL DEFAULT '',
  `consumer_key`      varchar(249) NOT NULL DEFAULT '',
  `consumer_secret`      varchar(249) NOT NULL DEFAULT '',
  PRIMARY KEY (`app_id`)
)
ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `oauth_tokens`
(
  `token_id`     int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id`       int(11) NOT NULL DEFAULT 0,
  `token`        varchar(249) NOT NULL DEFAULT '',
  `token_secret` varchar(249) NOT NULL DEFAULT '',
  `state`        tinyint(4) NOT NULL DEFAULT 0,
  `verify`       varchar(249) NOT NULL DEFAULT '',
  `user_id`      int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`token_id`),
  KEY `app_id` (`app_id`),
  KEY `user_id` (`user_id`)
)
ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `oauth_nonces`
(
  `nonce_id`     int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id`       int(11) NOT NULL DEFAULT 0,
  `ts`           datetime NULL,
  `nonce`        varchar(249) NOT NULL DEFAULT '',
  PRIMARY KEY (`nonce_id`),
  KEY `app_id` (`app_id`)
)
ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `offlines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `title` varchar(249) NOT NULL,
  `created` datetime default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

#
# Structure for the `options` table :
#



CREATE TABLE IF NOT EXISTS `OPTIONS` (
  `OptionID` int(11) NOT NULL auto_increment,
  `name` varchar(249) NOT NULL default '',
  `value` text,
  PRIMARY KEY  (`OptionID`)
) ENGINE=MyISAM;

#
# Structure for the `organizations` table :
#



CREATE TABLE IF NOT EXISTS `organizations` (
  `oid` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(249) default NULL,
  `cid` int(11) default NULL,
  `root_ref` int(11) default NULL,
  `level` int(11) default NULL,
  `next_ref` int(11) default NULL,
  `prev_ref` int(11) default NULL,
  `mod_ref` int(11) default NULL,
  `status` int(11) default NULL,
  `vol1` int(11) default NULL,
  `vol2` int(11) default NULL,
  `metadata` text,
  `module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`oid`),
   KEY `prev_ref` (`prev_ref`),
   KEY `vol1` (`vol1`),
   KEY `vol2` (`vol2`),
   KEY `cid` (`cid`),
   KEY `level` (`level`),
   KEY `module` (`module`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `organizations_bookmarks` (
  `bookmark_id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `prev_id` int(11) NOT NULL default '0',
  `title` varchar(249) default NULL,
  `item_id` int(10) NOT NULL default '0',
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL default '0',
  `resource_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`bookmark_id`)
) ENGINE=MyISAM;

#
# Structure for the `people` table :
#



CREATE TABLE IF NOT EXISTS `password_history` (
  `user_id` int(10) NOT NULL,
  `password` varchar(249) default '' NOT NULL,
  `change_date` datetime,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `simple_auth` (
	`auth_key` CHAR(32) NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL,
	`link` varchar(249) NOT NULL,
	`valid_before` DATETIME NOT NULL,
	PRIMARY KEY (`auth_key`)
)ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `People` (
  `MID` int(11) NOT NULL auto_increment,
  `mid_external` varchar(249) NOT NULL default '',
  `LastName` varchar(249) binary NOT NULL default '',
  `FirstName` varchar(249) binary NOT NULL default '',
  `LastNameLat` varchar(249) binary NOT NULL default '',
  `FirstNameLat` varchar(249) binary NOT NULL default '',
  `Patronymic` varchar(249) NOT NULL default '',
  `Registered` datetime default NULL,
  `Course` int(11) NOT NULL default '1',
  `EMail` varchar(249) NOT NULL default '',
  `email_confirmed` tinyint(4) unsigned NOT NULL default '0',
  `Phone` varchar(249) NOT NULL default '',
  `Information` text NOT NULL,
  `Address` text NULL,
  `Fax` varchar(249) NOT NULL default '',
  `Login` varchar(249) NOT NULL default '',
  `Domain` varchar(249) NOT NULL default '',
  `Password` varchar(249) NOT NULL default '',
  `javapassword` varchar(20) NOT NULL default '',
  `Age` int(11) NULL,
  `BirthDate` date,
  `CellularNumber` varchar(249) NOT NULL default '',
  `ICQNumber` int(11) NOT NULL default '0',
  `Gender` tinyint(4) NOT NULL default '0',
  `last` bigint(20) unsigned NOT NULL default '0',
  `countlogin` int(11) NOT NULL default '0',
  `rnid` tinyint(4) NOT NULL default '0',
  `Position` varchar(249) NOT NULL default '',
  `PositionDate` date,
  `PositionPrev` varchar(128) NOT NULL default '',
  `invalid_login` int(4) unsigned NOT NULL default '0',
  `isAD` int(11) unsigned default 0,
  `polls` blob,
  `Access_Level` int(11) unsigned NOT NULL default '5',
  `rang` int(11) unsigned NOT NULL default '0',
  `preferred_lang` tinyint(4) unsigned NOT NULL default '0',
  `blocked` tinyint(3) unsigned NOT NULL default '0',
  `block_message` text,
  `head_mid` int(11) unsigned default 0,
  `force_password` int(11) unsigned default 0,
  `lang` varchar(3) NOT NULL default 'rus',
  `need_edit` tinyint(3) unsigned NOT NULL default '0',
  `email_backup` varchar(249),
  `data_agreement` tinyint(1) unsigned NOT NULL default 0,
  `dublicate` int(11) unsigned default 0,
  `duplicate_of` int(11) unsigned default 0,
  `contact_displayed` tinyint(4) default 0,
  `push_token` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`MID`),
  INDEX `mid_blocked` (`mid`,`blocked`)
) ENGINE=MyISAM;

#
# Structure for the `periods` table :
#



CREATE TABLE IF NOT EXISTS `periods` (
  `lid` int(10) unsigned NOT NULL auto_increment,
  `starttime` int(11) NOT NULL default '540',
  `stoptime` int(11) NOT NULL default '630',
  `name` varchar(249) default NULL,
  `count_hours` int(11) default '2',
  PRIMARY KEY  (`lid`)
) ENGINE=MyISAM;

#
# Structure for the `permission2act` table :
#



CREATE TABLE IF NOT EXISTS `permission2act` (
  `pmid` int(11) unsigned NOT NULL default '0',
  `acid` varchar(8) NOT NULL default '',
  `type` varchar(220) NOT NULL default 'dean',
  PRIMARY KEY  (`pmid`,`acid`,`type`)
) ENGINE=MyISAM;

#
# Structure for the `permission2mid` table :
#



CREATE TABLE IF NOT EXISTS `permission2mid` (
  `pmid` int(11) unsigned NOT NULL default '0',
  `mid` int(11) unsigned default NULL,
  KEY `pmid_mid` (`pmid`,`mid`),
  KEY `pmid` (`pmid`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM;

#
# Structure for the `permission_groups` table :
#



CREATE TABLE IF NOT EXISTS `permission_groups` (
  `pmid` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  `default` int(11) default '0',
  `type` varchar(249) default 'dean',
  `rang` int(11) unsigned NOT NULL default '0',
  `application` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pmid`)
) ENGINE=MyISAM;

#
# Structure for the `ppt2swf` table :
#


CREATE TABLE IF NOT EXISTS `ppt2swf` (
  `status` int(11) NOT NULL default '0',
  `process` int(11) NOT NULL default '0',
  `success_date` datetime NULL,
  `pool_id` int(11) NOT NULL default '0',
  `url` varchar(249) NOT NULL default '',
  `webinar_id` int(11) NOT NULL
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `processes` (
  `process_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) default '' NOT NULL,
  `chain` text NOT NULL,
  `type` int(11) default 0 NOT NULL,
  `programm_id` int(11) default NULL,
  PRIMARY KEY  (`process_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `process_steps_data` (
  `process_step_id` int(11) NOT NULL auto_increment,
  `process_type` int(11) default NULL,
  `item_id` int(11) default NULL,
  `step` varchar(64) default NULL,
  `date_begin` datetime default NULL,
  `date_end` datetime default 0,
  PRIMARY KEY  (`process_step_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `programm` (
  `programm_id` int(11) NOT NULL auto_increment,
  `programm_type` tinyint(4) default 0 NOT NULL,
  `item_id` int(11) default NULL,
  `item_type` tinyint(4) default NULL,
  `mode_strict` tinyint(4) NOT NULL default 1,
  `mode_finalize` tinyint(4) NOT NULL default 0,
  `name` TEXT NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`programm_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `programm_events` (
  `programm_event_id` int(11) NOT NULL auto_increment,
  `programm_id` int(11) default 0 NOT NULL,
  `name` varchar(249) default '' NOT NULL,
  `type` int(11) default 0 NOT NULL,
  `item_id` int(11) default 0 NOT NULL,
  `day_begin` tinyint(4) default 1,
  `day_end` tinyint(4) default 1,
  `ordr` tinyint(4) default NULL,
  `isElective` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NULL DEFAULT '0',
  PRIMARY KEY  (`programm_event_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `programm_events_users` (
  `programm_event_user_id` int(11) NOT NULL auto_increment,
  `programm_event_id` int(11) NOT NULL,
  `programm_id` int(11) default NULL,
  `user_id` int(11) NOT NULL,
  `begin_date` datetime default NULL,
  `end_date` datetime default NULL,
  `status` int(11) default '0' NOT NULL,
  PRIMARY KEY  (`programm_event_user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `programm_users` (
  `programm_user_id` int(11) NOT NULL auto_increment,
  `programm_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assign_date` datetime NULL,
  PRIMARY KEY  (`programm_user_id`),
  KEY `programm_users_user_id` (`user_id`),
  KEY `programm_users_programm_id` (`programm_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `quest_clusters` (
  `cluster_id` int(11) NOT NULL AUTO_INCREMENT,
  `quest_id` INTEGER(11) NOT NULL,
  `name` varchar(249) DEFAULT NULL,
  PRIMARY KEY (`cluster_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `quest_categories` (
  `category_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `quest_id` INTEGER(11) DEFAULT NULL,
  `name` varchar(249) DEFAULT NULL,
  `description` text,
  `formula` text,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `quest_category_results` (
  `category_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `attempt_id` int(11) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `result` text,
  PRIMARY KEY (`category_result_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `quizzes` (
  `quiz_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(249) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  `subject_id` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  `calc_rating` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`quiz_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `quizzes_feedback` (
  `user_id` int(11) NOT NULL default '0',
  `subject_id` int(11) NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `begin` datetime,
  `end` datetime,
  `place` varchar(249) NOT NULL default '',
  `title` varchar(249) NOT NULL default '',
  `subject_name` varchar(249) NOT NULL default '',
  `trainer` varchar(249) NOT NULL default '',
  `trainer_id` int(11) NOT NULL default '0',
  `created` datetime,
  PRIMARY KEY  (`user_id`, `subject_id`, `lesson_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(249) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `subject_id` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`task_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `quizzes_answers` (
  `quiz_id` int(10) unsigned NOT NULL,
  `question_id` varchar(220) NOT NULL,
  `question_title` varchar(249)  NOT NULL default '',
  `theme` varchar(249)  NOT NULL default '',
  `answer_id` int(11) NOT NULL default '0',
  `answer_title` varchar(249)  NOT NULL default '',
  PRIMARY KEY  (`quiz_id`, `question_id`, `answer_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `quizzes_results` (
  `user_id` int(10) unsigned NULL,
  `lesson_id` int(10) unsigned NULL,
  `question_id` varchar(200) NULL,
  `answer_id` int(11) NOT NULL default '0',
  `freeanswer_data` text NOT NULL,
  `quiz_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NULL default '0',
  `junior_id` int(10) unsigned NOT NULL default '0',
  `link_id` int(11) unsigned NOT NULL default '0',
  KEY  (`user_id`, `lesson_id`, `question_id`, `answer_id`, `link_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `domain` varchar(249) default NULL,
  `name` varchar(249) default NULL,
  `fields` text,
  `created` datetime,
  `created_by` int(10) DEFAULT 0 NOT NULL,
  `status` tinyint(1) NULL default 0,
  PRIMARY KEY  (`report_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `responsibilities` (
  `responsibility_id` INT NOT NULL auto_increment,
  `user_id` INT NOT NULL ,
  `item_type` INT NOT NULL,
  `item_id` INT NOT NULL,
  `sv_scope` INT NULL,
  PRIMARY KEY (`responsibility_id`),
  KEY (`user_id`)
) ENGINE=MyISAM;


CREATE TABLE `timesheets` (
  `timesheet_id` int           NOT NULL,
  `user_id`      INT           NOT NULL,
  `action_type`  INT           NOT NULL,
  `description`  VARCHAR(5000) NULL,
  `action_date`  DATE          NOT NULL,
  `begin_time`   TIME          NOT NULL,
  `end_time`     TIME          NOT NULL,
  PRIMARY KEY (`timesheet_id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `recruiters` (
  `recruiter_id` INT NOT NULL auto_increment,
  `user_id` INT NULL ,
  `hh_auth_data` TEXT NULL,
  PRIMARY KEY (`recruiter_id`)
) ENGINE=MyISAM;



#
# Structure for the `rooms` table :
#



CREATE TABLE IF NOT EXISTS `rooms` (
  `rid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(249) default NULL,
  `volume` int(11) default NULL,
  `status` int(11) default NULL,
  `type` int(11) default NULL,
  `description` TEXT NULL,
  PRIMARY KEY  (`rid`)
) ENGINE=MyISAM;

#
# Structure for the `rooms2course` table :
#



CREATE TABLE IF NOT EXISTS `rooms2course` (
  `rid` int(11) default NULL,
  `cid` int(11) default NULL,
  KEY `rid_cid` (`rid`,`cid`),
  KEY `rid` (`rid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;

#
# Structure for the `schedule` table :
#



CREATE TABLE IF NOT EXISTS `schedule` (
  `SHEID` int(11) NOT NULL auto_increment,
  `title` varchar(249) NOT NULL default '',
  `url` text,
  `descript` text NOT NULL,
  `begin` datetime NOT NULL default 0,
  `end` datetime NOT NULL default 0,
  `createID` int(11) NOT NULL default '0',
  `createDate` datetime NULL,
  `typeID` int(11) NOT NULL default '0',
  `vedomost` int(11) default '0',
  `CID` int(11) NOT NULL default '0',
  `CHID` int(11) default NULL,
  `startday` int(11) NOT NULL DEFAULT '0',
  `stopday` int(11) NOT NULL DEFAULT '0',
  `timetype` int(11) NOT NULL DEFAULT '0',
  `isgroup` int(11) default '0',
  `cond_sheid` varchar(249) default '-1',
  `cond_mark` varchar(249) NOT NULL default '-',
  `cond_progress` varchar(249) NOT NULL default '0',
  `cond_avgbal` varchar(249) NOT NULL default '0',
  `cond_sumbal` varchar(249) NOT NULL default '0',
  `cond_operation` tinyint(3) unsigned NOT NULL default '0',
  `max_mark` int(10) NULL DEFAULT '0',
  `period` varchar(249) NOT NULL default '-1',
  `rid` int(11) NOT NULL default '0',
  `teacher` int(11) unsigned NOT NULL default '0',
  `moderator` int(11) unsigned NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `perm` int(11) NOT NULL default '0',
  `pub` tinyint(1) NOT NULL default '0',
  `sharepointId` int(11) NOT NULL default '0',
  `connectId` varchar(249) NOT NULL default '',
  `recommend` int(11) NOT NULL default '0',
  `notice` int(11) NOT NULL default '0',
  `notice_days` int(11) NOT NULL default '0',
  `all` int(11) NOT NULL default '0',
  `params` text,
  `activities` text,
  `order` int(11) default 0,
  `tool` varchar(255) NOT NULL default '',
  `isfree` tinyint(4) NOT NULL default '0',
  `has_proctoring` tinyint(4) NOT NULL default '0',
  `section_id` int(11) NULL,
  `session_id` int(11) NULL,
  `threshold` int(11) default NULL,
  `notify_before` int(11) NULL DEFAULT '0',
  `webinar_event_id` int(11) NULL,
  `material_id` int NULL,
  PRIMARY KEY  (`SHEID`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `typeID` (`typeID`),
  KEY `vedomost` (`vedomost`),
  KEY `CID` (`CID`),
  KEY `CHID` (`CHID`),
  KEY `period` (`period`),
  KEY `rid` (`rid`),
  KEY `gid` (`gid`),
  FULLTEXT INDEX `params` (`params`(100))
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `proctoring_files`;
CREATE TABLE `proctoring_files` (
	`proctoring_file_id` INT(11) NOT NULL auto_increment,
	`type` VARCHAR(50) NOT NULL,
	`SSID` INT(11) NOT NULL DEFAULT '0',
	`url` VARCHAR(1024) NULL DEFAULT NULL,
	`file_id` INT(11) NULL DEFAULT '0',
	`stamp` datetime NULL DEFAULT NULL,
  PRIMARY KEY  (`proctoring_file_id`)
)
ENGINE=MyISAM;

#
# Structure for the `scheduleid` table :
#



CREATE TABLE IF NOT EXISTS `scheduleID` (
  `SSID` int(11) NOT NULL auto_increment,
  `SHEID` int(11) NOT NULL default '0',
  `MID` int(11) NOT NULL default '0',
  `begin_personal` datetime NOT NULL default 0,
  `end_personal`   datetime NOT NULL default 0,
  `gid` int(11) default NULL,
  `isgroup` int(11) default '0',
  `V_STATUS` double NOT NULL default '-1',
  `V_DONE` int(11) NOT NULL default '0',
  `V_DESCRIPTION` varchar(249) NOT NULL default '',
  `DESCR` text,
  `SMSremind` tinyint(4) NOT NULL default '0',
  `ICQremind` tinyint(4) NOT NULL default '0',
  `EMAILremind` tinyint(4) NOT NULL default '0',
  `ISTUDremind` tinyint(4) NOT NULL default '0',
  `test_corr` int(11) NOT NULL default '0',
  `test_wrong` int(11) NOT NULL default '0',
  `test_date` datetime NOT NULL default 0,
  `test_answers` text,
  `test_tries` tinyint(4) default '0',
  `toolParams` text,
  `comments` text,
  `chief` tinyint(3) unsigned NOT NULL default '0',
  `created` datetime NULL,
  `updated` datetime NULL,
  `launched` datetime NULL,
  `passed_proctoring`  tinyint(4) NOT NULL default '0',
  `video_proctoring`  tinyint(4) NOT NULL default '0',
  `file_id` int(11) default NULL,
  `remote_event_id` int(11) default NULL,
  PRIMARY KEY  (`SSID`),
  KEY `MID` (`MID`),
  KEY `SHEID` (`SHEID`),
  KEY `SHEID_MID` (`SHEID`,`MID`)
) ENGINE=MyISAM;

#
# Structure for the `schedule_marks_history` table :
#



CREATE TABLE IF NOT EXISTS `schedule_marks_history`  (
	`MID`    	int(11) NOT NULL,
	`SSID`  	int(11) NOT NULL,
	`mark`   	int(11) NOT NULL DEFAULT '0',
	`updated`	datetime NOT NULL,
	KEY `MID` (`MID`),
        KEY `SSID` (`SSID`)
)ENGINE=MyISAM;


#
# Structure for the `seance` table :
#



CREATE TABLE IF NOT EXISTS `seance` (
  `stid` int(11) NOT NULL default '0',
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL default '0',
  `kod` varchar(249) NOT NULL default '',
  `attach` longblob NOT NULL,
  `filename` varchar(249) NOT NULL default '',
  `text` blob NOT NULL,
  `time` timestamp NULL,
  `bal` float default NULL,
  `lastbal` float default NULL,
  `comments` text,
  `review` blob NULL,
  `review_filename` varchar(249) NOT NULL default '',
  PRIMARY KEY  (`stid`,`kod`),
  KEY `mid` (`mid`),
  KEY `stid` (`stid`),
  KEY `cid` (`cid`),
  KEY `tid` (`tid`),
  KEY `kod` (`kod`)
) ENGINE=MyISAM;


CREATE TABLE `staff_units` (
  `staff_unit_id`          int          auto_increment,
  `staff_unit_id_external` varchar(249) DEFAULT NULL,
  `manager_staff_unit_id_external` varchar(249) DEFAULT NULL,
  `soid`                   int          DEFAULT NULL,
  `profile_id`             int          DEFAULT NULL,
  `name`                   varchar(249) DEFAULT NULL,
  `quantity`               int          DEFAULT 0,
  `quantity_text` varchar(50),
  PRIMARY KEY (`staff_unit_id`)
) ENGINE = MyISAM;

#
# Structure for the `structure_of_organ` table :
#



CREATE TABLE IF NOT EXISTS `structure_of_organ` (
  `soid` int(11) NOT NULL auto_increment,
  `soid_external` varchar(249) default NULL,
  `name` varchar(249) default NULL,
  `code` varchar(16) default NULL,
  `mid` int(11) default '0',
  `info` text,
  `owner_soid` int(11) default NULL,
  `profile_id` int(11) default NULL,
  `original_profile_id` int(11) default NULL,
  `agreem` tinyint(1) unsigned default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `own_results` tinyint(3) unsigned NOT NULL default '1',
  `enemy_results` tinyint(3) unsigned NOT NULL default '1',
  `display_results` tinyint(3) unsigned NOT NULL default '0',
  `threshold` tinyint(3) unsigned default 0 NOT NULL,
  `specialization` int(10) NOT NULL default '0',
  `claimant` tinyint(3) NOT NULL default '0',
  `org_id` int(10) DEFAULT '0',
  `lft` INT NOT NULL DEFAULT 0 ,
  `level` INT NOT NULL DEFAULT 0,
  `rgt` INT NOT NULL DEFAULT 0,
  `is_manager` INT NOT NULL DEFAULT 0,
  `position_date` DATE DEFAULT NULL,
  `blocked` INT NOT NULL DEFAULT 0,
  `employment_type` varchar(16) NULL,
  `employee_status` tinyint(1) NULL,
  `manager_soid` int DEFAULT NULL,
  `staff_unit_id` int DEFAULT NULL,
  `is_first_position` int DEFAULT NULL,
  `created_at` datetime,
  `deleted_at` datetime,
  `is_integration2` int DEFAULT '0',
  `deputy` int DEFAULT NULL,
  `last_at_session_id` INT NOT NULL DEFAULT 0,
  PRIMARY KEY  (`soid`),
  KEY `mid` (`mid`),
  KEY `owner_soid` (`owner_soid`),
  KEY `type` (`type`),
  KEY `claimant` (`claimant`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `structure_of_organ_history` (
  `soid` int(11) NOT NULL,
  `soid_external` varchar(249) default NULL,
  `name` varchar(249) default NULL,
  `code` varchar(16) default NULL,
  `mid` int(11) default '0',
  `info` text,
  `owner_soid` int(11) default NULL,
  `profile_id` int(11) default NULL,
  `original_profile_id` int(11) default NULL,
  `agreem` tinyint(1) unsigned default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `own_results` tinyint(3) unsigned NOT NULL default '1',
  `enemy_results` tinyint(3) unsigned NOT NULL default '1',
  `display_results` tinyint(3) unsigned NOT NULL default '0',
  `threshold` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `specialization` int(10) NOT NULL default '0',
  `claimant` tinyint(3) NOT NULL default '0',
  `org_id` int(10) DEFAULT '0',
  `lft` INT NOT NULL DEFAULT 0 ,
  `level` INT NOT NULL DEFAULT 0,
  `rgt` INT NOT NULL DEFAULT 0,
  `is_manager` INT NOT NULL DEFAULT 0,
  `position_date` DATE DEFAULT NULL,
  `blocked` INT NOT NULL DEFAULT 0,
  `employment_type` varchar(16) NULL,
  `employee_status` tinyint(1) NULL,
  `manager_soid` int DEFAULT NULL,
  `staff_unit_id` int DEFAULT NULL,
  `is_first_position` int DEFAULT NULL,
  `created_at` datetime,
  `deleted_at` datetime,
  `is_integration2` int DEFAULT '0',
  `deputy` int DEFAULT NULL,
  PRIMARY KEY  (`soid`),
  KEY `mid` (`mid`),
  KEY `owner_soid` (`owner_soid`),
  KEY `type` (`type`),
  KEY `claimant` (`claimant`)
) ENGINE=MyISAM;

#
# Structure for the `structure_organ_list` table :
#

CREATE TABLE IF NOT EXISTS `structure_organ_list` (
  `org_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(249) DEFAULT NULL,
  PRIMARY KEY (`org_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM;

#
# Structure for the `students` table :
#


CREATE TABLE IF NOT EXISTS `Students` (
  `SID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  `cgid` int(11) NOT NULL default '0',
  `Registered` int(11) NOT NULL default '1',
  `time_registered` timestamp NULL,
  `offline_course_path` varchar(249) NOT NULL default '',
  `time_ended` timestamp NULL,
  `time_ended_planned` timestamp NULL,
  `newcomer_id` int(11) NOT NULL default '0',
  `reserve_id` int(11) NOT NULL default '0',
  `application_id` int,
  `notified` int NULL default '0',
  `comment` varchar(249) NOT NULL default '',
  `programm_event_user_id` int(11) NOT NULL DEFAULT 0,
  `begin_personal` datetime NULL,
  `end_personal` datetime NULL,
  PRIMARY KEY  (`SID`),
  UNIQUE KEY `MID_CID` (`MID`,`CID`),
  KEY `CID` (`CID`),
  KEY `MID` (`MID`)
) ENGINE=MyISAM;

CREATE TABLE `soap_activities` (
  `activity_id` int           NOT NULL auto_increment,
  `direction`   int DEFAULT 0 NOT NULL,
  `request`     text,
  `response`    text,
  `method`      varchar(249)  NOT NULL,
  `created`     datetime,
  PRIMARY KEY (`activity_id`)
) ENGINE = MyISAM;


CREATE TABLE IF NOT EXISTS `Participants` (
  `participant_id` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  `cgid` int(11) NOT NULL default '0',
  `Registered` int(11) NOT NULL default '1',
  `time_registered` timestamp NULL,
  `offline_course_path` varchar(249) NOT NULL default '',
  `time_ended` timestamp NULL,
  `time_ended_planned` timestamp NULL,
  `begin_personal` datetime NULL,
  `end_personal` datetime NULL,
  `project_role` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`participant_id`),
  UNIQUE KEY `MID_CID` (`MID`,`CID`),
  KEY `CID` (`CID`),
  KEY `MID` (`MID`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `hrs` (
  `hr_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`hr_id`,`user_id`)
)  ENGINE=MyISAM;

#
# Structure for the `study_groups` table :
#


CREATE TABLE IF NOT EXISTS `study_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(249) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_auto` table :
#


CREATE TABLE IF NOT EXISTS `study_groups_auto` (
  `group_id` int(10) unsigned NOT NULL,
  `position_code` varchar(100) NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`position_code`,`department_id`),
  KEY `study_groups_auto_department_id` (`department_id`),
  KEY `study_groups_auto_group_id` (`group_id`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_courses` table :
#


CREATE TABLE IF NOT EXISTS `study_groups_courses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `group_id` int(10) NOT NULL DEFAULT '0',
  `course_id` int(10) NOT NULL DEFAULT '0',
  `lesson_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `course_id` (`course_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_custom` table :
#


CREATE TABLE IF NOT EXISTS `study_groups_custom` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_programms` table :
#


CREATE TABLE IF NOT EXISTS `study_groups_programms` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `group_id` int(10) NOT NULL DEFAULT '0',
  `programm_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `programm_id` (`programm_id`)
) ENGINE=MyISAM;

#
# Structure for the `teachers` table :
#


CREATE TABLE IF NOT EXISTS tag
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `body`       varchar(249) NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `tag_ref`
(
  `tag_id`  INT(10) UNSIGNED NOT NULL,
  `item_type` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`tag_id`, `item_type`, `item_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `moderators` (
  `moderator_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`moderator_id`),
  UNIQUE KEY `UID_PRID` (`user_id`,`project_id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `Teachers` (
  `PID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`PID`),
  UNIQUE KEY `MID_CID` (`MID`,`CID`),
  KEY `MID` (`MID`),
  KEY `CID` (`CID`)
) ENGINE=MyISAM;

#
#
# Structure for the `test` table :
#



CREATE TABLE IF NOT EXISTS `test` (
  `tid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `cidowner` int(11) NOT NULL default '0',
  `title` varchar(249) NOT NULL default '',
  `datatype` int(11) NOT NULL default '0',
  `data` text NOT NULL,
  `random` tinyint(4) NOT NULL default '0',
  `lim` int(11) NOT NULL default '0',
  `qty` tinyint(4) NOT NULL default '1',
  `sort` tinyint(4) NOT NULL default '0',
  `free` tinyint(4) NOT NULL default '0',
  `skip` tinyint(4) NOT NULL default '0',
  `rating` tinyint(4) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  `questres` tinyint(4) NOT NULL default '0',
  `endres` tinyint(4) NOT NULL default '1',
  `showurl` tinyint(4) NOT NULL default '1',
  `showotvet` tinyint(4) NOT NULL default '0',
  `timelimit` smallint(6) NOT NULL default '0',
  `startlimit` int(11) NOT NULL default '1',
  `limitclean` mediumint(5) NOT NULL default '0',
  `last` int(11) NOT NULL default '0',
  `lastmid` int(11) NOT NULL default '0',
  `cache_qty` int(11) NOT NULL default '0',
  `random_vars` text,
  `allow_view_log` int(11) NOT NULL default '1',
  `created_by` int(11) unsigned NOT NULL default '0',
  `comments` text,
  `mode` tinyint(3) unsigned NOT NULL default '0',
  `is_poll` tinyint(4) NOT NULL default '0',
  `poll_mid` int(11) NOT NULL default '0',
  `test_id` int(11) NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `threshold` int(11) NOT NULL default '75',
  `adaptive` int(11) NOT NULL default '0',
  PRIMARY KEY  (`tid`),
  KEY `cid` (`cid`),
  KEY `is_poll` (`is_poll`),
  KEY `poll_mid` (`poll_mid`),
  KEY `test_id` (`test_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `test_abstract` (
  `test_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(249) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `keywords` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  `subject_id` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`test_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `test_feedback` (
  `test_feedback_id` int(11) NOT NULL auto_increment,
  `title` varchar(249) default '' NOT NULL,
  `type` int(11) default 0 NOT NULL,
  `text` text,
  `parent` int(11) default 0 NOT NULL,
  `treshold_min` int(11) default 0 NOT NULL,
  `treshold_max` int(11) default 0 NOT NULL,
  `test_id` int(11) default 0 NOT NULL,
  `question_id` varchar(45) default '' NOT NULL,
  `answer_id` varchar(45) default '' NOT NULL,
  `show_event` int(11) NOT NULL default '0',
  `show_on_values` text,
  PRIMARY KEY  (`test_feedback_id`),
  KEY `parent` (`parent`),
  KEY `type` (`type`),
  KEY `treshold` (`treshold_min`,`treshold_max`),
  KEY `test_id` (`test_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `tests_questions` (
  `subject_id` int(11) NOT NULL DEFAULT 0,
  `test_id` int(11) NOT NULL DEFAULT 0,
  `kod` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY  (`subject_id`, `test_id`, `kod`),
  KEY `kod` (`kod`),
  KEY `subject_id` (`subject_id`),
  KEY `test_id` (`test_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `exercises` (
  `exercise_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(249) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  `subject_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`exercise_id`)
) ENGINE=MyISAM;


#
# Structure for the `testcount` table :
#



CREATE TABLE IF NOT EXISTS `testcount` (
  `mid` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `qty` smallint(5) unsigned NOT NULL default '0',
  `last` int(10) unsigned NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`tid`, `cid`, `lesson_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `updates` (
  `update_id` int(11) NOT NULL default '0',
  `version` varchar(249) NOT NULL default '',
  `created` datetime default NULL,
  `created_by` int(11) NOT NULL default '0',
  `updated` datetime default NULL,
  `organization` varchar(249) NOT NULL default '',
  `description` text,
  `servers` text,
  PRIMARY KEY  (`update_id`)
) ENGINE=MyISAM;

#
# Structure for the `user_login_log` table :
#



CREATE TABLE IF NOT EXISTS `user_login_log` (
  `login` varchar(249) default NULL,
  `date` datetime default NULL,
  `event_type` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `comments` varchar(249) default NULL,
  `ip` int(11) NOT NULL default '0'
  /*PRIMARY KEY  (`login`, `date`) валится при авто-логауте сразу после логина */
) ENGINE=MyISAM;




CREATE TABLE IF NOT EXISTS `scorm_tracklog` (
  `trackID` int(10) unsigned NOT NULL auto_increment,
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `ModID` int(10) unsigned NOT NULL default '0',
  `McID` int(10) unsigned NOT NULL default '0',
  `lesson_id` int(10) unsigned NOT NULL default '0',
  `trackdata` blob NOT NULL,
  `stop` datetime NOT NULL default 0,
  `start` datetime NOT NULL default 0,
  `score` float NOT NULL default '0',
  `scoremax` float NOT NULL default '0',
  `scoremin` float NOT NULL default '0',
  `status` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`trackID`),
  KEY `mid_lesson_id` (`mid`, `lesson_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `scorm_report` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `mid` int(10) unsigned NOT NULL default '0',
  `cid`         int(10)          NOT NULL DEFAULT 0,
  `lesson_id` int(10) unsigned NOT NULL default '0',
  `report_data` blob NOT NULL,
  `updated` datetime NOT NULL default 0,
  PRIMARY KEY  (`report_id`)
) ENGINE=MyISAM;




CREATE TABLE IF NOT EXISTS `sessions` (
  `sessid` int(10) unsigned NOT NULL auto_increment,
  `sesskey` varchar(32) NOT NULL default '',
  `mid` int(10) unsigned NOT NULL default '0',
  `course_id` int(10) unsigned NULL,
  `lesson_id` int(10) unsigned NULL,
  `lesson_type` int(10) unsigned NULL,
  `start` datetime NOT NULL default 0,
  `stop` datetime NOT NULL default 0,
  `ip` varchar(16) NOT NULL default '',
  `logout` tinyint(3) NOT NULL default '0',
  `browser_name` VARCHAR(64) NULL DEFAULT NULL,
  `browser_version` VARCHAR(64) NULL DEFAULT NULL,
  `flash_version` VARCHAR(64) NULL DEFAULT NULL,
  `os` VARCHAR(64) NULL DEFAULT NULL,
  `screen` VARCHAR(64) NULL DEFAULT NULL,
  `cookie` TINYINT(1) NULL DEFAULT NULL,
  `js` TINYINT(1) NULL DEFAULT NULL,
  `java_version` VARCHAR(64) NULL DEFAULT NULL,
  `silverlight_version` VARCHAR(64) NULL DEFAULT NULL,
  `acrobat_reader_version` VARCHAR(64) NULL DEFAULT NULL,
  `msxml_version` VARCHAR(64) NULL DEFAULT NULL,
  PRIMARY KEY  (`sessid`),
  KEY `mid` (`mid`),
  KEY `start` (`start`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `session_guest` (
  `session_guest_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `start` DATETIME NULL DEFAULT NULL ,
  `stop` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`session_guest_id`),
  KEY `start` (`start`),
  KEY `stop` (`stop`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `interesting_facts` (
  `interesting_facts_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `title` TEXT NULL DEFAULT NULL ,
  `text` TEXT NULL DEFAULT NULL ,
  `status` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`interesting_facts_id`) )
ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `library` (
  `bid` int(10) unsigned NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `cats` text,
  `mid` int(10) unsigned NOT NULL default '0',
  `uid` varchar(249) NOT NULL default '',
  `title` varchar(249) NOT NULL default '',
  `author` varchar(249) NOT NULL default '',
  `publisher` varchar(249) NOT NULL default '',
  `publish_date` varchar(4) NOT NULL default '',
  `description` text NOT NULL,
  `keywords` text NOT NULL,
  `filename` varchar(249) NOT NULL default '',
  `location` varchar(249) NOT NULL default '',
  `metadata` blob NOT NULL,
  `need_access_level` int(10) unsigned NOT NULL default '5',
  `upload_date` datetime NOT NULL default 0,
  `is_active_version` tinyint(3) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `is_package` tinyint(3) unsigned NOT NULL default '0',
  `quantity` int(11) unsigned NOT NULL default '0',
  `content` varchar(249) NOT NULL default '',
  `scorm_params` text NOT NULL,
  `pointId` int(11) NOT NULL default '0',
  `courses` varchar(249) default '' NOT NULL,
  `lms` int(11) NOT NULL default '0',
  `cms` int(11) NOT NULL default '0',
  `place` varchar(249) default '' NOT NULL,
  `not_moderated` int(11) NOT NULL default '0',
  PRIMARY KEY  (`bid`),
  KEY `cid` (`cid`),
  KEY `need_access_level` (`need_access_level`),
  KEY `is_active_version` (`is_active_version`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `library_categories` (
  `catid` varchar(249) NOT NULL default '',
  `name` varchar(249) NOT NULL default '',
  `parent` varchar(249) NOT NULL default '',
  PRIMARY KEY  (`catid`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `library_index` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` int(10) NOT NULL default '0',
  `file` varchar(249) NOT NULL default '',
  `keywords` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `courses_marks` (
  `cid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `mark` int(10) NOT NULL default '-1',
  `alias` varchar(249) NOT NULL default '',
  `confirmed` tinyint(4) NOT NULL default '0',
  `comments` text NOT NULL,
  `certificate_validity_period` int(10),
  `date`  datetime NULL default NULL,
  PRIMARY KEY  (`cid`,`mid`),
  KEY `cid` (`cid`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `hacp_debug` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `message` text NOT NULL,
  `date` datetime NOT NULL default 0,
  `direction` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `help` (
  `help_id` int(10) unsigned NOT NULL auto_increment,
  `role` varchar(249) NULL default '',
  `module` varchar(249),
  `app_module` varchar(25),
  `controller` varchar(249),
  `action` varchar(249),
  `link_subject` tinyint(3) unsigned NOT NULL default '0',
  `is_active_version` tinyint(3),
  `link` varchar(249),
  `title` varchar(249),
  `text` text NOT NULL,
  `moderated` tinyint(3) unsigned NOT NULL default '0',
  `lang` varchar(3) NOT NULL default '',
  PRIMARY KEY  (`help_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `holidays` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(249) DEFAULT NULL,
  `date` DATE NOT NULL,
  `type` TINYINT(4) NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `htmlpage` (
  `page_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned,
  `name` varchar(249) NULL default '',
  `ordr` int(11) NOT NULL default '10',
  `text` text NOT NULL,
  `url` varchar(249) NOT NULL DEFAULT '',
  `description` VARCHAR(5000),
  `icon_url` varchar(249) NULL,
  `visible` tinyint(4) DEFAULT '0',
  `in_slider` tinyint(4) DEFAULT '0',
  PRIMARY KEY  (`page_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `htmlpage_groups` (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `lft` int(10) unsigned,
  `rgt` int(10) unsigned,
  `level` int(10) unsigned,
  `name` varchar(249) default '',
  `ordr` int(11) NOT NULL default '10',
  `role` varchar(249) default '',
  `is_single_page` tinyint(1) default NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `crontask` (
  `crontask_id` varchar(249) NOT NULL default '',
  `crontask_runtime` int(11) unsigned default NULL,
  `crontask_endtime` int(11) unsigned default NULL
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `managers` (
  `id` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `sequence_current` (
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `current` varchar(249) NOT NULL default '',
  `subject_id` int(10) NOT NULL default '0',
  `lesson_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`,`subject_id`,`lesson_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `sequence_history` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`mid` INT UNSIGNED NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
`item` varchar(249) NOT NULL default '',
`date` DATETIME NOT NULL ,
`subject_id` int(10) NOT NULL default '0',
`lesson_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `developers` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `methodologist` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `supervisors` (
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `employee` (
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `providers` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(249) NOT NULL DEFAULT '',
  `address` text NULL,
  `contacts` text NULL,
  `description` text NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `suppliers` (
  `supplier_id` int(11) NOT NULL auto_increment,
  `title` varchar(249) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `contacts` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`supplier_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `webinar_answers` (
  `aid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL,
  `text` varchar(249) default NULL,
  PRIMARY KEY  (`aid`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinars` (
  `webinar_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) NOT NULL,
  `create_date` datetime NULL,
  `subject_id` int(11) NOT NULL,
  `subject` varchar(50) NOT NULL DEFAULT 'subject',
  PRIMARY KEY  (`webinar_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `webinar_chat` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `message` varchar(249) NULL default '',
    `datetime` datetime NULL,
    `userId` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_files` (
    `webinar_id` int(11) NOT NULL default '0',
    `file_id` int(11) NOT NULL default '0',
    `num` int(11) NOT NULL default '0'
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `webinar_history` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `userId` int(11) NOT NULL default '0',
    `action` varchar(249) NOT NULL default '',
    `item` varchar(249) NOT NULL default '',
    `datetime` datetime NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_plan` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `href` varchar(249) NOT NULL default '',
    `title` varchar(249) NOT NULL default '',
    `bid` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_plan_current` (
    `pointId` int(11) NOT NULL,
    `currentItem` int(11) NOT NULL default '0',
    PRIMARY KEY  (`pointId`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_questions` (
  `qid` int(11) NOT NULL auto_increment,
  `text` varchar(249) default NULL,
  `type` tinyint(1) default NULL,
  `point_id` int(11) default NULL,
  `is_voted` tinyint(1) default NULL,
  PRIMARY KEY  (`qid`),
  UNIQUE KEY `text` (`text`,`point_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_users` (
    `pointId` int(11) NOT NULL,
    `userId` int(11) NOT NULL,
    `last` datetime NOT NULL default 0,
    PRIMARY KEY  (`pointId`,`userId`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_votes` (
  `vid` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `qid` int(11) default NULL,
  `aid` int(11) default NULL,
  PRIMARY KEY  (`vid`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_whiteboard` (
  `actionId` int(11) NOT NULL auto_increment,
  `pointId` int(11) default NULL,
  `userId` int(11) default NULL,
  `actionType` varchar(249) default NULL,
  `datetime` datetime default NULL,
  `color` int(11) default NULL,
  `tool` int(11) default NULL,
  `text` text,
  `width` int(11) default NULL,
  `height` int(11) default NULL,
  PRIMARY KEY  (`actionId`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_whiteboard_points` (
  `pointId` int(11) NOT NULL auto_increment,
  `actionId` int(11) default NULL,
  `x` int(11) default NULL,
  `y` int(11) default NULL,
  `type` int(11) default NULL,
  PRIMARY KEY  (`pointId`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `webinar_records` (
  `id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NULL,
  `webinar_id` int(11) NULL,
  `name` varchar(249) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `wiki_articles`
(
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` DATETIME,
  `title`   varchar(249) NOT NULL,
  `subject_name` varchar(249),
  `subject_id` INT(10) UNSIGNED NOT NULL,
  `lesson_id` INT(10) UNSIGNED NULL default NULL,
  `changed` DATETIME,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `wiki_archive`
(
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id`    INT(10) UNSIGNED NOT NULL,
  `created` DATETIME,
  `author`  INT(10) UNSIGNED NOT NULL,
  `body`    LONGTEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `video` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(249) DEFAULT NULL,
  `created` int(11) unsigned NOT NULL default '0',
  `title` varchar(249) NOT NULL default '',
  `main_video` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `resources` (
  `resource_id` int(10) unsigned NOT NULL auto_increment,
  `resource_id_external` varchar(249)  NULL,
  `title` varchar(249) NULL,
  `url` varchar(249) NOT NULL DEFAULT '',
  `volume` varchar(249) NOT NULL default '0',
  `filename` varchar(249) NOT NULL,
  `type` int(11) NOT NULL default '0',
  `filetype` int(11) NOT NULL default '0',
  `edit_type`            int(11)          NOT NULL default '0',
  `description` text NOT NULL,
  `content` text,
  `created` datetime,
  `updated` datetime,
  `created_by` int(10) unsigned NOT NULL default '0',
  `services` int(11) default '0' NOT NULL,
  `subject` varchar(50) NOT NULL default 'subject',
  `subject_id` int(11) default '0' NOT NULL,
  `status` int(11) default '0' NOT NULL,
  `location` int(11) default '0' NOT NULL,
  `db_id` varchar(249) NOT NULL default '',
  `test_id` int(11) default '0' NOT NULL,
  `activity_id` int(11) default '0' NOT NULL,
  `activity_type` int(11) default '0' NOT NULL,
  `related_resources` text,
  `parent_id` int(11) default '0' NOT NULL,
  `parent_revision_id` int(11) default '0' NOT NULL,
  `external_viewer` varchar(16) NOT NULL,
  `storage_id` int NOT NULL  DEFAULT 0,
  PRIMARY KEY  (`resource_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `scales` (
  `scale_id` int(11) NOT NULL auto_increment,
  `name` varchar(249) NOT NULL,
  `description` text,
  `type` tinyint(4) NOT NULL default '0',
  `mode` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`scale_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `scale_values` (
  `value_id` int(11) NOT NULL auto_increment,
  `scale_id` int(11) NOT NULL,
  `value` int(11) NOT NULL default '0',
  `text` varchar(249) default NULL,
  `description` text,
  PRIMARY KEY  (`value_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `resource_revisions` (
  `revision_id` int(10) unsigned NOT NULL auto_increment,
  `resource_id` int(10) unsigned NOT NULL,
  `url` varchar(249) NOT NULL DEFAULT '',
  `volume` varchar(249) NOT NULL default '0',
  `filename` varchar(249) NOT NULL,
  `filetype` int(11) NOT NULL default '0',
  `content` text,
  `updated` datetime,
  `created_by` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`revision_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `projects` (
  `projid` int(11) NOT NULL auto_increment,
  `external_id` varchar(45) default NULL,
  `code` varchar(249) default NULL,
  `name` varchar(249) default NULL,
  `shortname` varchar(32) default NULL,
  `supplier_id` int(11) default NULL,
  `description` text,
  `type` varchar(45) default NULL,
  `reg_type` varchar(45) default NULL,
  `begin` datetime default NULL,
  `end` datetime default NULL,
  `begin_planned` datetime default NULL,
  `end_planned` datetime default NULL,
  `longtime` int(11) DEFAULT NULL,
  `price` float default NULL,
  `price_currency` varchar(25) default NULL,
  `plan_users` int(11) default NULL,
  `services` int(11) default '0' NOT NULL,
  `period` tinyint(4) default '0' NOT NULL,
  `period_restriction_type` tinyint(4) NOT NULL default '0',
  `created` datetime NOT NULL,
  `last_updated` datetime default NULL,
  `access_mode` int(11) default '0' NOT NULL,
  `access_elements` int(11) default NULL,
  `mode_free_limit` int(11) default NULL,
  `auto_done` int(11) default '0' NOT NULL,
  `base` int(11) default '0' NOT NULL,
  `base_id` int(11) default '0' NOT NULL,
  `base_color` varchar(45) default NULL,
  `claimant_process_id` int(11) default '0' NOT NULL,
  `state` tinyint(4) default '0',
  `default_uri` varchar(249) default NULL,
  `scale_id` int(11) default '0',
  `auto_mark` tinyint(4) default '0',
  `auto_graduate` tinyint(4) default '0',
  `formula_id` int(11) default NULL,
  `threshold` int(11) default NULL,
  `is_public` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `protocol` varchar(249) NULL,
  PRIMARY KEY  (`projid`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `type` (`type`),
  KEY `reg_type` (`reg_type`)
) ENGINE=MyISAM;

CREATE TABLE `tasks_variants` (
  `variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `name` varchar(249) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`variant_id`)
) ENGINE=MyISAM;

CREATE TABLE `meetings` (
  `meeting_id`      int(11)             NOT NULL auto_increment,
  `title`           varchar(249)        NOT NULL,
  `url`             text,
  `descript`        text                NOT NULL,
  `begin`           datetime            NOT NULL default 0,
  `end`             datetime            NOT NULL default 0,
  `createID`        int(11)             NOT NULL default '0',
  `createDate`      datetime            NULL,
  `typeID`          int(11)             NOT NULL default '0',
  `vedomost`        int(11)                      default '0',
  `CID`             int(11)             NOT NULL default '0',
  `project_id`      int(11)                      default NULL,
  `startday`        int(11)             NOT NULL DEFAULT '0',
  `stopday`         int(11)             NOT NULL DEFAULT '0',
  `timetype`        int(11)             NOT NULL DEFAULT '0',
  `isgroup`         int(11)              default '0',
  `cond_project_id` varchar(249)                 default '-1',
  `cond_mark`       varchar(249)        NOT NULL default '-',
  `cond_progress`   varchar(249)        NOT NULL default '0',
  `cond_avgbal`     varchar(249)        NOT NULL default '0',
  `cond_sumbal`     varchar(249)        NOT NULL default '0',
  `cond_operation`  tinyint(3) unsigned NOT NULL default '0',
  `max_mark` int NOT NULL default 0,
  `period`          varchar(249)        NOT NULL default '-1',
  `rid`             int(11)             NOT NULL default '0',
  `moderator`       int(11) unsigned    NOT NULL default '0',
  `gid`             int(11)                      default '-1',
  `perm`            int(11)             NOT NULL default '0',
  `pub`             tinyint(1)          NOT NULL default '0',
  `sharepointId`    int(11)             NOT NULL default '0',
  `connectId`       varchar(249)        NOT NULL,
  `recommend`       int(11)     NOT NULL default '0',
  `notice`          int(11)             NOT NULL default '0',
  `notice_days`     int(11)             NOT NULL default '0',
  `all`             int(11)     NOT NULL default '0',
  `params`          text,
  `activities`      text,
  `order`           int(11)                      default 0,
  `tool`            varchar(249)        NOT NULL,
  `isfree`          tinyint(4)          NOT NULL default '0',
  `section_id`      int(11)             NULL,
  PRIMARY KEY (`meeting_id`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `typeID` (`typeID`),
  KEY `vedomost` (`vedomost`),
  KEY `project_id` (`project_id`),
  KEY `period` (`period`),
  KEY `rid` (`rid`),
  KEY `gid` (`gid`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `projects_marks` (
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `mid` int(10) unsigned NOT NULL DEFAULT '0',
  `mark` varchar(249) NOT NULL DEFAULT '-1',
  `alias` varchar(249) NOT NULL DEFAULT '',
  `confirmed` tinyint(4) NOT NULL DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`cid`,`mid`),
  KEY `cid` (`cid`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM;

#
# Structure for the `meetings` table :
#


CREATE TABLE IF NOT EXISTS `meetings` (
  `meeting_id` int(11) NOT NULL auto_increment,
  `title` varchar(249) NOT NULL default '',
  `url` text,
  `descript` text NOT NULL,
  `begin` datetime NOT NULL default 0,
  `end` datetime NOT NULL default 0,
  `createID` int(11) NOT NULL default '0',
  `createDate` datetime NULL,
  `typeID` int(11) NOT NULL default '0',
  `vedomost` int(11) default '0',
  `CID` int(11) NOT NULL default '0',
  `project_id` int(11) default NULL,
  `startday` int(11) NOT NULL DEFAULT '0',
  `stopday` int(11) NOT NULL DEFAULT '0',
  `timetype` int(11) NOT NULL DEFAULT '0',
  `isgroup` int(11) default '0',
  `cond_project_id` varchar(249) default '-1',
  `cond_mark` varchar(249) NOT NULL default '-',
  `cond_progress` varchar(249) NOT NULL default '0',
  `cond_avgbal` varchar(249) NOT NULL default '0',
  `cond_sumbal` varchar(249) NOT NULL default '0',
  `cond_operation` tinyint(3) unsigned NOT NULL default '0',
  `period` varchar(249) NOT NULL default '-1',
  `rid` int(11) NOT NULL default '0',
  `moderator` int(11) unsigned NOT NULL default '0',
  `gid` int(11) default '-1',
  `perm` int(11) NOT NULL default '0',
  `pub` tinyint(1) NOT NULL default '0',
  `sharepointId` int(11) NOT NULL default '0',
  `connectId` varchar(249) NOT NULL default '',
  `recommend` int(11) NOT NULL default '0',
  `notice` int(11) NOT NULL default '0',
  `notice_days` int(11) NOT NULL default '0',
  `all` int(11) NOT NULL default '0',
  `params` text,
  `activities` text,
  `order` int(11) default 0,
  `tool` varchar(249) NOT NULL default '',
  `isfree` tinyint(4) NOT NULL default '0',
  `section_id` int(11) NULL,
  PRIMARY KEY  (`meeting_id`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `typeID` (`typeID`),
  KEY `vedomost` (`vedomost`),
  KEY `project_id` (`project_id`),
  KEY `period` (`period`),
  KEY `rid` (`rid`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM;

#
# Structure for the `meetingsid` table :
#



CREATE TABLE IF NOT EXISTS `meetingsID` (
  `SSID` int(11) NOT NULL auto_increment,
  `meeting_id` int(11) NOT NULL default '0',
  `MID` int(11) NOT NULL default '0',
  `begin_personal` datetime DEFAULT NULL,
  `end_personal` datetime DEFAULT NULL,
  `beginRelative` datetime DEFAULT NULL,
  `endRelative` datetime DEFAULT NULL,
  `gid` int(11) default NULL,
  `isgroup` int(11) default '0',
  `V_STATUS` double NOT NULL default '-1',
  `V_DONE` int(11) NOT NULL default '0',
  `V_DESCRIPTION` varchar(249) NOT NULL default '',
  `DESCR` text,
  `SMSremind` tinyint(4) NOT NULL default '0',
  `ICQremind` tinyint(4) NOT NULL default '0',
  `EMAILremind` tinyint(4) NOT NULL default '0',
  `ISTUDremind` tinyint(4) NOT NULL default '0',
  `test_corr` int(11) NOT NULL default '0',
  `test_wrong` int(11) NOT NULL default '0',
  `test_date` datetime NOT NULL default 0,
  `test_answers` text,
  `test_tries` tinyint(4) default '0',
  `toolParams` text,
  `comments` text,
  `chief` tinyint(3) unsigned NOT NULL default '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `launched` datetime NULL,
  PRIMARY KEY  (`SSID`),
  KEY `MID` (`MID`),
  KEY `meeting_id` (`meeting_id`),
  KEY `meeting_id_MID` (`meeting_id`,`MID`)
) ENGINE=MyISAM;

CREATE TABLE `meetings_marks_history` (
  `MID`     int      NOT NULL,
  `SSID`    int      NOT NULL,
  `mark`    int      NOT NULL DEFAULT 0,
  `updated` datetime NOT NULL
) ENGINE = MyISAM;


CREATE TABLE IF NOT EXISTS `subjects` (
  `subid` int(11) NOT NULL auto_increment,
  `is_labor_safety` int(11) default 0 NOT NULL,
  `external_id` varchar(45) default NULL,
  `code` varchar(249) default NULL,
  `name` varchar(1024) NULL,
  `shortname` varchar(1024) NULL,
  `supplier_id` int(11) default NULL,
  `short_description` varchar(1024) ,
  `description` text,
  `type` varchar(45) default NULL,
  `reg_type` varchar(45) default NULL,
  `begin` datetime default NULL,
  `end` datetime default NULL,
  `begin_planned` datetime default NULL,
  `end_planned` datetime default NULL,
  `longtime` int(11) DEFAULT NULL,
  `price` float default NULL,
  `price_currency` varchar(25) default NULL,
  `plan_users` int(11) default NULL,
  `services` int(11) default '0' NOT NULL,
  `period` tinyint(4) default '0' NOT NULL,
  `created` datetime default NULL,
  `period_restriction_type` tinyint(4) NOT NULL default '0',
  `last_updated` datetime default NULL,
  `access_mode` int(11) default '0' NOT NULL,
  `access_elements` int(11) default NULL,
  `mode_free_limit` int(11) default NULL,
  `auto_done` int(11) default '0' NOT NULL,
  `base` int(11) default '0' NOT NULL,
  `base_id` int(11) default '0' NOT NULL,
  `base_color` varchar(45) default NULL,
  `claimant_process_id` int(11) default '0' NOT NULL,
  `state` tinyint(4) default '0',
  `default_uri` varchar(249) default NULL,
  `scale_id` int(11) default '0',
  `auto_mark` tinyint(4) default '0',
  `auto_graduate` tinyint(4) default '0',
  `formula_id` int(11) default NULL,
  `threshold` int(11) default NULL,
  `in_slider` int(11) default '0' NOT NULL,
  `in_banner` tinyint(4) default '0',
  `create_from_tc_session` int,
  `provider_id` int,
  `status` int,
  `format` int,
  `criterion_id` int,
  `criterion_type` int,
	`created_by` int,
	`category` int,
	`city` int,
    `primary_type` int,
    `mark_required` int,
    `check_form` int,
	`provider_type` int default '2',
    `after_training` int,
    `feedback` int,
    `education_type` int default '2' NOT NULL,
    `rating` float NULL,
	`direction_id` int,
    `banner_url` varchar(249) NULL,
    `auto_notification` int NOT NULL,
  PRIMARY KEY  (`subid`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `type` (`type`),
  KEY `reg_type` (`reg_type`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subjects_courses` (
  `subject_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`course_id`)
) ENGINE=MyISAM;

CREATE TABLE `subjects_feedback_users` (
  `feedback_user_id` int NOT NULL auto_increment,
  `user_id`          int NOT NULL,
  `feedback_id`      int NOT NULL,
  `subordinate_id`   int NULL,
  PRIMARY KEY (`feedback_user_id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `subjects_quests` (
  `subject_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`quest_id`),
  KEY  `subject_id` (`subject_id`),
  KEY `quest_id` (`quest_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subjects_exercises` (
  `subject_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`exercise_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subjects_resources` (
  `subject_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `subject` varchar(50) NOT NULL default 'subject',
  PRIMARY KEY  (`subject_id`,`resource_id`,`subject`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subjects_quizzes` (
  `subject_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`quiz_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subjects_tasks` (
  `subject_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`task_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `subject_criteria` (
  `subject_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `criterion_type` int(11) NOT NULL,
  PRIMARY KEY (`subject_id`, `criterion_id`, `criterion_type`)
) ENGINE=MyISAM;

CREATE TABLE `tc_applications` (
  `application_id`            int          NOT NULL auto_increment,
  `session_id`                int          NULL,
  `session_quarter_id`        int          NULL,
  `session_department_id`     int          NULL,
  `department_application_id` int          NULL,
  `department_id`             int          NULL,
  `user_id`                   int          NULL,
  `position_id`               int          NULL,
  `provider_id`               int          NULL,
  `subject_id`                int          NULL,
  `period`                    varchar(16)  NULL,
  `criterion_id`              int          NULL,
  `category`                  int          NULL,
  `created`                   datetime     NULL,
  `expire`                    date         NULL,
  `primary_type`              int          NULL,
  `criterion_type`            int          NULL,
  `status`                    int          NULL,
  `department_goal`           varchar(249) null,
  `education_goal`            varchar(249) null,
  `cost_item`                 int          NULL,
  `price`                     int          NULL,
  `price_employee`            int          NULL,
  `event_name`                varchar(249) null,
  `initiator`                 int          NOT NULL DEFAULT 0,
  `payment_type`              int          NOT NULL DEFAULT 0,
  `payment_percent`           int          NOT NULL DEFAULT 0,
  `parent_application_id`            int          NULL,
  `deleted`            int          NULL,
  `study_status` int,
  `origin_type` int,
  PRIMARY KEY (`application_id`)
) ENGINE = MyISAM;


CREATE TABLE `tc_applications_impersonal` (
  `application_impersonal_id` int          NOT NULL auto_increment,
  `session_id`                int          NULL,
  `session_quarter_id`        int          NULL,
  `session_department_id`     int          NULL,
  `department_application_id` int          NULL,
  `department_id`             int          NULL,
  `provider_id`               int          NULL,
  `subject_id`                int          NULL,
  `period`                    varchar(16)  NULL,
  `criterion_id`              int          NULL,
  `category`                  int          NULL,
  `created`                   datetime     NULL,
  `expire`                    date         NULL,
  `primary_type`              int          NULL,
  `criterion_type`            int          NULL,
  `status`                    int          NULL,
  `cost_item`                 int          NULL,
  `price`                     int          NULL,
  `quantity`                  int          NULL,
  `event_name`                varchar(249) null,
  PRIMARY KEY (`application_impersonal_id`)
) ENGINE = MyISAM;


CREATE TABLE `tc_corporate_learning` (
  `corporate_learning_id` int          NOT NULL auto_increment,
  `name`                  varchar(249) NULL,
  `month`                 datetime     NULL,
  `cycle_id`              int          NULL,
  `cost_for_organizer`    varchar(249) NULL,
  `organizer_id`          int          NULL,
  `manager_name`          varchar(249) NULL,
  `people_count`          varchar(249) NULL,
  `meeting_type`          int          NULL,
  PRIMARY KEY (`corporate_learning_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_corporate_learning_participant` (
  `participant_id`        int NOT NULL,
  `corporate_learning_id` int NOT NULL,
  `cost`                  int NULL,
  PRIMARY KEY (`participant_id`, `corporate_learning_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_department_applications` (
  `department_application_id` int  NOT NULL auto_increment,
  `department_id`             int  NOT NULL DEFAULT 0,
  `session_department_id`     int  NOT NULL DEFAULT 0,
  `session_id`                int  NOT NULL DEFAULT 0,
  `subject_id`                int  NOT NULL DEFAULT 0,
  `profile_id`                int  NOT NULL DEFAULT 0,
  `is_offsite`                int  NOT NULL DEFAULT 0,
  `city_id`                   int  NOT NULL DEFAULT 0,
  `category`                  int  NOT NULL DEFAULT 0,
  `study_month`               date NULL,
  `session_quarter_id` int NOT NULL,
  PRIMARY KEY (`department_application_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_document` (
  `document_id` int          NOT NULL auto_increment,
  `name`        varchar(249) NULL,
  `add_date`    datetime     NULL,
  `subject_id`  int          NOT NULL DEFAULT 0,
  `type`        int          NOT NULL DEFAULT 0,
  `filename`    varchar(249) NULL,
  PRIMARY KEY (`document_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_feedbacks` (
  `subject_id`        int           NOT NULL,
  `user_id`           int           NOT NULL,
  `mark`              int           NULL,
  `text`              varchar(2048) NULL,
  `date`              datetime      NULL,
  `mark_goal`         int           NULL,
  `mark_goal2`        int           NULL,
  `longtime`          int           NULL,
  `mark_usefull`      int           NULL,
  `mark_motivation`   int           NULL,
  `mark_course`       int           NULL,
  `mark_teacher`      int           NULL,
  `mark_papers`       int           NULL,
  `mark_organization` int           NULL,
  `recomend`          int           NULL,
  `mark_final`        int           NULL,
  `text_goal`         varchar(1024) NULL,
  `text_usefull`      varchar(1024) NULL,
  `text_not_usefull`  varchar(1024) NULL,
  PRIMARY KEY (`user_id`, `subject_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_prefixes` (
  `prefix_id`   int          NOT NULL auto_increment,
  `name`        varchar(249) NULL,
  `counter`     int          NOT NULL DEFAULT 1,
  `prefix_type` int          NOT NULL DEFAULT 1,
  PRIMARY KEY (`prefix_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_provider_contacts` (
  `contact_id`  int          NOT NULL auto_increment,
  `provider_id` int          NULL,
  `name`        varchar(249) NULL,
  `position`    varchar(64)  NULL,
  `phone`       varchar(32)  NULL,
  `email`       varchar(32)  NULL,
  PRIMARY KEY (`contact_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_providers` (
  `provider_id`            int           NOT NULL auto_increment,
  `name`                   varchar(249)  NULL,
  `description`            text          NULL,
  `status`                 tinyint       NULL,
  `type`                   tinyint       NOT NULL DEFAULT 0,
  `address_legal`          varchar(1000) NULL,
  `address_postal`         varchar(1000) NULL,
  `inn`                    varchar(32)   NULL,
  `kpp`                    varchar(32)   NULL,
  `bik`                    varchar(32)   NULL,
  `subscriber_fio`         varchar(249)  NULL,
  `subscriber_position`    varchar(249)  NULL,
  `subscriber_reason`      varchar(249)  NULL,
  `account`                varchar(249)  NULL,
  `account_corr`           varchar(249)  NULL,
  `created`                datetime      NULL,
  `created_by`             int           NULL,
  `create_from_tc_session` int           NULL,
  `department_id`          int           NOT NULL DEFAULT 0,
  `dzo_id`                 int           NOT NULL DEFAULT 0,
  `licence`                varchar(249)  NULL     DEFAULT NULL,
  `registration`           varchar(249)  NULL     DEFAULT NULL,
  `pass_by`                int           NOT NULL DEFAULT 0,
  `prefix_id`              int           NOT NULL DEFAULT 0,
  `information`            text          NULL,
  PRIMARY KEY (`provider_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_provider_files` (
  `provider_id` int NOT NULL,
  `file_id`     int NOT NULL,
  PRIMARY KEY (`provider_id`, `file_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_provider_rooms` (
  `room_id`     INT          NOT NULL auto_increment,
  `provider_id` INT          NOT NULL,
  `name`        varchar(249) NULL     DEFAULT NULL,
  `type`        TINYINT      NULL     DEFAULT NULL,
  `places`      INT          NOT NULL DEFAULT 0,
  `description` TEXT         NULL     DEFAULT NULL,
  `created`     datetime     NULL,
  `created_by`  int          NULL,
  PRIMARY KEY (`room_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_provider_scmanagers` (
  `user_id`     int NOT NULL,
  `provider_id` int NOT NULL,
  PRIMARY KEY (`user_id`, `provider_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_providers_subjects` (
  `provider_subject_id` int NOT NULL auto_increment,
  `subject_id`          int NOT NULL,
  `provider_id`         int NOT NULL,
  PRIMARY KEY (`provider_subject_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_sessions` (
  `session_id`    int          NOT NULL auto_increment,
  `name`          varchar(249) NULL,
  `cycle_id`      int          NULL,
  `date_begin`    date         NULL,
  `date_end`      date         NULL,
  `norm`          int          NULL,
  `status`        int          NOT NULL DEFAULT 0,
  `type`          int          NOT NULL DEFAULT 0,
  `checked_items` text         NOT NULL ,
  `provider_id`   int          DEFAULT NULL,
  `responsible_id`   int          DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_session_departments` (
  `session_department_id` int NOT NULL auto_increment,
  `department_id`         int NULL,
  `session_id`            int NULL,
  `session_quarter_id`    int NULL,
  `parent_session_department_id`    int NULL,
  PRIMARY KEY (`session_department_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_sessions_quarter` (
  `session_quarter_id` int          NOT NULL auto_increment,
  `session_id`         int          NOT NULL,
  `name`               varchar(249) NULL,
  `cycle_id`           int          NULL,
  `date_begin`         date         NULL,
  `date_end`           date         NULL,
  `norm`               int          NULL,
  `status`             int          NOT NULL DEFAULT 0,
  `type`               int          NOT NULL DEFAULT 0,
  `checked_items`      text         NOT NULL,
  `provider_id`        int                   DEFAULT NULL,
  PRIMARY KEY (`session_quarter_id`)
) ENGINE = MyISAM;

CREATE TABLE `tc_provider_teachers` (
  `teacher_id`  int           NOT NULL AUTO_INCREMENT,
  `provider_id` int           NULL,
  `name`        varchar(249)  NULL,
  `description` varchar(2048) NULL,
  `contacts`    varchar(2048) NULL,
  `created`     datetime      NULL,
  `created_by`  int           NULL,
  `user_id`     int           NULL,
  PRIMARY KEY (`teacher_id`)
)
  ENGINE = MyISAM;

CREATE TABLE `tc_provider_teachers2subjects` (
  `teacher_id`  int NOT NULL,
  `provider_id` int NULL,
  `subject_id`  int NOT NULL,
  PRIMARY KEY (`teacher_id`, `subject_id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `feedback` (
	`feedback_id` INT(11) NOT NULL AUTO_INCREMENT,
	`subject_id` INT(11) NOT NULL DEFAULT 0,
	`user_id` INT(11) NULL DEFAULT NULL,
	`quest_id` INT(11) NOT NULL DEFAULT 0,
	`status` INT(11) NOT NULL DEFAULT '0',
	`date_finished` DATETIME NULL DEFAULT NULL,
	`name` varchar(249) NOT NULL DEFAULT '',
	`respondent_type` TINYINT(4) NOT NULL DEFAULT '0',
	`assign_type` TINYINT(4) NOT NULL DEFAULT '1',
	`assign_days` INT(11) NULL DEFAULT NULL,
	`assign_new` TINYINT(4) NULL DEFAULT NULL,
	`assign_anonymous` TINYINT(4) NULL DEFAULT NULL,
	`assign_teacher` TINYINT(4) NULL DEFAULT NULL,
	`assign_anonymous_hash` varchar(249) NULL DEFAULT NULL,
	PRIMARY KEY (`feedback_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `feedback_users` (
	`feedback_user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`feedback_id` INT(11) NOT NULL,
	`subordinate_id` INT(11) NULL,
	`common_date_end` INT(11) NULL,
	PRIMARY KEY (`feedback_user_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `state_of_process` (
  `state_of_process_id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,
  `process_id` int(11) NOT NULL,
  `process_type` int(11) NOT NULL,
  `current_state` varchar(249) NOT NULL DEFAULT '',
  `passed_states` text,
  `status` int(11) default 0 NOT NULL,
  `params` text NOT NULL,
  `last_passed_state` varchar(249) DEFAULT '' NOT NULL,
  PRIMARY KEY  (`state_of_process_id`),
  KEY `item_id` (`item_id`),
  KEY `process_id` (`process_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `state_of_process_data` (
	`state_of_process_data_id` INT NOT NULL auto_increment,
	`state_of_process_id` INT NOT NULL,
	`programm_event_user_id` INT NULL,
	`state` varchar(249) NOT NULL,
	`begin_date_planned` DATETIME NULL,
	`begin_date` DATETIME NULL,
	`begin_by_user_id` INT NULL,
	`begin_auto` TINYINT NOT NULL,
	`end_date_planned` DATETIME NULL,
	`end_date` DATETIME NULL,
	`end_by_user_id` INT NULL,
	`end_auto` TINYINT NULL,
	`status` INT NULL,
	`comment` VARCHAR(4000) NULL,
	`comment_date` DATETIME NULL,
	`comment_user_id` INT NULL,
	PRIMARY KEY (`state_of_process_data_id`)
);


CREATE TABLE IF NOT EXISTS `subscriptions`  (
    `subscription_id` int(11) AUTO_INCREMENT NOT NULL,
    `user_id`         int(10) UNSIGNED NOT NULL DEFAULT '0',
    `channel_id`      int(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY(`subscription_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subscription_entries`  (
    `entry_id`    int(11) AUTO_INCREMENT NOT NULL,
    `channel_id`  int(10) UNSIGNED NOT NULL,
    `title`       varchar(249) NULL,
    `link`        varchar(249) NULL,
    `description` text NULL,
    `content`     text NULL,
    `author`      int(10) UNSIGNED NOT NULL,
    PRIMARY KEY(`entry_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `subscription_channels`  (
    `channel_id`    int(11) AUTO_INCREMENT NOT NULL,
    `activity_name` varchar(45) NULL,
    `subject_name`  varchar(45) NULL,
    `subject_id`    int(11) NOT NULL DEFAULT '0',
    `subject` varchar(50) NOT NULL DEFAULT 'subject',
    `lesson_id`     int(11) NOT NULL DEFAULT '0',
    `title`         varchar(249) NULL,
    `description`   text NULL,
    `link`          varchar(249) NULL,
    PRIMARY KEY(`channel_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `from` int(11) NOT NULL default '0',
  `to` int(11) NOT NULL default '0',
  `subject` varchar(249) default NULL,
  `theme` varchar(249) default NULL,
  `subject_id` int(11) unsigned default NULL,
  `message` text,
  `created` datetime default NULL,
  `readed`     int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`message_id`),
  KEY `from` (`from`),
  KEY `to` (`to`),
  KEY `subject_id` (`subject_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `interface` (
  `interface_id` int(11) NOT NULL auto_increment,
  `role` varchar(249) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `block` varchar(249) NOT NULL default '',
  `necessity` int(11) default '0',
  `x` int(11) NOT NULL default '1',
  `y` int(11) NOT NULL default '1',
  `width` int(11) NOT NULL default '100',
  `param_id` varchar(249) default 0 NOT NULL,
  `skin` int DEFAULT 0 NOT NULL,
  PRIMARY KEY  (`interface_id`),
  KEY `role` (`role`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `captcha` (
  `login` varchar(249) NOT NULL,
  `attempts` int(11) NOT NULL default '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`login`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `videochat_users` (
    `pointId` varchar(249) NOT NULL,
    `userId` int(11) NOT NULL,
    `last` datetime NOT NULL default 0,
    PRIMARY KEY  (`pointId`,`userId`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `notice` (
    `id` int(11) NOT NULL auto_increment,
	`cluster` varchar(32),
    `event` varchar(249) NULL,
    `receiver` int(11) NULL,
    `title` varchar(249) NULL,
    `message` text NULL,
    `type` int(11) NULL,
    `enabled` int(11) NOT NULL DEFAULT 1,
    `priority` int(11) NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


CREATE TABLE `storage_filesystem` (
  id           int(10) UNSIGNED NOT NULL auto_increment,
  parent_id    int(10) UNSIGNED,
  subject_id   int(10) UNSIGNED NOT NULL,
  subject_name varchar(249),
  name         varchar(249),
  alias        varchar(249),
  is_file      tinyint(1)       NOT NULL,
  description  varchar(249),
  user_id      int(10) UNSIGNED NULL     DEFAULT NULL,
  created      DATETIME,
  changed      DATETIME,
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
  KEY subject_id (subject_id)
) ENGINE = MyISAM;


CREATE TABLE `storage` (
  id           int(10) UNSIGNED NOT NULL auto_increment,
  parent_id    int(10) UNSIGNED,
  hash         varchar(249),
  phash        varchar(249),
  subject_id   int(10) UNSIGNED NOT NULL,
  subject_name varchar(249),
  name         varchar(249),
  alias        varchar(249),
  is_file      tinyint(1)       NOT NULL,
  description  varchar(249),
  user_id      int(10) UNSIGNED NULL     DEFAULT NULL,
  created      DATETIME,
  changed      DATETIME,
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
  KEY subject_id (subject_id)
) ENGINE = MyISAM;


CREATE TABLE IF NOT EXISTS `chat_channels`
(
  id           int(10) UNSIGNED NOT NULL auto_increment,
  subject_name varchar(249),
  subject_id   int(10) UNSIGNED NOT NULL,
  lesson_id    int(10) UNSIGNED NULL default NULL,
  `name`       varchar(249) NULL,
  start_date   DATE,
  end_date     DATE,
  show_history tinyint(1) UNSIGNED DEFAULT 1 NOT NULL,
  start_time   int(4) UNSIGNED,
  end_time     int(4) UNSIGNED,
  is_general   tinyint(1) UNSIGNED DEFAULT 0 NOT NULL,
  PRIMARY KEY (id),
  KEY subject_id (subject_id),
  KEY lesson_id (lesson_id)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `chat_history`
(
  id           int(10) UNSIGNED NOT NULL auto_increment,
  channel_id           int(10) UNSIGNED NOT NULL,
  sender           int(10) UNSIGNED NOT NULL,
  receiver           int(10) UNSIGNED NULL default NULL,
  message text NOT NULL,
  created   DATETIME NOT NULL default 0,
  PRIMARY KEY (id),
  KEY channel_id (channel_id),
  KEY created (created)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `chat_ref_users`
(
  channel_id           int(10) UNSIGNED NOT NULL,
  user_id           int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (channel_id,user_id)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(249),
  `order` tinyint(4) NULL,
  PRIMARY KEY  (`section_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `reports_roles`
(
  `role` varchar(100) NOT NULL default '',
  `report_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`role`, `report_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `support_requests` (
  `support_request_id` INT NOT NULL AUTO_INCREMENT,
  `date_` DATETIME NULL,
  `theme` varchar(249) NULL,
  `status` INT NULL,
  `problem_description` TEXT NULL,
  `wanted_result` TEXT NULL,
  `user_id` INT NULL,
  `url` varchar(249) NULL,
  `file_id` INT NULL,
  PRIMARY KEY (`support_request_id`)
) ENGINE=MyISAM;

CREATE TABLE  `webinar_dbs` (
  `db_id` varchar(249) NOT NULL,
  `host` varchar(249) NOT NULL,
  `port` int(11) NOT NULL,
  `name` varchar(249) NOT NULL,
  `login` varchar(249) NOT NULL,
  `pass` varchar(249) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS  `webinar_dbs` (
  `db_id` varchar(249) NOT NULL,
  `host` varchar(249) NOT NULL,
  `port` int(11) NOT NULL,
  `name` varchar(249) NOT NULL,
  `login` varchar(249) NOT NULL,
  `pass` varchar(249) NOT NULL
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `likes` (
	`like_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`item_type` INT(11) NOT NULL,
	`item_id` INT(11) NOT NULL,
	`count_like` INT(11) NOT NULL DEFAULT '0',
	`count_dislike` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`like_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `like_user` (
	`like_user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`item_type` INT(11) UNSIGNED NOT NULL,
	`item_id` INT(11) UNSIGNED NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL,
	`value` TINYINT(4) NOT NULL,
	`date` DATETIME NOT NULL,
	PRIMARY KEY (`like_user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `at_hh_regions` (
  `id` int(11) unsigned NOT NULL,
  `parent` int(11) unsigned NOT NULL DEFAULT 0,
  `name` varchar(249) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `questionnaires` (
  `quest_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(16) DEFAULT NULL,
  `status` TINYINT(4) DEFAULT 0,
  `name` varchar(249) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `subject_id` INTEGER(11) NOT NULL DEFAULT 0,
  `scale_id` INTEGER(11) DEFAULT 0 NOT NULL,
  `creator_role` varchar(249) default '',
  `displaycomment` TINYINT(4) DEFAULT 0,
  `profile_id`     int,
  PRIMARY KEY (`quest_id`),
  KEY `quest_id_type` (`quest_id`, `type`),
  KEY `type` (`type`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `quest_questions` (
  `question_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` INTEGER(11) DEFAULT NULL,
  `subject_id` INT(11) NOT NULL DEFAULT '0',
  `type` VARCHAR(16) DEFAULT NULL,
  `quest_type` VARCHAR(16) DEFAULT NULL,
  `question` TEXT NULL,
  `shorttext` varchar(249) DEFAULT NULL,
  `mode_scoring` TINYINT(4) DEFAULT NULL,
  `show_free_variant` TINYINT(4) DEFAULT NULL,
  `shuffle_variants` TINYINT(4) DEFAULT NULL,
  `file_id` INT(11) NOT NULL DEFAULT '0',
  `data` TEXT NULL,
  `score_min` float(9,3) NOT NULL DEFAULT '0',
  `score_max` float(9,3) NOT NULL DEFAULT '1',
  `variants_use_wysiwyg` tinyint(1) NOT NULL DEFAULT 0,
  `justification` TEXT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=MyISAM;


CREATE TABLE `quest_question_quests` (
  `question_id` INTEGER(11) NOT NULL,
  `quest_id`    INTEGER(11) NOT NULL,
  `cluster_id`  INTEGER(11) NULL,
  KEY `question_id_quest_id` (`question_id`, `quest_id`),
  KEY `question_id` (`question_id`),
  KEY `quest_id` (`quest_id`),
  KEY `cluster_id` (`cluster_id`)
)
  ENGINE = MyISAM;


CREATE TABLE IF NOT EXISTS `quest_question_variants` (
  `question_variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `variant` text,
  `free_variant` varchar(4000),
  `shorttext` varchar(249) DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT NULL,
  `score_weighted` float,
  `score_raw` int,
  `category_id` varchar(1024) DEFAULT NULL,
  `weight` float(11,0) DEFAULT NULL,
  `data` TEXT NULL,
  PRIMARY KEY (`question_variant_id`),
  KEY (`question_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `quest_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `quest_id` int(11) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  `context_event_id` int(11) DEFAULT NULL,
  `context_type` tinyint(4) DEFAULT NULL,
  `date_begin` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `status` tinyint(11) DEFAULT NULL,
  `score_weighted` float(9,3) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `score_sum` float(9,3) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `is_resultative` tinyint(11) DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `user_id` (`user_id`),
  KEY `quest_id` (`quest_id`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `quest_attempt_clusters` (
	`quest_attempt_cluster_id` int(11) NOT NULL AUTO_INCREMENT,
	`quest_attempt_id` int NULL,
	`cluster_id` int(11),
	`score_percented` float(9,3),
	PRIMARY KEY (`quest_attempt_cluster_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `quest_question_results` (
  `question_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `variant` TEXT DEFAULT NULL,
  `free_variant` TEXT DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT NULL,
  `score_weighted` float(9,3) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `score_min` float(9,3) DEFAULT NULL,
  `score_max` float(9,3) DEFAULT NULL,
  `show_feedback`      tinyint(4)       DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  PRIMARY KEY (`question_result_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM;



CREATE TABLE `schedule_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id`         int(11)          DEFAULT NULL,
  `user_id`           int(11)          DEFAULT NULL,
  `date_start`        DATETIME         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = MyISAM;


CREATE TABLE `quest_settings` (
  `quest_id`                   int(11) NOT NULL,
  `scope_type`                 tinyint(4)       default 0 NOT NULL,
  `scope_id`                   int(11)          default 0 NOT NULL,
  `info`                       text,
  `cluster_limits`             text,
  `comments`                   text,
  `mode_selection`             tinyint(4)       default NULL,
  `mode_selection_questions`   tinyint(4)       default NULL,
  `mode_selection_all_shuffle` tinyint(4)       default NULL,
  `mode_passing`               tinyint(4)       default NULL,
  `mode_display`               tinyint(4)       default NULL,
  `mode_display_clusters`      tinyint(4)       default NULL,
  `mode_display_questions`     tinyint(4)       default NULL,
  `show_result`                tinyint(4)       default NULL,
  `show_log`                   tinyint(4)       default NULL,
  `limit_time`                 tinyint(4)       default NULL,
  `limit_attempts`             tinyint(4)       default NULL,
  `limit_clean`                tinyint(4)       default NULL,
  `mode_test_page`             int     NOT NULL DEFAULT 0
  COMMENT '������������ ����� ���������� ����� (0 - ���������������� �����������, 1 - C�������� ������������ ����� ����������',
  mode_self_test tinyint(4) default NULL,
  UNIQUE KEY (`quest_id`, `scope_type`, `scope_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `user_additional_fields` (
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`field_id` INT(11) NOT NULL DEFAULT '0',
	`value` TEXT NOT NULL,
	UNIQUE KEY (`user_id`, `field_id`)
)
ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_events` (
	event_id INT(11) NOT NULL AUTO_INCREMENT,
	event_type_id INT(8) NOT NULL,
	event_trigger_id INT(11) NOT NULL,
	event_group_id INT(8) DEFAULT 0 NOT NULL,
	description TEXT NOT NULL,
	create_time bigint(20) NOT NULL,
	PRIMARY KEY (event_id),
	KEY event_type_id (event_type_id),
	KEY `es_events_event_type_id` (`event_type_id`),
	KEY `es_events_event_group_id` (`event_group_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_event_users` (
	event_id INT(11) NOT NULL,
	user_id INT(11) NOT NULL,
	views TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (event_id,user_id),
	KEY `es_event_users_event_id` (`event_id`),
    KEY `es_event_users_user_id` (`user_id`),
    KEY `es_event_users_views` (`views`),
    KEY `es_event_users_views_user_id` (`views`, `user_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_event_group_types` (
        event_group_type_id INT(8) NOT NULL,
        name varchar(249) NOT NULL,
        PRIMARY KEY (event_group_type_id)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_event_types` (
	event_type_id INT(8) NOT NULL,
	name varchar(249) NOT NULL,
	event_group_type_id INT(8) NOT NULL,
	PRIMARY KEY (event_type_id),
	KEY`name` (`name`),
	KEY`name_event_group_type_id` (`name`, `event_group_type_id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_event_groups` (
	event_group_id INT(8) NOT NULL AUTO_INCREMENT,
	trigger_instance_id INT(11) NOT NULL,
	`type` varchar(249) NOT NULL,
	`data` TEXT NOT NULL,
	UNIQUE KEY group_name (trigger_instance_id, `type`),
	PRIMARY KEY (event_group_id)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_notify_types` (
        notify_type_id INT(8) NOT NULL,
        name varchar(249) NOT NULL,
        PRIMARY KEY (notify_type_id)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `es_user_notifies` (
        user_id INT(11) NOT NULL,
        notify_type_id INT(8) NOT NULL,
        event_type_id INT(8) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (user_id,notify_type_id, event_type_id)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS `tracks2group` (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        trid INT(11) NOT NULL DEFAULT 0,
        `level` INT(11) NOT NULL DEFAULT 0,
        gid INT(11) NOT NULL DEFAULT 0,
        updated datetime
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `kbase_assessment` (
  `id` int(10) NOT NULL auto_increment,
  `type` int(10) NOT NULL default '0',
  `resource_id` int(10) NOT NULL,
  `MID` int(10) NOT NULL default '0',
  `assessment` INTEGER(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `at_ps_standard` (
	`standard_id` INT(11) NOT NULL AUTO_INCREMENT,
	`number` VARCHAR(50) NOT NULL,
	`code` VARCHAR(50) NOT NULL,
	`name` VARCHAR(1024) NOT NULL,
	`area` VARCHAR(1024) NOT NULL,
	`vid` VARCHAR(1024) NOT NULL,
	`prikaz_number` VARCHAR(50) NOT NULL,
	`prikaz_date` DATE NOT NULL,
	`minjust_number` VARCHAR(50) NOT NULL,
	`minjust_date` DATE NOT NULL,
	`sovet` VARCHAR(1024) NOT NULL,
	`url` VARCHAR(1024) NOT NULL,
	PRIMARY KEY (`standard_id`)
)
ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `at_ps_function` (
	`function_id` INT(11) NOT NULL AUTO_INCREMENT,
	`standard_id` INT(11) NOT NULL,
	`name` VARCHAR(1024) NOT NULL,
	PRIMARY KEY (`function_id`),
	INDEX `standard_id` (`standard_id`)
)
ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `at_ps_requirement` (
	`requirement_id` INT(11) NOT NULL AUTO_INCREMENT,
	`function_id` INT(11) NULL DEFAULT NULL,
	`type` INT(11) NULL DEFAULT NULL,
	`name` VARCHAR(1024) NULL DEFAULT NULL,
	PRIMARY KEY (`requirement_id`),
	INDEX `function_id` (`function_id`)
)
ENGINE=MyISAM;




CREATE TABLE IF NOT EXISTS `estaff_spot` (
  `spot_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `state_date` DATETIME DEFAULT NULL,
  `state_id` varchar(249) NOT NULL DEFAULT '',
  `vacancy_name` varchar(249) NOT NULL DEFAULT '',
  `resume_text` TEXT,
	INDEX `user_id` (`user_id`),
	INDEX `state_id` (`state_id`)
) ENGINE=MyISAM;

CREATE OR REPLACE VIEW `study_groups_auto_users` AS
SELECT `ga`.`group_id` AS `group_id`,`sou`.`mid` AS `user_id`
FROM ((`study_groups_auto` `ga` join `structure_of_organ` `sod` on((`sod`.`soid` = `ga`.`department_id`))) join `structure_of_organ` `sou` on(((`sou`.`lft` >= `sod`.`lft`) and (`sou`.`rgt` <= `sod`.`rgt`) and (`sou`.`code` = `ga`.`position_code`))));


CREATE OR REPLACE VIEW `study_groups_users` AS
SELECT `study_groups_custom`.`group_id` AS `group_id`,`study_groups_custom`.`user_id` AS `user_id`,1 AS `type`
FROM `study_groups_custom`
UNION
SELECT `study_groups_auto_users`.`group_id` AS `group_id`,`study_groups_auto_users`.`user_id` AS `user_id`,2 AS `type`
FROM `study_groups_auto_users`;

CREATE TABLE `eclass` (
  `id` int(10) NOT NULL auto_increment,
  `lesson_id`  int(10) NULL,
  `synced`     int(10) NOT NULL default '0',
  `sync_date`  datetime,
  `title`        varchar(249),
  `subject_id` int(10) NULL,
  PRIMARY KEY (`id`)
--  PRIMARY KEY (`webinar_id`)
)
  ENGINE = MyISAM;


CREATE TABLE `deputy_assign` (
  `assign_id`      INT auto_increment,
  `user_id`        INT,
  `deputy_user_id` INT,
  `begin_date`     DATETIME,
  `end_date`       DATETIME,
  `not_active`     int(11) default 0,
  PRIMARY KEY (`assign_id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `mail_queue` (
    `id` INT(10) UNSIGNED NOT NULL auto_increment,
    `subject` VARCHAR(1024) NULL DEFAULT NULL,
    `recipient` VARCHAR(255) NULL DEFAULT NULL,
    `body` MEDIUMTEXT NOT NULL,
    `created` DATETIME NOT NULL,
    `data` TEXT NOT NULL,
    `sended` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `created` (`created`)
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
ROW_FORMAT=DYNAMIC;
