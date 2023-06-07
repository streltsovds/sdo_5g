#
# Structure for the `actions` table :
#

DROP TABLE IF EXISTS `actions`;

CREATE TABLE `actions` (
  `acid` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `url` varchar(64) NOT NULL default '',
  `title` varchar(128) NOT NULL default '',
  `sequence` int(11) unsigned NOT NULL default '0',
  `type` varchar(255) default 'dean',
  PRIMARY KEY  (`acid`)
);

#
# Structure for the `admins` table :
#

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `AID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  UNIQUE KEY `AID` (`AID`),
  UNIQUE KEY `MID` (`MID`)
);

#
# Structure for the `alt_mark` table :
#

DROP TABLE IF EXISTS `alt_mark`;
CREATE TABLE `alt_mark` (
  `id` int(4) default NULL,
  `int` int(4) default NULL,
  `char` char(2) default NULL,
  KEY `id` (`id`) 
);

DROP TABLE IF EXISTS `at_categories`;
CREATE TABLE `at_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `name` varchar(32) default NULL,  
  `description` varchar(255) default NULL,  
  PRIMARY KEY (`category_id`)
);

DROP TABLE IF EXISTS `at_criteria`;
CREATE TABLE `at_criteria` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `cluster_id` int(11) NULL, 
  `category_id` int(11) NULL, 
  `type` int(11) NOT NULL default '0',
  `order` int NOT NULL DEFAULT 0,  
  PRIMARY KEY  (`criterion_id`)
);

DROP TABLE IF EXISTS `at_criteria_kpi`;
CREATE TABLE `at_criteria_kpi` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `order` int NOT NULL DEFAULT 0,  
  PRIMARY KEY  (`criterion_id`)
);

DROP TABLE IF EXISTS `at_criteria_test`;
CREATE TABLE `at_criteria_test` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `lft` int(11) NOT NULL default '0', 
  `rgt` int(11) NOT NULL default '0', 
  `level` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `description` text NULL, 
  PRIMARY KEY  (`criterion_id`)
);

DROP TABLE IF EXISTS `at_criteria_personal`;
CREATE TABLE `at_criteria_personal` (
  `criterion_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `quest_id` int(11) NULL, 
  `description` text NULL,
  PRIMARY KEY  (`criterion_id`)
);

DROP TABLE IF EXISTS `at_criteria_scale_values`;
CREATE TABLE `at_criteria_scale_values` (
  `criterion_value_id` int(11) NOT NULL auto_increment,
  `criterion_id` int(11) NOT NULL,
  `value_id` int(11) DEFAULT NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY (`criterion_value_id`)
);

DROP TABLE IF EXISTS `at_criteria_clusters`;
CREATE TABLE `at_criteria_clusters` (
  `cluster_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `order` int NOT NULL DEFAULT 0,    
  PRIMARY KEY (`cluster_id`)
);

DROP TABLE IF EXISTS `at_criteria_indicators`;
CREATE TABLE `at_criteria_indicators` (
  `indicator_id` int(11) NOT NULL auto_increment,
  `criterion_id` int(11) DEFAULT NULL,
  `name` varchar(255) default NULL,
  `description_positive` varchar(255) default NULL,
  `description_negative` varchar(255) default NULL,
  `reverse` tinyint(4) NOT NULL DEFAULT 0,    
  `order` int NOT NULL DEFAULT 0,    
  PRIMARY KEY (`indicator_id`),
  KEY `criterion_id` (`criterion_id`)
);

DROP TABLE IF EXISTS `at_kpi_units`;
CREATE TABLE `at_kpi_units` (
  `kpi_unit_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,    
  PRIMARY KEY (`kpi_unit_id`)
);

DROP TABLE IF EXISTS `at_kpi_clusters`;
CREATE TABLE `at_kpi_clusters` (
  `kpi_cluster_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,    
  PRIMARY KEY (`kpi_cluster_id`)
);

DROP TABLE IF EXISTS `at_kpis`;
CREATE TABLE `at_kpis` (
  `kpi_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,    
  `kpi_cluster_id` int(1) NOT NULL default 0,  
  `kpi_unit_id` int(1) NOT NULL default 0,  
  `is_typical` tinyint(4) NOT NULL default 0,  
  PRIMARY KEY (`kpi_id`)
);

DROP TABLE IF EXISTS `at_user_kpis`;
CREATE TABLE `at_user_kpis` (
  `user_kpi_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,    
  `cycle_id` int(11) NOT NULL,    
  `kpi_id` int(11) default NULL,    
  `weight` float(4,2) default NULL,    
  `value_plan` varchar(32) default NULL,
  `value_fact` varchar(32) default NULL,   
  `comments` text default NULL,  
  PRIMARY KEY (`user_kpi_id`)
);

DROP TABLE IF EXISTS `at_profile_kpis`;
CREATE TABLE `at_profile_kpis` (
  `profile_kpi_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NOT NULL,    
  `kpi_id` int(11) default NULL,    
  `weight` float(4,2) default NULL,    
  `value_plan` varchar(32) default NULL,
  PRIMARY KEY (`profile_kpi_id`)
);

DROP TABLE IF EXISTS `at_profile_skills`;
CREATE TABLE `at_profile_skills` (
  `profile_skill_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NOT NULL,    
  `type` tinyint(0) default NULL,    
  `skill` varchar(255) default NULL,
  PRIMARY KEY (`profile_skill_id`)
);

DROP TABLE IF EXISTS `at_profiles`;
CREATE TABLE `at_profiles` (
  `profile_id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NULL,
  `programm_id` int(11) NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `name` varchar(32) default NULL,    
  `shortname` varchar(64) default NULL,    
  `description` varchar(255) default NULL,
  `requirements` text default NULL,
  `age_min` tinyint(4) NOT NULL default '0',
  `age_max` tinyint(4) NOT NULL default '0',
  `gender` tinyint(4) NOT NULL default '0',
  `education` tinyint(4) NOT NULL default '0',
  `additional_education` text ,
  `academic_degree` tinyint(4) NOT NULL default '0',
  `trips` tinyint(4) NOT NULL default '0',
  `trips_duration` varchar(255) default NULL,
  `mobility` tinyint(4) NOT NULL default '0',
  `experience` text,
  `comments` text,
  PRIMARY KEY (`profile_id`)
);

DROP TABLE IF EXISTS `at_evaluation_criteria`;
CREATE TABLE `at_evaluation_criteria` (
  `evaluation_type_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`evaluation_type_id`,`criterion_id`)
);

DROP TABLE IF EXISTS `at_profile_criterion_values`;
CREATE TABLE `at_profile_criterion_values` (
  `profile_criterion_value_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NOT NULL,
  `criterion_type` tinyint(4) NOT NULL default 1,
  `criterion_id` int(11) NOT NULL,
  `value_id` int(11) NULL,
  `value` int(11) NULL,
  `method` varchar(20) NOT NULL,
  PRIMARY KEY  (`profile_criterion_value_id`)
);

DROP TABLE IF EXISTS `at_evaluation_memos`;
CREATE TABLE `at_evaluation_memos` (
  `evaluation_memo_id` int(11) NOT NULL auto_increment,
  `evaluation_type_id` int(11) NOT NULL,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`evaluation_memo_id`)
);

DROP TABLE IF EXISTS `at_session_user_criterion_values`;
CREATE TABLE `at_session_user_criterion_values` (
  `session_user_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `criterion_type` tinyint(4) NOT NULL DEFAULT 1,
  `value` float(9,2) DEFAULT NULL,
  PRIMARY KEY  (`session_user_id`,`criterion_id`)
);

DROP TABLE IF EXISTS `at_evaluation_results`;
CREATE TABLE `at_evaluation_results` (
  `result_id` int(11) NOT NULL auto_increment,
  `criterion_id` int(11) NOT NULL,
  `session_event_id` int(11) NOT NULL,
  `session_user_id` int(11) NOT NULL,
  `relation_type` int(11) default NULL,   
  `position_id` int(11) NOT NULL,  
  `value_id` int(11) NOT NULL,  
  `value_weight` float NULL,  
  `indicators_status` tinyint(4) NULL,
  `custom_criterion_name` varchar(255) DEFAULT NULL,
  `custom_criterion_parent_id` int(11) DEFAULT NULL,  
  PRIMARY KEY  (`result_id`)
);

DROP TABLE IF EXISTS `at_evaluation_results_indicators`;
CREATE TABLE `at_evaluation_results_indicators` (
  `indicator_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `indicator_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `session_event_id` int(11) NOT NULL,
  `session_user_id` int(11) NOT NULL,
  `relation_type` int(11) default NULL,   
  `position_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  PRIMARY KEY (`indicator_result_id`)
);

DROP TABLE IF EXISTS `at_evaluation_memo_results`;
CREATE TABLE `at_evaluation_memo_results` (
  `evaluation_memo_result_id` int(11) NOT NULL auto_increment,
  `evaluation_memo_id` int(11) NOT NULL,
  `value` text default NULL,
  `session_event_id` int(11) NOT NULL,
  PRIMARY KEY  (`evaluation_memo_result_id`)
);

DROP TABLE IF EXISTS `at_evaluation_type`;
CREATE TABLE `at_evaluation_type` (
  `evaluation_type_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `comment` text default NULL,  
  `scale_id` int(11) default NULL, #deprecated!!!
  `category_id` int(11) default NULL, 
  `profile_id` int(11) default NULL, 
  `vacancy_id` int(11) default NULL, 
  `newcomer_id` int(11) default NULL, 
  `method` varchar(20) NOT NULL,
  `submethod` varchar(255) NOT NULL,
  `methodData` text,  /*deprecated, move data to submethod!*/
  `relation_type` int(11) default NULL, 
  `programm_type` int(11) default '0', 
  PRIMARY KEY  (`evaluation_type_id`)
);

DROP TABLE IF EXISTS `at_managers`;
CREATE TABLE `at_managers` (
  `atmanager_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`atmanager_id`)
);

DROP TABLE IF EXISTS `at_session_events`;
CREATE TABLE `at_session_events` (
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
  `method` varchar(255) default '',
  `name` varchar(255) default '',
  `description` text default NULL,  
  `status` int(11) default '0',
  `date_begin` date default NULL,
  `date_end` date default NULL,
  `date_filled` datetime default NULL,
  PRIMARY KEY  (`session_event_id`)
);

DROP TABLE IF EXISTS `at_session_pairs`;
CREATE TABLE `at_session_pairs` (
  `session_pair_id` int(11) NOT NULL auto_increment,
  `session_event_id` int(11) NOT NULL default '0',
  `first_user_id` int(11) NOT NULL default '0',
  `second_user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`session_pair_id`)
);

DROP TABLE IF EXISTS `at_session_pair_results`;
CREATE TABLE `at_session_pair_results` (
  `session_pair_id` int(11) NOT NULL default 0,
  `session_event_id` int(11) NOT NULL default 0,
  `criterion_id` int(11) NOT NULL default 0,
  `user_id` int(11) NOT NULL default 0,
  `parent_soid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`session_pair_id`, `criterion_id`)
);

DROP TABLE IF EXISTS `at_session_pair_ratings`;
CREATE TABLE `at_session_pair_ratings` (
  `session_id` int(11) NOT NULL default 0,
  `criterion_id` int(11) NOT NULL default 0,
  `session_user_id` int(11) NOT NULL default 0,
  `user_id` int(11) NOT NULL default 0,
  `rating` tinyint(3) NOT NULL default 0,
  `ratio` int(11) NOT NULL default 0,
  `parent_soid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`session_id`, `criterion_id`, `user_id`)
);

DROP TABLE IF EXISTS `at_session_event_lessons`;
CREATE TABLE `at_session_event_lessons` (
  `session_event_id` int(11) NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  `criteria` text default NULL,
  PRIMARY KEY  (`session_event_id`, `lesson_id`)
);

DROP TABLE IF EXISTS `at_session_event_attempts`;
CREATE TABLE `at_session_event_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_event_id` int(11) DEFAULT NULL,
  `method` varchar(255) default '',
  `date_begin` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`attempt_id`)
);

DROP TABLE IF EXISTS `at_session_users`;
CREATE TABLE `at_session_users` (
  `session_user_id` int(11) NOT NULL auto_increment,
  `session_id` int(11) default NULL, 
  `position_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `profile_id` int(11) default NULL,
  `process_id` int(11) default NULL,
  `vacancy_candidate_id` int(11) default NULL,
  `newcomer_id` int(11) default NULL,
  `status` tinyint(4) NOT NULL default 0,      
  `total_competence` float(4,2) default NULL,    
  `total_kpi` float(4,2) default NULL,    
  `result_category` tinyint(4) NULL, 
  PRIMARY KEY  (`session_user_id`)
) ;

DROP TABLE IF EXISTS `at_relations`;
CREATE TABLE `at_relations` (
  `relation_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `respondents` text default NULL,
  `relation_type` varchar(255) default '', 
  PRIMARY KEY  (`relation_id`)
);

DROP TABLE IF EXISTS `at_session_respondents`;
CREATE TABLE `at_session_respondents` (
  `session_respondent_id` int(11) NOT NULL auto_increment,
  `session_id` int(11) default NULL,   
  `position_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `progress` tinyint(4) NOT NULL default 0,      
  PRIMARY KEY  (`session_respondent_id`)
) ;

DROP TABLE IF EXISTS `at_sessions`;
CREATE TABLE `at_sessions` (
  `session_id` int(11) NOT NULL auto_increment,
  `programm_type` tinyint(4) default NULL,
  `name` varchar(255) default NULL,
  `shortname` varchar(32) default NULL,  
  `description` text default NULL,  
  `report_comment` text default NULL,  
  `cycle_id` int(11) default NULL,
  `begin_date` datetime default NULL,
  `end_date` datetime default NULL,
  `initiator_id` int(11) default NULL,
  `checked_soids` text default NULL,
  `state` tinyint(4) NOT NULL default 0,  
  PRIMARY KEY  (`session_id`)
);

DROP TABLE IF EXISTS `recruit_vacancies`;
CREATE  TABLE `recruit_vacancies` (
  `vacancy_id` int not null auto_increment,
  `name` varchar(255) null,
  `position_id` int null,
  `parent_position_id` int null,
  `parent_top_position_id` int null,
  `created_by` int null,
  `profile_id` int null,
  `reason` int null,
  `create_date` DATETIME NULL ,
  `open_date` datetime null,
  `close_date` datetime null,
  `work_place` text null,
  `work_mode` int null,
  `trip_mode` int null,
  `salary` varchar(255) null,
  `bonus` varchar(255) null,
  `subordinates` int null,
  `subordinates_count` int null,
  `subordinates_categories` text null,
  `tasks` text null,
  `status` int default 0 not null,
  `age_min` varchar(255) null,
  `age_max` varchar(255) null,
  `gender` varchar(255) null,
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
  PRIMARY KEY (`vacancy_id`) 
);

DROP TABLE IF EXISTS `recruit_vacancy_hh_resume_ignore`;
CREATE TABLE `recruit_vacancy_hh_resume_ignore` (
	`vacancy_hh_resume_ignore_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`vacancy_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`hh_resume_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`date` DATETIME NOT NULL,
	`create_user_id` INT(11) NOT NULL,
	PRIMARY KEY (`vacancy_hh_resume_ignore_id`),
	KEY `hh_resume_id` (`vacancy_id`, `hh_resume_id`)
);


DROP TABLE IF EXISTS `recruit_candidates`;
CREATE  TABLE `recruit_candidates` (
  `candidate_id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NULL ,
  `source` INT NULL ,
  `file_id` INT NULL ,
  `resume_external_url` VARCHAR(255),
  PRIMARY KEY (`candidate_id`) 
);

DROP TABLE IF EXISTS `recruit_vacancy_candidates`;
CREATE  TABLE `recruit_vacancy_candidates` (
  `vacancy_candidate_id` INT NOT NULL AUTO_INCREMENT,
  `vacancy_id` INT NOT NULL ,
  `candidate_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `process_id` INT NOT NULL ,
  `status` INT NULL ,  
  `result` INT NULL ,  
  PRIMARY KEY (`vacancy_candidate_id`) 
);

DROP TABLE IF EXISTS `recruit_vacancy_recruiters`;
CREATE TABLE `recruit_vacancy_recruiters` (
	`vacancy_recruiter_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`vacancy_id` INT(11) UNSIGNED NOT NULL,
	`recruiter_id` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`vacancy_recruiter_id`)
);

DROP TABLE IF EXISTS `recruit_newcomers`;
CREATE  TABLE `recruit_newcomers` (
  `newcomer_id` INT NOT NULL AUTO_INCREMENT ,
  `name` varchar(255) null,  
  `user_id` INT NULL ,
  `vacancy_candidate_id` INT NULL ,
  `profile_id` INT NULL ,
  `position_id` INT NULL ,
  `process_id` INT NULL ,
  `session_id` INT NULL ,
  `created` date,
  `status` int default 0 not null,
  `result` INT NULL ,
  PRIMARY KEY (`newcomer_id`) 
);

DROP TABLE IF EXISTS `recruit_newcomer_recruiters`;
CREATE TABLE `recruit_newcomer_recruiters` (
	`newcomer_recruiter_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`newcomer_id` INT(11) UNSIGNED NOT NULL,
	`recruiter_id` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`newcomer_recruiter_id`)
);


DROP TABLE IF EXISTS `blog`;
CREATE TABLE `blog`
(
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255),
  `body`       LONGTEXT NOT NULL,
  `created`    DATETIME NOT NULL,
  `created_by` INT(10) UNSIGNED NOT NULL,
  `subject_name` VARCHAR(255),
  `subject_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `created_by` (`created_by`)
)
;

#
# Structure for the `cam_casting` table :
#

DROP TABLE IF EXISTS `cam_casting`;

CREATE TABLE `cam_casting` (
  `castID` int(11) NOT NULL auto_increment,
  `cam_key` varchar(32) NOT NULL default '0',
  `CID` tinyint(4) NOT NULL default '0',
  `MID` int(11) NOT NULL default '0',
  `SHEID` int(11) NOT NULL default '0',
  `FILE` blob,
  UNIQUE KEY `castID` (`castID`,`cam_key`),
  KEY `castID_2` (`castID`),
  KEY `CID` (`CID`),
  KEY `MID` (`MID`), 
  KEY `SHEID` (`SHEID`)
);

#
# Structure for the `cgname` table :
#

DROP TABLE IF EXISTS `cgname`;

CREATE TABLE `cgname` (
  `cgid` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  KEY `cgid` (`cgid`)
);

#
# Structure for the `chat` table :
#

DROP TABLE IF EXISTS `chat`;

CREATE TABLE `chat` (
  `CHID` bigint(20) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `type` int(11) default '0',
  `CID` int(11) default '0',
  PRIMARY KEY  (`CHID`),
  KEY `CID` (`CID`),
  KEY `type` (`type`) 
);

#
# Structure for the `chat_messages` table :
#

DROP TABLE IF EXISTS `chat_messages`;

CREATE TABLE `chat_messages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `rid` varchar(255) NOT NULL default '',
  `cid` int(11) unsigned NOT NULL DEFAULT '0',
  `uid` int(11) unsigned NOT NULL default '0',
  `message` text,
  `posted` int(11) unsigned NOT NULL default '0',
  `user` varchar(255) default NULL,
  `sendto` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `cid` (`cid`),
  KEY `uid` (`uid`)
);

#
# Structure for the `chat_users` table :
#

DROP TABLE IF EXISTS `chat_users`;

CREATE TABLE `chat_users` (
  `uid` int(11) unsigned default NULL,
  `rid` varchar(255) NOT NULL default '',
  `cid` int(11) unsigned NOT NULL DEFAULT '0',
  `joined` int(11) unsigned default NULL,
  `user` varchar(255) default NULL,
  UNIQUE KEY `rid` (`rid`,`uid`),
  KEY `cid` (`cid`)
);

#
# Structure for the `CHATHISTORY` table :
#

DROP TABLE IF EXISTS `CHATHISTORY`;

CREATE TABLE `CHATHISTORY` (
  `HISTORYID` int(11) NOT NULL auto_increment,
  `CHATID` int(11) NOT NULL default '0',
  `Login` varchar(255) NOT NULL default '',
  `TimeDate` datetime default NULL,
  `MsgText` text,
  `Params` text,
  PRIMARY KEY  (`HISTORYID`)
);

#
# Structure for the `claimants` table :
#

DROP TABLE IF EXISTS `claimants`;

CREATE TABLE `claimants` (
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
  `mid_external` varchar(255) NOT NULL default '',
  `lastname` varchar(255) binary NOT NULL default '',
  `firstname` varchar(255) binary NOT NULL default '',
  `patronymic` varchar(255) NOT NULL default '',
  `comments` varchar(255) NOT NULL default '',
  `dublicate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`SID`),
  KEY `MID_CID` (`MID`,`CID`),
  KEY `MID` (`MID`),
  KEY `CID` (`CID`),
  KEY `base_subject` (`base_subject`)
);


#
# Structure for the `classifiers` table :
#

DROP TABLE IF EXISTS `classifiers`;

CREATE TABLE `classifiers` (
  `classifier_id` int(11) NOT NULL auto_increment,
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` int(11) NOT NULL default '0',
  PRIMARY KEY  (`classifier_id`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
);

DROP TABLE IF EXISTS `classifiers_links`;
CREATE TABLE `classifiers_links` (
  `item_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY  (`item_id`,`classifier_id`, `type`)
);

DROP TABLE IF EXISTS `classifiers_images`;
CREATE TABLE `classifiers_images` (
  `classifier_image_id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`classifier_image_id`)
);



DROP TABLE IF EXISTS `classifiers_types`;

CREATE TABLE `classifiers_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `link_types` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`type_id`)
);

#
# Structure for the `comp2course` table :
#

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `id` INT(11) NOT NULL auto_increment,
    `activity_name` VARCHAR(255) NOT NULL DEFAULT '',
    `subject_name` VARCHAR(255) NULL DEFAULT '',
    `subject_id` INT(11) NOT NULL DEFAULT 0,
    `user_id` INT(11) NOT NULL DEFAULT 0,
    `item_id` INT(11) NOT NULL DEFAULT 0,
    `message` TEXT,
    `created` DATETIME NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    KEY `activity_name` (`activity_name`),
    KEY `subject_name` (`subject_name`),
    KEY `subject_id` (`subject_id`),
    KEY `user_id` (`user_id`),
    KEY `item_id` (`item_id`)
);


DROP TABLE IF EXISTS `comp2course`;

CREATE TABLE `comp2course` (
  `ccoid` int(4) unsigned NOT NULL auto_increment,
  `cid` int(11) unsigned default NULL,
  `tid` int(11) unsigned default NULL,
  `coid` int(11) unsigned default NULL,
  `level` int(4) unsigned default NULL,
  PRIMARY KEY  (`ccoid`),
  KEY `cid` (`cid`),
  KEY `tid` (`tid`),
  KEY `coid` (`coid`)
);

#
# Structure for the `competence` table :
#

DROP TABLE IF EXISTS `competence`;

CREATE TABLE `competence` (
  `coid` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `type` int(4) default NULL,
  `level` int(4) default NULL,
  `status` int(4) default NULL,
  `info` text,
  PRIMARY KEY  (`coid`)
);

#
# Structure for the `competence_roles_vacancy` table :
#

DROP TABLE IF EXISTS `competence_roles_vacancy`;

CREATE TABLE `competence_roles_vacancy`  ( 
	`vacancy_id`     	int(11) NOT NULL,
	`competence_role_id`	int(11) NOT NULL,
	PRIMARY KEY(`vacancy_id`,`competence_role_id`)
);

#
# Structure for the `conf_cid` table :
#

DROP TABLE IF EXISTS `conf_cid`;

CREATE TABLE `conf_cid` (
  `cid` int(10) unsigned NOT NULL default '0',
  `autoindex` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cid`)
);

#
# Structure for the `course2group` table :
#

DROP TABLE IF EXISTS `course2group`;

CREATE TABLE `course2group` (
  `cid` int(11) unsigned default NULL,
  `gid` int(11) unsigned default NULL,
  `cgid` int(11) unsigned default NULL,
  KEY `cid` (`cid`),
  KEY `gid` (`gid`),
  KEY `cgid` (`cgid`)
);

#
# Structure for the `Courses` table :
#

DROP TABLE IF EXISTS `Courses`;

CREATE TABLE `Courses` (
  `CID` int(4) NOT NULL auto_increment,
  `Title` varchar(255) binary  NOT NULL default '',
  `Description` text NOT NULL,
  `TypeDes` tinyint(4) NOT NULL default '0',
  `CD` text NOT NULL,
  `cBegin` date NOT NULL default '0000-00-00',
  `cEnd` date NOT NULL default '0000-00-00',
  `Fee` float NOT NULL default '0',
  `valuta` tinyint(4) NOT NULL default '0',
  `Status` varchar(25) NOT NULL default '',
  `createby` varchar(50) NOT NULL default '',
  `createdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `longtime` int(11) NOT NULL default '0',
  `did` text,
  `credits_student` int(10) unsigned NOT NULL default '0',
  `credits_teacher` int(10) unsigned NOT NULL default '0',
  `locked` tinyint(3) unsigned NOT NULL default '0',
  `chain` int(10) unsigned NOT NULL default '0',
  `is_poll` tinyint(3) unsigned NOT NULL default '0',
  `is_module_need_check` tinyint(3) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `tree` longtext,
  `progress` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `provider` int(11) NOT NULL default '0',
  `provider_options` varchar(255) NOT NULL default '',  
  `planDate` date default NULL,
  `developStatus` varchar(45) default NULL,
  `lastUpdateDate` date default NULL,
  `archiveDate` date default NULL,
  `services` int(11) default '0',
  `has_tree` int(10) default '0',
  `new_window` tinyint(3) NOT NULL default '0',
  `emulate` tinyint(3) NOT NULL default '0',
  `format` int(10) NOT NULL default 0,
  `author` tinyint(3) NOT NULL default 0,
  PRIMARY KEY  (`CID`)
);

#
# Structure for the `courses_stat` table :
#

DROP TABLE IF EXISTS `Courses_stat`;

CREATE TABLE `Courses_stat` (
  `CID` int(11) NOT NULL default '0',
  `last_access` timestamp NOT NULL,
  `MID` int(11) NOT NULL default '0',
  `teacher` char(1) default NULL,
  KEY `CID` (`CID`),
  KEY `MID` (`MID`)
);

#
# Structure for the `cycles` table :
#

DROP TABLE IF EXISTS `cycles`;

CREATE TABLE `cycles` (
  `cycle_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `begin_date` date default NULL,
  `end_date` date default NULL,
  `newcomer_id` int NULL,
  PRIMARY KEY  (`cycle_id`)
);

#
# Structure for the `deans` table :
#

DROP TABLE IF EXISTS `deans`;

CREATE TABLE `deans` (
  `DID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `subject_id`  int(11) NOT NULL default '0',
  PRIMARY KEY `DID` (`DID`),
  KEY `MID` (`MID`)
);


#
# Structure for the `deans` table :
#

DROP TABLE IF EXISTS `dean_poll_users`;

CREATE TABLE `dean_poll_users` (
  `lesson_id`  int(11) NOT NULL default '0',
  `head_mid`  int(11) NOT NULL default '0',
  `student_mid`  int(11) NOT NULL default '0',
  KEY `lesson_id` (`lesson_id`),
  KEY `head_mid` (`head_mid`),
  KEY `student_mid` (`student_mid`)
);


#
# Structure for the `deans_options` table :
#

DROP TABLE IF EXISTS `deans_options`;

CREATE TABLE `deans_options` (
  `user_id` int(11) NOT NULL,
  `unlimited_subjects` int(11) NOT NULL default '1',
  `unlimited_classifiers` int(11) NOT NULL default '1',
  `assign_new_subjects`  int(11) NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `unlimited_subjects` (`unlimited_subjects`),
  KEY `unlimited_classifiers` (`unlimited_classifiers`),
  KEY `assign_new_subjects` (`assign_new_subjects`)
);

DROP TABLE IF EXISTS `deans_responsibilities`;

CREATE TABLE `deans_responsibilities` (
  `user_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL
);

#
# Structure for the `departments` table :
#

DROP TABLE IF EXISTS `departments`;

CREATE TABLE `departments` (
  `did` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `mid` int(10) unsigned NOT NULL default '0',
  `info` blob,
  `color` varchar(255) default NULL,
  `owner_did` int(11) unsigned default NULL,
  `not_in` tinyint(3) unsigned NOT NULL default '0',
  `application` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`did`),
  KEY `mid` (`mid`),
  KEY `application` (`application`)
);


DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `tool` varchar(255) NOT NULL default '',
  `scale_id` int(11) NOT NULL default 1,
  `weight` tinyint(4) NOT NULL default 5,
  PRIMARY KEY  (`event_id`)
);

#
# Structure for the `eventtools` table :
#

DROP TABLE IF EXISTS `EventTools`;

CREATE TABLE `EventTools` (
  `TypeID` bigint(20) NOT NULL auto_increment,
  `TypeName` varchar(255) NOT NULL default '',
  `Student` tinyint(4) NOT NULL default '0',
  `Teacher` tinyint(4) NOT NULL default '0',
  `Deen` tinyint(4) NOT NULL default '0',
  `Icon` varchar(255) NOT NULL default '',
  `XSL` varchar(255) NOT NULL default '',
  `Description` text NOT NULL,
  `tools` text NOT NULL,
  `type` int(11) NOT NULL default '0',
  `weight` float NOT NULL default '0',
  PRIMARY KEY  (`TypeID`)

);

DROP TABLE IF EXISTS `faq`;

CREATE TABLE `faq` (
    `faq_id` int NOT NULL auto_increment,
    `question` TEXT,
    `answer` TEXT,
    `roles` varchar(255) NOT NULL default '',
    `published` ENUM('0','1') default '0',
  PRIMARY KEY  (`faq_id`)
);

#
# Structure for the `file` table :
#

DROP TABLE IF EXISTS `file`;

CREATE TABLE `file` (
  `kod` varchar(100) NOT NULL default '',
  `fnum` int(11) NOT NULL default '0',
  `ftype` int(11) NOT NULL default '0',
  `fname` varchar(100) NOT NULL default '',
  `fdata` mediumblob NOT NULL,
  `fdate` int(10) unsigned NOT NULL default '0',
  `fx` int(11) NOT NULL default '0',
  `fy` int(11) NOT NULL default '0',
  PRIMARY KEY  (`kod`,`fnum`)
);

#
# Structure for the `files` table :
#

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `file_id` int(11)  NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `file_size` int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`)
);

#
# Structure for the `videoblock` table :
#

DROP TABLE IF EXISTS `videoblock`;

CREATE TABLE `videoblock` (
  `file_id` int(11)  NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`file_id`)
);

#
# Structure for the `file_tranfer` table :
#

DROP TABLE IF EXISTS `file_tranfer`;

CREATE TABLE `file_tranfer` (
  `ftID` int(11) NOT NULL auto_increment,
  `ft_key` varchar(32) NOT NULL default '',
  `ModID` int(11) NOT NULL default '0',
  `t_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `MID` int(11) NOT NULL default '0',
  UNIQUE KEY `ft_key` (`ft_key`),
  KEY `ftID` (`ftID`)
);

#
# Structure for the `filefoto` table :
#

DROP TABLE IF EXISTS `filefoto`;

CREATE TABLE `filefoto` (
  `mid` int(11) NOT NULL default '0',
  `foto` mediumblob NOT NULL,
  `last` int(11) NOT NULL default '0',
  `fx` smallint(6) NOT NULL default '0',
  `fy` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`mid`)
);

#
# Structure for the `formula` table :
#

DROP TABLE IF EXISTS `formula`;

CREATE TABLE `formula` (
  `id` mediumint(9) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `formula` text NOT NULL,
  `type` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

#
# Structure for the `forumcategories` table :
# #9201 - Unused
#

DROP TABLE IF EXISTS `forumcategories`;

CREATE TABLE `forumcategories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `cid` int(11) unsigned NOT NULL default '0',
  `create_by` int(11) unsigned NOT NULL default '0',
  `create_date` datetime default NULL,
  `cms` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `cms` (`cms`),
  KEY `cid` (`cid`)
);

#
# Structure for the `forummessages` table :
# #9201 - Unused
#

DROP TABLE IF EXISTS `forummessages`;

CREATE TABLE `forummessages` (
  `id` int(11) NOT NULL auto_increment,
  `thread` int(11) default NULL,
  `posted` varchar(15) default NULL,
  `icon` tinyint(4) default NULL,
  `name` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `sendmail` tinyint(1) default NULL,
  `message` text,
  `is_topic` tinyint(4) default NULL,
  `mid` int(11) unsigned NOT NULL default '0',
  `type` int(11) unsigned NOT NULL default '0',
  `oid` int(11) unsigned NOT NULL default '0',
  `parent` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `thread` (`thread`),
  KEY `mid` (`mid`),
  KEY `is_topic` (`is_topic`),
  KEY `oid` (`oid`),
  KEY `parent` (`parent`),
  KEY `type` (`type`)
);

#
# Structure for the `forumthreads` table :
# #9201 - Unused
#

DROP TABLE IF EXISTS `forumthreads`;

CREATE TABLE `forumthreads` (
  `thread` int(11) NOT NULL auto_increment,
  `category` int(11) unsigned NOT NULL default '0',
  `course` varchar(100) default NULL,
  `lastpost` varchar(15) default NULL,
  `answers` int(11) default '0',
  `private` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`thread`),
  KEY `category` (`category`)
);

#
# Tables for the forum service
#

DROP TABLE IF EXISTS `forums_list`;

CREATE TABLE `forums_list`(
    `forum_id` int unsigned NOT NULL AUTO_INCREMENT,
    `subject_id` int unsigned NOT NULL DEFAULT 0,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `title` varchar(255) NOT NULL,
    `created` datetime NULL,
    `updated` datetime NULL,
    `flags` int unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY  (`forum_id`)
);

DROP TABLE IF EXISTS `forums_sections`;

CREATE TABLE `forums_sections`(
    `section_id` int unsigned NOT NULL AUTO_INCREMENT,
    `lesson_id` int unsigned NOT NULL DEFAULT 0,
    `forum_id` int unsigned NOT NULL,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `parent_id` int unsigned NOT NULL DEFAULT 0,
    `title` varchar(255) NOT NULL,
    `text` mediumtext NOT NULL,
    `created` datetime NULL,
    `updated` datetime NULL,
    `last_msg` datetime NULL,
    `count_msg` int unsigned NOT NULL DEFAULT 0,
    `order` int NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0,
    `is_hidden` int unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`section_id`)
);

DROP TABLE IF EXISTS `forums_messages`;

CREATE TABLE `forums_messages`(
    `message_id` int unsigned NOT NULL AUTO_INCREMENT,
    `forum_id` int unsigned NOT NULL,
    `section_id` int unsigned NOT NULL DEFAULT 0,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `level` int unsigned NOT NULL,
    `answer_to` int unsigned NOT NULL DEFAULT 0,
    `title` varchar(255) NOT NULL,
    `text` mediumtext NOT NULL,
    `text_preview` varchar(255) NOT NULL,
    `text_size` int unsigned NOT NULL DEFAULT 0,
    `created` datetime NULL,
    `updated` datetime NULL,
    `delete_date` datetime NULL,
    `deleted_by` int NOT NULL DEFAULT 0,
    `rating` int NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0,
    `is_hidden` int unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`message_id`)
);

DROP TABLE IF EXISTS `forums_messages_showed`;

CREATE TABLE `forums_messages_showed`(
    `user_id` int unsigned NOT NULL,
    `message_id` int unsigned NOT NULL,
    `created` datetime NULL,
    PRIMARY KEY(`user_id`, `message_id`)
);

#
# Structure for the `graduated` table :
#

DROP TABLE IF EXISTS `graduated`;

CREATE TABLE `graduated` (
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
  PRIMARY KEY  (`SID`),
  KEY `MID` (`MID`),
  KEY `CID` (`CID`),
  KEY `MID_CID` (`MID`,`CID`)
);

#
# Structure for the `certificates` table :
#

DROP TABLE IF EXISTS `certificates`;

CREATE TABLE `certificates` (
  `certificate_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `subject_id` int(11) NOT NULL default '0',
  `created` datetime default NULL,
  `name` VARCHAR(50) NULL DEFAULT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `organization` VARCHAR(50) NULL DEFAULT NULL,
  `startdate` DATE NULL DEFAULT NULL,
  `enddate` DATE NULL DEFAULT NULL,
  `filename` VARCHAR(50) NULL DEFAULT NULL,
  PRIMARY KEY  (`certificate_id`),
  KEY `USERID` (`user_id`),
  KEY `SUBJECTID` (`subject_id`),
  KEY `USER_SUBJECT` (`user_id`,`subject_id`)
);

#
# Structure for the `groupname` table :
#

DROP TABLE IF EXISTS `groupname`;

CREATE TABLE `groupname` (
  `gid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `owner_gid` INT (11) DEFAULT NULL,
  PRIMARY KEY  (`gid`),
  KEY `cid` (`cid`)
);

#
# Structure for the `groupuser` table :
#

DROP TABLE IF EXISTS `groupuser`;

CREATE TABLE `groupuser` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`gid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`),
  KEY `gid` (`gid`)
);

#
# Structure for the `knigi` table :
#

DROP TABLE IF EXISTS `Knigi`;

CREATE TABLE `Knigi` (
  `KID` int(11) NOT NULL auto_increment,
  `CID` int(11) NOT NULL default '0',
  `Name` text NOT NULL,
  `Author` text NOT NULL,
  `Izdatel` text NOT NULL,
  `Year` int(11) NOT NULL default '0',
  `Description` longtext NOT NULL,
  `Url` text NOT NULL,
  `access` int(11) NOT NULL default '0',
  `file_ext` varchar(10) default NULL,
  `file_active` int(11) NOT NULL default '0',
  PRIMARY KEY  (`KID`),
  KEY `CID` (`CID`) 
);

#
# Structure for the `list` table :
#

DROP TABLE IF EXISTS `list`;

CREATE TABLE `list` (
  `kod` varchar(100) NOT NULL default '',
  `qtype` int(11) NOT NULL default '0',
  `qdata` text NOT NULL,
  `qtema` varchar(255) NOT NULL default '',
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
  `prepend_test` varchar(255) NOT NULL default '',
  `is_poll` tinyint(4) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
  `ordr` int(11) NOT NULL default '10',
  `name` varchar(255),
  PRIMARY KEY  (`kod`),
  KEY `id` (`id`),
  KEY `qtype` (`qtype`),
  KEY `is_poll` (`is_poll`)
);

#
# Structure for the `list_files` table :
#

DROP TABLE IF EXISTS `list_files`;
CREATE TABLE `list_files` (
  `file_id` int(11) NOT NULL,
  `kod` varchar(255) NOT NULL,
  PRIMARY KEY  (`file_id`,`kod`)
);

#
# Structure for the `logseance` table :
#

DROP TABLE IF EXISTS `logseance`;

CREATE TABLE `logseance` (
  `stid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL default '0',
  `kod` varchar(255) NOT NULL default '',
  `number` smallint(5) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `bal` float NOT NULL default '0',
  `balmax` float NOT NULL default '0',
  `balmin` float NOT NULL default '0',
  `good` int(11) NOT NULL default '0',
  `vopros` longblob NOT NULL,
  `otvet` longblob NOT NULL,
  `attach` longblob NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `sheid` int(11) NOT NULL default '0',
  `comments` text,
  `review` longblob NOT NULL,
  `review_filename` varchar(255) NOT NULL default '',
  `qtema` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`stid`,`kod`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`),
  KEY `kod` (`kod`),
  KEY `sheid` (`sheid`)
);

#
# Structure for the `loguser` table :
#

DROP TABLE IF EXISTS `loguser`;

CREATE TABLE `loguser` (
  `stid` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL default '0',
  `balmax` float NOT NULL default '0',
  `balmin` float NOT NULL default '0',
  `balmax2` float NOT NULL default '0',
  `balmin2` float NOT NULL default '0',
  `bal` float NOT NULL default '0',
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
);

#
# Structure for the `mod_content` table :
#

DROP TABLE IF EXISTS `mod_content`;

CREATE TABLE `mod_content` (
  `McID` int(11) NOT NULL auto_increment,
  `Title` varchar(255) NOT NULL default '',
  `ModID` int(11) NOT NULL default '0',
  `mod_l` varchar(255) default NULL,
  `type` varchar(40) NOT NULL default '0',
  `conttype` varchar(80) default NULL,
  `scorm_params` text NOT NULL,
  UNIQUE KEY `ModID` (`McID`)
);

#
# Structure for the `mod_list` table :
#

DROP TABLE IF EXISTS `mod_list`;

CREATE TABLE `mod_list` (
  `ModID` int(11) NOT NULL auto_increment,
  `Title` varchar(255) NOT NULL default '',
  `Num` varchar(40) NOT NULL default '0',
  `Descript` varchar(255) default NULL,
  `Pub` tinyint(2) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  `PID` int(11) NOT NULL default '0',
  `forum_id` int(11) default NULL,
  `test_id` varchar(255) NOT NULL default '',
  `run_id` varchar(255) NOT NULL default '',
  UNIQUE KEY `ModID` (`ModID`)
);

#
# Structure for the `money` table :
#

DROP TABLE IF EXISTS `money`;

CREATE TABLE `money` (
  `moneyid` int(10) unsigned NOT NULL auto_increment,
  `sum` int(11) default NULL,
  `mid` int(11) default NULL,
  `trid` int(11) default NULL,
  `date` int(11) default NULL,
  `type` int(11) default NULL,
  `sign` int(11) default NULL,
  `info` varchar(255) default NULL,
  PRIMARY KEY  (`moneyid`),
  KEY `mid` (`mid`) 
);

#
# Structure for the `new_news` table :
#

DROP TABLE IF EXISTS `new_news`;

CREATE TABLE `new_news` (
  `nID` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `autor` varchar(40) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `Title` varchar(120) NOT NULL default '',
  `news` text NOT NULL,
  `perm` tinyint(2) NOT NULL default '0',
  `inarhiv` tinyint(2) NOT NULL default '0',
  `image` blob,
  UNIQUE KEY `nID` (`nID`),
  KEY `nID_2` (`nID`)
);

#
# Structure for the `news` table :
#

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `author` varchar(255) default NULL,
  `created_by` int(11) default NULL,
  `announce` text NOT NULL,
  `message` text NOT NULL,
  `subject_name` varchar(255) default NULL,
  `subject_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `subject_id` (`subject_id`)
);

#
# Structure for the `news2` table :
#

DROP TABLE IF EXISTS `news2`;

CREATE TABLE `news2` (
  `nID` int(11) NOT NULL auto_increment,
  `date` timestamp NULL,
  `Title` varchar(255) NOT NULL default '',
  `author` varchar(50) default NULL,
  `message` text NOT NULL,
  `lang` char(3) NOT NULL default '',
  `show` int(1) NOT NULL default '0',
  `standalone` int(1) NOT NULL default '0',
  `application` tinyint(4) default 0,
  `soid` varchar(16) default NULL,
  `resource_id` int(11) default NULL,
  `type` int(4) NOT NULL default '0',
  PRIMARY KEY  (`nID`)
);

DROP TABLE IF EXISTS `oauth_apps`;
CREATE TABLE `oauth_apps`
(
  `app_id`     int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `created`    datetime NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `callback_url`      varchar(255) NOT NULL DEFAULT '',
  `api_key`      varchar(255) NOT NULL DEFAULT '',
  `consumer_key`      varchar(255) NOT NULL DEFAULT '',
  `consumer_secret`      varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`app_id`)
)
;

DROP TABLE IF EXISTS `oauth_tokens`;
CREATE TABLE `oauth_tokens`
(
  `token_id`     int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id`       int(11) NOT NULL DEFAULT 0,
  `token`        varchar(255) NOT NULL DEFAULT '',
  `token_secret` varchar(255) NOT NULL DEFAULT '',
  `state`        tinyint(4) NOT NULL DEFAULT 0,
  `verify`       varchar(255) NOT NULL DEFAULT '',
  `user_id`      int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`token_id`),
  KEY `app_id` (`app_id`),
  KEY `user_id` (`user_id`) 
)
;

DROP TABLE IF EXISTS `oauth_nonces`;
CREATE TABLE `oauth_nonces`
(
  `nonce_id`     int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id`       int(11) NOT NULL DEFAULT 0,
  `ts`           datetime NOT NULL,
  `nonce`        varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`nonce_id`),
  KEY `app_id` (`app_id`)
)
;

#
# Structure for the `options` table :
#

DROP TABLE IF EXISTS `options_at`;

CREATE TABLE `options_at` (
  `OptionID` tinyint(4) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`OptionID`)
);


DROP TABLE IF EXISTS `options_cms`;

CREATE TABLE `options_cms` (
  `OptionID` tinyint(4) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`OptionID`)
);

DROP TABLE IF EXISTS `OPTIONS`;

CREATE TABLE `OPTIONS` (
  `OptionID` tinyint(4) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`OptionID`)
);

#
# Structure for the `organizations` table :
#

DROP TABLE IF EXISTS `organizations`;

CREATE TABLE `organizations` (
  `oid` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
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
);

#
# Structure for the `people` table :
#

DROP TABLE IF EXISTS `password_history`;

CREATE TABLE `password_history` (
  `user_id` int(10) NULL,
  `password` varchar(255) default NULL,
  `change_date` datetime NOT NULL,
  KEY `user_id` (`user_id`) 
);

DROP TABLE IF EXISTS `simple_auth`;

CREATE TABLE `simple_auth` (
	`auth_key` CHAR(32) NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL,
	`link` VARCHAR(255) NOT NULL,
	`valid_before` DATETIME NOT NULL,
	PRIMARY KEY (`auth_key`)
);

DROP TABLE IF EXISTS `People`;

CREATE TABLE `People` (
  `MID` int(11) NOT NULL auto_increment,
  `mid_external` varchar(255) NOT NULL default '',
  `LastName` varchar(255) binary NOT NULL default '',
  `FirstName` varchar(255) binary NOT NULL default '',
  `LastNameLat` varchar(255) binary NOT NULL default '',
  `FirstNameLat` varchar(255) binary NOT NULL default '',
  `Patronymic` varchar(255) NOT NULL default '',
  `Registered` datetime default NULL,
  `Course` int(11) NOT NULL default '1',
  `EMail` varchar(255) NOT NULL default '',
  `email_confirmed` tinyint(4) unsigned NOT NULL default '0',
  `Phone` varchar(255) NOT NULL default '',
  `Information` text NOT NULL,
  `Address` text NOT NULL,
  `Fax` varchar(255) NOT NULL default '',
  `Login` varchar(255) NOT NULL default '',
  `Password` varchar(255) NOT NULL default '',
  `javapassword` varchar(20) NOT NULL default '',
  `BirthDate` date NOT NULL default '0000-00-00',
  `CellularNumber` varchar(255) NOT NULL default '',
  `ICQNumber` int(11) NOT NULL default '0',
  `Gender` tinyint(4) NOT NULL default '0',
  `last` bigint(20) unsigned NOT NULL default '0',
  `countlogin` int(11) NOT NULL default '0',
  `rnid` tinyint(4) NOT NULL default '0',
  `Position` VARCHAR(255) NOT NULL default '',
  `PositionDate` date NOT NULL default '0000-00-00',
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
  `dublicate` int(11) unsigned default 0,
  PRIMARY KEY  (`MID`)
);

#
# Structure for the `periods` table :
#

DROP TABLE IF EXISTS `periods`;

CREATE TABLE `periods` (
  `lid` int(10) unsigned NOT NULL auto_increment,
  `starttime` int(11) default '540',
  `stoptime` int(11) default '630',
  `name` varchar(255) default NULL,
  `count_hours` int(11) default '2',
  PRIMARY KEY  (`lid`)
);

#
# Structure for the `permission2act` table :
#

DROP TABLE IF EXISTS `permission2act`;

CREATE TABLE `permission2act` (
  `pmid` int(11) unsigned NOT NULL default '0',
  `acid` varchar(8) NOT NULL default '',
  `type` varchar(255) NOT NULL default 'dean',
  PRIMARY KEY  (`pmid`,`acid`,`type`)
);

#
# Structure for the `permission2mid` table :
#

DROP TABLE IF EXISTS `permission2mid`;

CREATE TABLE `permission2mid` (
  `pmid` int(11) unsigned NOT NULL default '0',
  `mid` int(11) unsigned default NULL,
  KEY `pmid_mid` (`pmid`,`mid`),
  KEY `pmid` (`pmid`),
  KEY `mid` (`mid`)
);

#
# Structure for the `permission_groups` table :
#

DROP TABLE IF EXISTS `permission_groups`;

CREATE TABLE `permission_groups` (
  `pmid` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  `default` enum('0','1') default '0',
  `type` varchar(255) default 'dean',
  `rang` int(11) unsigned NOT NULL default '0',
  `application` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pmid`)
);

#
# Structure for the `personal` table :
#

DROP TABLE IF EXISTS `personal`;

CREATE TABLE `personal` (
  `PID` tinyint(4) NOT NULL auto_increment,
  `FIO` varchar(60) NOT NULL default '',
  `work` varchar(120) NOT NULL default '',
  `tel` varchar(40) NOT NULL default '',
  `email` varchar(40) NOT NULL default '',
  `type` varchar(255) NOT NULL default '0',
  UNIQUE KEY `PID` (`PID`),
  KEY `PID_2` (`PID`)
);

#
# Structure for the `posts` table :
#

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `posted` varchar(15) NOT NULL default '',
  `name` varchar(255) default NULL,
  `course` varchar(100) default NULL,
  `email` varchar(255) default NULL,
  `text` text,
  PRIMARY KEY  (`posted`)
);

#
# Structure for the `posts3` table :
#

DROP TABLE IF EXISTS `posts3`;

CREATE TABLE `posts3` (
  `PostID` int(11) NOT NULL auto_increment,
  `posted` timestamp NOT NULL,
  `name` varchar(120) NOT NULL default '',
  `CID` int(11) NOT NULL default '0',
  `email` varchar(120) NOT NULL default '',
  `text` text NOT NULL,
  `mid` int(11) unsigned NOT NULL default '0',
  `startday` int(10) unsigned NOT NULL default '0',
  `stopday` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `PostID` (`PostID`),
  KEY `PostID_2` (`PostID`)
);

#
# Structure for the `ppt2swf` table :
#

DROP TABLE IF EXISTS `ppt2swf`;
CREATE TABLE `ppt2swf` (
  `status` int(11) NOT NULL default '0',
  `process` int(11) NOT NULL default '0',
  `success_date` datetime NULL,
  `pool_id` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `webinar_id` int(11) NOT NULL
);


DROP TABLE IF EXISTS `processes`;
CREATE TABLE `processes` (
  `process_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `chain` text,
  `type` int(11) default NULL,
  `programm_id` int(11) default NULL,
  PRIMARY KEY  (`process_id`)
);

DROP TABLE IF EXISTS `programm`;
CREATE TABLE `programm` (
  `programm_id` int(11) NOT NULL auto_increment,
  `programm_type` tinyint(4) default NULL,
  `item_id` int(11) default NULL,  
  `item_type` tinyint(4) default NULL,   
  `mode_strict` tinyint(4) default 1,   
  `mode_finalize` tinyint(4) default 0,   
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`programm_id`)
);

DROP TABLE IF EXISTS `programm_events`;
CREATE TABLE `programm_events` (
  `programm_event_id` int(11) NOT NULL auto_increment,
  `programm_id` int(11) default NULL,
  `name` varchar(255) default NULL,
  `type` int(11) default NULL,
  `item_id` int(11) default NULL,
  `day_begin` tinyint(4) default 1,
  `day_end` tinyint(4) default 1,
  `ordr` tinyint(4) default NULL,
  PRIMARY KEY  (`programm_event_id`)
);

DROP TABLE IF EXISTS `programm_events_users`;
CREATE TABLE `programm_events_users` (
  `programm_event_user_id` int(11) NOT NULL auto_increment,
  `programm_event_id` int(11) default NULL,
  `programm_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `begin_date` datetime default NULL,
  `end_date` datetime default NULL,
  `status` int(11) default '0',
  PRIMARY KEY  (`programm_event_user_id`)  
);

DROP TABLE IF EXISTS `programm_users`;
CREATE TABLE `programm_users` (
  `programm_user_id` int(11) NOT NULL auto_increment,
  `programm_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assign_date` datetime NOT NULL,
  PRIMARY KEY  (`programm_user_id`)
);

DROP TABLE IF EXISTS `questionnaires`;
CREATE TABLE `questionnaires` (
  `quest_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(16) DEFAULT NULL,
  `status` TINYINT(4) DEFAULT 0,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `info` TEXT DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `mode_selection` TINYINT(4) DEFAULT NULL,
  `mode_selection_questions` TINYINT(4) DEFAULT NULL,
  `mode_selection_all_shuffle` TINYINT(4) DEFAULT NULL,
  `mode_passing` TINYINT(4) DEFAULT NULL,
  `mode_display` TINYINT(4) DEFAULT NULL,
  `mode_display_clusters` TINYINT(4) DEFAULT NULL,
  `mode_display_questions` TINYINT(4) DEFAULT NULL,
  `show_result` TINYINT(4) DEFAULT NULL,
  `show_log` TINYINT(4) DEFAULT NULL,
  `limit_time` TINYINT(4) DEFAULT NULL,
  `limit_attempts` TINYINT(4) DEFAULT NULL,
  PRIMARY KEY (`quest_id`)
);

DROP TABLE IF EXISTS `quest_clusters`;
CREATE TABLE `quest_clusters` (
  `cluster_id` int(11) NOT NULL AUTO_INCREMENT,
  `quest_id` INTEGER(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cluster_id`)
);

DROP TABLE IF EXISTS `quest_questions`;
CREATE TABLE `quest_questions` (
  `question_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` INTEGER(11) DEFAULT NULL,
  `type` VARCHAR(16) DEFAULT NULL,
  `question` TEXT NOT NULL,
  `shorttext` VARCHAR(255) DEFAULT NULL,
  `mode_scoring` TINYINT(4) DEFAULT NULL,
  `show_free_variant` TINYINT(4) DEFAULT NULL,  
  PRIMARY KEY (`question_id`)
);

DROP TABLE IF EXISTS `quest_question_quests`;
CREATE TABLE `quest_question_quests` (
  `question_id` INTEGER(11) NOT NULL,
  `quest_id` INTEGER(11) NOT NULL,
  KEY (`question_id`, `quest_id`)
);

DROP TABLE IF EXISTS `quest_question_variants`;
CREATE TABLE `quest_question_variants` (
  `question_variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `variant` text,
  `shorttext` varchar(255) DEFAULT NULL,
  `file_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `weight` float(11,0) DEFAULT NULL,
  PRIMARY KEY (`question_variant_id`)
);

DROP TABLE IF EXISTS `quest_attempts`;
CREATE TABLE `quest_attempts` (
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
  `duration` int(11) DEFAULT NULL,
  `is_resultative` tinyint(11) DEFAULT NULL,
  PRIMARY KEY (`attempt_id`)
);

DROP TABLE IF EXISTS `quest_categories`;
CREATE TABLE `quest_categories` (
  `category_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `quest_id` INTEGER(11) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`category_id`)
);

DROP TABLE IF EXISTS `quest_question_results`;
CREATE TABLE `quest_question_results` (
  `question_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `variant` VARCHAR(255) DEFAULT NULL,
  `free_variant` TEXT DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT NULL,
  `score_weighted` float(9,3) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`question_result_id`)
);

DROP TABLE IF EXISTS `quest_category_results`;
CREATE TABLE `quest_category_results` (
  `category_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `attempt_id` int(11) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `result` text,
  PRIMARY KEY (`category_result_id`)
);

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE `quizzes` (
  `quiz_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL default '',
  `subject_id` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`quiz_id`)
);

DROP TABLE IF EXISTS `quizzes_feedback`;
CREATE TABLE `quizzes_feedback` (
  `user_id` int(11) NOT NULL default '0',
  `subject_id` int(11) NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `begin` datetime,
  `end` datetime,
  `place` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `subject_name` varchar(255) NOT NULL default '',
  `trainer` varchar(255) NOT NULL default '',
  `trainer_id` int(11) NOT NULL default '0',
  `created` datetime,  
  PRIMARY KEY  (`user_id`, `subject_id`, `lesson_id`)
);

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `task_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL default '',
  `subject_id` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`task_id`)
);


DROP TABLE IF EXISTS `quizzes_answers`;
CREATE TABLE `quizzes_answers` (
  `quiz_id` int(10) unsigned NOT NULL,
  `question_id` varchar(255) NOT NULL,
  `question_title` varchar(255)  NOT NULL default '',
  `theme` varchar(255)  NOT NULL default '',
  `answer_id` int(11) NOT NULL default '0',
  `answer_title` varchar(255)  NOT NULL default '',
  PRIMARY KEY  (`quiz_id`, `question_id`, `answer_id`)
);

DROP TABLE IF EXISTS `quizzes_results`;
CREATE TABLE `quizzes_results` (
  `user_id` int(10) unsigned NOT NULL,
  `lesson_id` int(10) unsigned NOT NULL,
  `question_id` varchar(255) NOT NULL,
  `answer_id` int(11) NOT NULL default '0',
  `freeanswer_data` text NOT NULL default '',
  `quiz_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL default '0',
  `junior_id` int(10) unsigned NOT NULL default '0',
  KEY  (`user_id`, `lesson_id`, `question_id`, `answer_id`)
);

DROP TABLE IF EXISTS `rank`;

CREATE TABLE `rank` (
  `rnid` tinyint(4) NOT NULL auto_increment,
  `Title` varchar(64) default NULL,
  PRIMARY KEY  (`rnid`)
);

#
# Structure for the `reckoning_courses` table :
#

DROP TABLE IF EXISTS `reckoning_courses`;

CREATE TABLE `reckoning_courses` (
  `trid` int(11) default NULL,
  `cid` int(11) default NULL,
  `mid` int(11) default NULL,
  `mark` varchar(255) NOT NULL default '0',
  KEY `trid` (`trid`),
  KEY `cid` (`cid`),
  KEY `mid` (`mid`)
);

DROP TABLE IF EXISTS `reports`;

CREATE TABLE `reports` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `domain` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `fields` text,
  `created` datetime,
  `created_by` int(10) DEFAULT 0 NOT NULL,
  `status` tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (`report_id`)
);

DROP TABLE IF EXISTS `responsibilities`;
CREATE  TABLE `responsibilities` (
  `responsibility_id` INT NOT NULL auto_increment,
  `user_id` INT NULL ,
  `item_type` INT NULL,
  `item_id` INT NULL,
  PRIMARY KEY (`responsibility_id`)
);

DROP TABLE IF EXISTS `recruiters`;
CREATE  TABLE `recruiters` (
  `recruiter_id` INT NOT NULL auto_increment,
  `user_id` INT NULL ,
  `hh_auth_data` TEXT NULL,
  PRIMARY KEY (`recruiter_id`)
);

DROP TABLE IF EXISTS `room`;

CREATE TABLE `room` (
  `rid` int(11) NOT NULL default '0',
  `name` varchar(255) default 'NULL',
  `descript` varchar(255) default 'NULL',
  `typ` char(1) default 'N',
  `adminid` int(11) default '0'
);

#
# Structure for the `rooms` table :
#

DROP TABLE IF EXISTS `rooms`;

CREATE TABLE `rooms` (
  `rid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `volume` int(11) default NULL,
  `status` int(11) default NULL,
  `type` int(11) default NULL,
  `description` text,
  PRIMARY KEY  (`rid`)
);

#
# Structure for the `rooms2course` table :
#

DROP TABLE IF EXISTS `rooms2course`;

CREATE TABLE `rooms2course` (
  `rid` int(11) default NULL,
  `cid` int(11) default NULL,
  KEY `rid_cid` (`rid`,`cid`),
  KEY `rid` (`rid`),
  KEY `cid` (`cid`)
);

#
# Structure for the `schedule` table :
#

DROP TABLE IF EXISTS `schedule`;

CREATE TABLE `schedule` (
  `SHEID` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `url` text,
  `descript` text NOT NULL,
  `begin` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `createID` int(11) NOT NULL default '0',
  `createDate` datetime NULL,
  `typeID` int(11) NOT NULL default '0',
  `vedomost` int(11) default '0',
  `CID` int(11) NOT NULL default '0',
  `CHID` int(11) default NULL,
  `startday` int(11) NOT NULL DEFAULT '0',
  `stopday` int(11) NOT NULL DEFAULT '0',
  `timetype` int(11) NOT NULL DEFAULT '0',
  `isgroup` enum('0','1') default '0',
  `cond_sheid` varchar(255) default '-1',
  `cond_mark` varchar(255) NOT NULL default '-',
  `cond_progress` varchar(255) NOT NULL default '0',
  `cond_avgbal` varchar(255) NOT NULL default '0',
  `cond_sumbal` varchar(255) NOT NULL default '0',
  `cond_operation` tinyint(3) unsigned NOT NULL default '0',
  `period` varchar(255) NOT NULL default '-1',
  `rid` int(11) NOT NULL default '0',
  `teacher` int(11) unsigned NOT NULL default '0',
  `moderator` int(11) unsigned NOT NULL default '0',
  `gid` int(11) default '-1',
  `perm` int(11) NOT NULL default '0',
  `pub` tinyint(1) NOT NULL default '0',
  `sharepointId` int(11) NOT NULL default '0',
  `connectId` varchar(255) NOT NULL default '',
  `recommend` enum('0','1') NOT NULL default '0',
  `notice` int(11) NOT NULL default '0',
  `notice_days` int(11) NOT NULL default '0',
  `all` enum('0','1') NOT NULL default '0',
  `params` text,
  `activities` text,
  `order` int(11) default 0,
  `tool` varchar(255) NOT NULL default '',
  `isfree` tinyint(4) NOT NULL default '0',  
  `section_id` int(11) NULL,
  `session_id` int(11) NULL,
  PRIMARY KEY  (`SHEID`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `typeID` (`typeID`),
  KEY `vedomost` (`vedomost`),
  KEY `CID` (`CID`),
  KEY `CHID` (`CHID`),
  KEY `period` (`period`),
  KEY `rid` (`rid`),
  KEY `gid` (`gid`)
);

#
# Structure for the `scheduleid` table :
#

DROP TABLE IF EXISTS `scheduleID`;

CREATE TABLE `scheduleID` (
  `SSID` int(11) NOT NULL auto_increment,
  `SHEID` int(11) NOT NULL default '0',
  `MID` int(11) NOT NULL default '0',
  `beginRelative` datetime DEFAULT NULL,
  `endRelative` datetime DEFAULT NULL,  
  `gid` int(11) default NULL,
  `isgroup` enum('0','1') default '0',
  `V_STATUS` double NOT NULL default '-1',
  `V_DONE` int(11) NOT NULL default '0',
  `V_DESCRIPTION` varchar(255) NOT NULL default '',
  `DESCR` text,
  `SMSremind` tinyint(4) NOT NULL default '0',
  `ICQremind` tinyint(4) NOT NULL default '0',
  `EMAILremind` tinyint(4) NOT NULL default '0',
  `ISTUDremind` tinyint(4) NOT NULL default '0',
  `test_corr` int(11) NOT NULL default '0',
  `test_wrong` int(11) NOT NULL default '0',
  `test_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `test_answers` text,
  `test_tries` tinyint(4) default '0',
  `toolParams` text,
  `comments` text,
  `chief` tinyint(3) unsigned NOT NULL default '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `launched` datetime NOT NULL,
  PRIMARY KEY  (`SSID`),
  KEY `MID` (`MID`),
  KEY `SHEID` (`SHEID`),
  KEY `SHEID_MID` (`SHEID`,`MID`)
);

#
# Structure for the `schedule_marks_history` table :
#

DROP TABLE IF EXISTS `schedule_marks_history`;

CREATE TABLE `schedule_marks_history`  ( 
	`MID`    	int(11) NOT NULL,
	`SSID`  	int(11) NOT NULL,
	`mark`   	int(11) NOT NULL DEFAULT '0',
	`updated`	datetime NOT NULL, 
	KEY `MID` (`MID`),
        KEY `SSID` (`SSID`)	
);


#
# Structure for the `seance` table :
#

DROP TABLE IF EXISTS `seance`;

CREATE TABLE `seance` (
  `stid` int(11) NOT NULL default '0',
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL default '0',
  `kod` varchar(255) NOT NULL default '',
  `attach` longblob NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  `text` blob NOT NULL,
  `time` timestamp NOT NULL,
  `bal` float default NULL,
  `lastbal` float default NULL,
  `comments` text,
  `review` blob NOT NULL,
  `review_filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`stid`,`kod`),
  KEY `mid` (`mid`),
  KEY `stid` (`stid`),
  KEY `cid` (`cid`),
  KEY `tid` (`tid`),
  KEY `kod` (`kod`)
);

#
# Structure for the `str_of_organ2competence` table :
#

DROP TABLE IF EXISTS `str_of_organ2competence`;

CREATE TABLE `str_of_organ2competence` (
  `coid` int(11) NOT NULL default '0',
  `soid` int(11) NOT NULL default '0',
  `percent` tinyint(4) default '0',
  PRIMARY KEY  (`coid`,`soid`),
  KEY `coid` (`coid`),
  KEY `soid` (`soid`)
);

#
# Structure for the `structure_of_organ` table :
#

DROP TABLE IF EXISTS `structure_of_organ`;

CREATE TABLE `structure_of_organ` (
  `soid` int(11) NOT NULL auto_increment,
  `soid_external` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `code` varchar(16) default NULL,
  `mid` int(11) default '0',
  `info` text,
  `owner_soid` int(11) default NULL,
  `profile_id` int(11) default NULL,  
  `agreem` tinyint(1) unsigned default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `own_results` tinyint(3) unsigned NOT NULL default '1',
  `enemy_results` tinyint(3) unsigned NOT NULL default '1',
  `display_results` tinyint(3) unsigned NOT NULL default '0',
  `threshold` tinyint(3) unsigned default NULL,
  `specialization` int(10) NOT NULL default '0',
  `claimant` tinyint(3) NOT NULL default '0',
  `lft` INT NULL DEFAULT 0 ,
  `level` INT NULL DEFAULT 0,
  `rgt` INT NULL DEFAULT 0,
  `is_manager` INT NOT NULL DEFAULT 0,
  `blocked` INT NOT NULL DEFAULT 0,
  PRIMARY KEY  (`soid`),
  KEY `mid` (`mid`),
  KEY `owner_soid` (`owner_soid`),
  KEY `type` (`type`),
  KEY `claimant` (`claimant`)
);

#
# Structure for the `students` table :
#

DROP TABLE IF EXISTS `Students`;

CREATE TABLE `Students` (
  `SID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  `cgid` int(11) NOT NULL default '0',
  `Registered` int(11) NOT NULL default '1',
  `time_registered` timestamp NULL,
  `offline_course_path` varchar(255) NOT NULL default '',
  `time_ended` timestamp NULL,
  `time_ended_planned` timestamp NULL,  
  PRIMARY KEY  (`SID`),
  UNIQUE KEY `MID_CID` (`MID`,`CID`),
  KEY `CID` (`CID`),
  KEY `MID` (`MID`)
);

#
# Structure for the `teachers` table :
#

DROP TABLE IF EXISTS `tag`;
CREATE TABLE tag
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `body`       varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `tag_ref_blog`;
DROP TABLE IF EXISTS `tag_ref`;
CREATE TABLE `tag_ref`
(
  `tag_id`  INT(10) UNSIGNED NOT NULL,
  `item_type` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL,  
  PRIMARY KEY (`tag_id`, `item_type`, `item_id`)
);

DROP TABLE IF EXISTS `Teachers`;

CREATE TABLE `Teachers` (
  `PID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  `CID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`PID`),
  UNIQUE KEY `MID_CID` (`MID`,`CID`),
  KEY `MID` (`MID`),
  KEY `CID` (`CID`)
);

#
# Structure for the `teachnotes` table :
#

DROP TABLE IF EXISTS `teachnotes`;

CREATE TABLE `teachnotes` (
  `NOTID` int(11) NOT NULL auto_increment,
  `SHEID` int(11) NOT NULL default '0',
  `MID` int(11) NOT NULL default '0',
  `noteText` text,
  `ISTUDremind` tinyint(4) NOT NULL default '0',
  `SMSremind` tinyint(4) NOT NULL default '0',
  `EMAILremind` tinyint(4) NOT NULL default '0',
  `ICQremind` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`NOTID`)
);

#
# Structure for the `test` table :
#

DROP TABLE IF EXISTS `test`;

CREATE TABLE `test` (
  `tid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `cidowner` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
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
  `allow_view_log` enum('0','1') NOT NULL default '1',
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
);

DROP TABLE IF EXISTS `test_abstract`;
CREATE TABLE `test_abstract` (
  `test_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `keywords` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL default '',
  `subject_id` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`test_id`)
);

DROP TABLE IF EXISTS `test_feedback`;
CREATE TABLE `test_feedback` (
  `test_feedback_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `type` int(11) default NULL,
  `text` text,
  `parent` int(11) default NULL,
  `treshold_min` int(11) default NULL,
  `treshold_max` int(11) default NULL,
  `test_id` int(11) default NULL,
  `question_id` varchar(45) default NULL,
  `answer_id` varchar(45) default NULL,
  `show_event` int(11) NOT NULL default '0',
  `show_on_values` text,
  PRIMARY KEY  (`test_feedback_id`),
  KEY `parent` (`parent`),
  KEY `type` (`type`),
  KEY `treshold` (`treshold_min`,`treshold_max`),
  KEY `test_id` (`test_id`)
);

DROP TABLE IF EXISTS `tests_questions`;
CREATE TABLE `tests_questions` (
  `subject_id` int(11) NOT NULL DEFAULT 0,
  `test_id` int(11) NOT NULL DEFAULT 0,
  `kod` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY  (`subject_id`, `test_id`, `kod`),
  KEY `kod` (`kod`),
  KEY `subject_id` (`subject_id`),
  KEY `test_id` (`test_id`)
);

DROP TABLE IF EXISTS `exercises`;
CREATE TABLE `exercises` (
  `exercise_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `status` int(11) NOT NULL default '0',
  `description` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `questions` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL default '',
  `subject_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`exercise_id`)
);


#
# Structure for the `testcontent` table :
#

DROP TABLE IF EXISTS `TestContent`;

CREATE TABLE `TestContent` (
  `QID` int(11) NOT NULL auto_increment,
  `TID` int(11) NOT NULL default '0',
  `xmlQ` text NOT NULL,
  `questionText` text NOT NULL,
  `type` tinyint(4) NOT NULL default '0',
  `attachFileName` varchar(255) default NULL,
  `attachExt` varchar(255) default NULL,
  `theme` varchar(255) NOT NULL default '',
  `isObligatory` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`QID`)
);

#
# Structure for the `testcount` table :
#

DROP TABLE IF EXISTS `testcount`;

CREATE TABLE `testcount` (
  `mid` int(11) NOT NULL default '0',
  `tid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `qty` smallint(5) unsigned NOT NULL default '0',
  `last` int(10) unsigned NOT NULL default '0',
  `lesson_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`tid`, `cid`, `lesson_id`)
);

#
# Structure for the `testneed` table :
#

DROP TABLE IF EXISTS `testneed`;

CREATE TABLE `testneed` (
  `tid` int(11) NOT NULL default '0',
  `kod` varchar(100) NOT NULL default ''
);

#
# Structure for the `testquestions` table :
#

DROP TABLE IF EXISTS `testquestions`;

CREATE TABLE `testquestions` (
  `tid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  `questions` blob NOT NULL,
  PRIMARY KEY  (`tid`,`cid`)
);

#
# Structure for the `testtitle` table :
#

DROP TABLE IF EXISTS `TestTitle`;

CREATE TABLE `TestTitle` (
  `TID` int(11) NOT NULL auto_increment,
  `Title` varchar(255) NOT NULL default '',
  `CID` int(11) NOT NULL default '0',
  `timelim` varchar(255) NOT NULL default '',
  `blockQ` varchar(255) NOT NULL default '',
  `trylimit` varchar(255) NOT NULL default '',
  `orderQ` varchar(255) NOT NULL default '',
  `questlim` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`TID`)
);

#
# Structure for the `tracks` table :
#

DROP TABLE IF EXISTS `tracks`;

CREATE TABLE `tracks` (
  `trid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `id` varchar(255) default NULL,
  `volume` int(11) default NULL,
  `status` int(11) default NULL,
  `type` int(11) default NULL,
  `owner` varchar(255) default NULL,
  `totalcost` int(11) default NULL,
  `currency` int(11) default NULL,
  `description` text,
  `credits_free` int(10) unsigned NOT NULL default '0',
  `threshold` tinyint(3) unsigned NOT NULL default '0',
  `number_of_levels` tinyint(3) unsigned NOT NULL default '0',
  `year` int(11) NOT NULL default '0',
  `locked` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`trid`)
);

#
# Structure for the `tracks2course` table :
#

DROP TABLE IF EXISTS `tracks2course`;

CREATE TABLE `tracks2course` (
  `trid` int(11) default NULL,
  `cid` int(11) default NULL,
  `level` int(11) default NULL,
  `name` varchar(255) default NULL,
  `hours_samost` int(11) NOT NULL default '0',
  `hours_lecture` int(11) NOT NULL default '0',
  `hours_lab` int(11) NOT NULL default '0',
  `hours_practice` int(11) NOT NULL default '0',
  `hours_seminar` int(11) NOT NULL default '0',
  `hours_kurs` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `control` int(11) NOT NULL default '0',
  KEY `trid` (`trid`),
  KEY `cid` (`cid`),
  KEY `level` (`level`)
);

#
# Structure for the `tracks2mid` table :
#

DROP TABLE IF EXISTS `tracks2mid`;

CREATE TABLE `tracks2mid` (
  `trmid` int(10) unsigned NOT NULL auto_increment,
  `trid` int(11) default NULL,
  `mid` int(11) default NULL,
  `level` int(11) default NULL,
  `started` int(11) default NULL,
  `changed` int(11) default NULL,
  `stoped` int(11) default NULL,
  `status` int(11) default NULL,
  `sign` int(11) default NULL,
  `info` text,
  `do_next_level` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`trmid`),
  UNIQUE KEY `TRID_MID` (`trid`,`mid`),
  KEY `mid` (`mid`)
);

DROP TABLE IF EXISTS `tracks_levels`;

CREATE TABLE `tracks_levels` (
  `id` int(11) NOT NULL auto_increment,
  `trid` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `volume` float NOT NULL default '0',
  `date_begin` date NOT NULL,
  `date_end` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `trid` (`trid`),
  KEY `level` (`level`)
);

#
# Structure for the `training` table :
#

DROP TABLE IF EXISTS `training`;

CREATE TABLE `training` (
  `trid` int(11) NOT NULL auto_increment,
  `title` varchar(128) default NULL,
  `cid` int(11) default NULL,
  PRIMARY KEY  (`trid`),
  UNIQUE KEY `trid` (`trid`)
);

#
# Structure for the `training_run` table :
#

DROP TABLE IF EXISTS `training_run`;

CREATE TABLE `training_run` (
  `run_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `path` text,
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`run_id`)
);

DROP TABLE IF EXISTS `updates`;
CREATE TABLE `updates` (
  `update_id` int(11) NOT NULL default '0',
  `version` varchar(255) NOT NULL default '',
  `created` datetime default NULL,
  `created_by` int(11) NOT NULL default '0',
  `updated` datetime default NULL,
  `organization` varchar(255) NOT NULL default '',
  `description` text,
  `servers` text,
  PRIMARY KEY  (`update_id`)
);

#
# Structure for the `user_login_log` table :
#

DROP TABLE IF EXISTS `user_login_log`;

CREATE TABLE `user_login_log` (
  `login` varchar(255) default NULL,
  `date` datetime default NULL,
  `event_type` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `comments` varchar(255) default NULL,
  `ip` int(11) NOT NULL default '0',
  PRIMARY KEY  (`login`, `date`)
);


DROP TABLE IF EXISTS `schedulecount`;

CREATE TABLE `schedulecount` (
  `mid` int(11) NOT NULL default '0',
  `sheid` int(11) NOT NULL default '0',
  `qty` smallint(6) unsigned NOT NULL default '0',
  `last` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mid`,`sheid`)
);


DROP TABLE IF EXISTS `scorm_tracklog`;

CREATE TABLE `scorm_tracklog` (
  `trackID` int(10) unsigned NOT NULL auto_increment,
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `ModID` int(10) unsigned NOT NULL default '0',
  `McID` int(10) unsigned NOT NULL default '0',
  `lesson_id` int(10) unsigned NOT NULL default '0',
  `trackdata` blob NOT NULL,
  `stop` datetime NOT NULL default '0000-00-00 00:00:00',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `score` float NOT NULL default '0',
  `scoremax` float NOT NULL default '0',
  `scoremin` float NOT NULL default '0',
  `status` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`trackID`)
);

DROP TABLE IF EXISTS `scorm_report`;

CREATE TABLE `scorm_report` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `mid` int(10) unsigned NOT NULL default '0',
  `lesson_id` int(10) unsigned NOT NULL default '0',
  `report_data` blob NOT NULL,
  `updated` datetime default NULL,
  PRIMARY KEY  (`report_id`)
);


DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `sessid` int(10) unsigned NOT NULL auto_increment,
  `sesskey` varchar(32) NOT NULL default '',
  `mid` int(10) unsigned NOT NULL default '0',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `stop` datetime NOT NULL default '0000-00-00 00:00:00',
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
  PRIMARY KEY  (`sessid`)
);


DROP TABLE IF EXISTS `session_guest`;
CREATE TABLE `session_guest` (
  `session_guest_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `start` DATETIME NULL DEFAULT NULL ,
  `stop` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`session_guest_id`) 
);

DROP TABLE IF EXISTS `interesting_facts`;
CREATE TABLE  `interesting_facts` (
  `interesting_facts_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `title` TEXT NULL DEFAULT NULL ,
  `text` TEXT NULL DEFAULT NULL ,
  `status` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`interesting_facts_id`) 
);

DROP TABLE IF EXISTS `interview`;
CREATE TABLE `interview` (
  `interview_id` int(11) NOT NULL auto_increment,
  `title` text,
  `lesson_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `to_whom` int(11) default NULL,
  `type` int(11) default NULL,
  `question_id` varchar(250) default NULL,
  `message` text,
  `date` datetime default NULL,
  `interview_hash` int(11) default NULL,
  PRIMARY KEY  (`interview_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `user_id` (`user_id`),
  KEY `to_whom` (`to_whom`),  
  KEY `question_id` (`question_id`)
);

DROP TABLE IF EXISTS `interview_files`;
CREATE TABLE `interview_files` (
  `interview_id` int(11) NOT NULL,
  `file_id` varchar(45) NOT NULL,
  PRIMARY KEY  (`interview_id`,`file_id`)
);


DROP TABLE IF EXISTS `library`;
CREATE TABLE `library` (
  `bid` int(10) unsigned NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `cats` text,
  `mid` int(10) unsigned NOT NULL default '0',
  `uid` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  `publisher` varchar(255) NOT NULL default '',
  `publish_date` varchar(4) NOT NULL default '',
  `description` text NOT NULL,
  `keywords` text NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  `location` varchar(255) NOT NULL default '',
  `metadata` blob,
  `need_access_level` int(10) unsigned NOT NULL default '5',
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active_version` tinyint(3) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `is_package` tinyint(3) unsigned NOT NULL default '0',
  `quantity` int(11) unsigned NOT NULL default '0',
  `content` varchar(255) NOT NULL default '',
  `scorm_params` text NOT NULL,
  `pointId` int(11) NOT NULL default '0',
  `courses` varchar(255) default NULL,
  `lms` enum('0','1') NOT NULL default '0',
  `place` varchar(255) default NULL,
  `not_moderated` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`bid`),
  KEY `cid` (`cid`),
  KEY `need_access_level` (`need_access_level`),
  KEY `is_active_version` (`is_active_version`)
);


DROP TABLE IF EXISTS `library_assign`;

CREATE TABLE `library_assign` (
  `assid` int(10) unsigned NOT NULL auto_increment,
  `bid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `start` date NOT NULL default '0000-00-00',
  `stop` date NOT NULL default '0000-00-00',
  `closed` tinyint(3) unsigned NOT NULL default '0',
  `number` int(10) NOT NULL default '1',
  `type` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`assid`),
  KEY `bid` (`bid`),
  KEY `mid` (`mid`)
);


DROP TABLE IF EXISTS `library_categories`;

CREATE TABLE `library_categories` (
  `catid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `parent` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`catid`),
  KEY `parent` (`parent`)
);

DROP TABLE IF EXISTS `library_index`;

CREATE TABLE `library_index` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` int(10) unsigned NOT NULL default '0',
  `file` varchar(255) NOT NULL default '',
  `keywords` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `module` (`module`)
);

DROP TABLE IF EXISTS `laws`;

CREATE TABLE `laws` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(11) unsigned NOT NULL default '0',
  `categories` text NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `initiator` varchar(255) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  `annotation` text NOT NULL,
  `type` int(10) unsigned NOT NULL default '0',
  `region` int(10) unsigned NOT NULL default '0',
  `area_of_application` varchar(255) NOT NULL default '',
  `create_date` date NOT NULL default '0000-00-00',
  `expire` varchar(255) NOT NULL default '',
  `modify_date` date NOT NULL default '0000-00-00',
  `edit_reason` text NOT NULL,
  `current_version` tinyint(3) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL default '',
  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `uploaded_by` int(11) unsigned NOT NULL default '0',
  `access_level` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `laws_categories`;

CREATE TABLE `laws_categories` (
  `catid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `parent` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`catid`)
);

DROP TABLE IF EXISTS `laws_index`;
CREATE TABLE `laws_index` (
  `id` int(10) unsigned NOT NULL default '0',
  `word` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`,`word`)
);

DROP TABLE IF EXISTS `laws_index_words`;
CREATE TABLE `laws_index_words` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `word` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `departments_soids`;
CREATE TABLE `departments_soids` (
  `did` int(10) unsigned NOT NULL default '0',
  `soid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`did`,`soid`),
  KEY `did` (`did`),
  KEY `soid` (`soid`)
);

DROP TABLE IF EXISTS `departments_specs`;
CREATE TABLE `departments_specs` (
  `did` int(11) NOT NULL,
  `spid` int(11) NOT NULL,
  PRIMARY KEY  (`did`,`spid`)
);

DROP TABLE IF EXISTS `courses_marks`;
CREATE TABLE `courses_marks` (
  `cid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `mark` varchar(255) NOT NULL default '-1',
  `alias` varchar(255) NOT NULL default '',
  `confirmed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`cid`,`mid`),
  KEY `cid` (`cid`),
  KEY `mid` (`mid`)
);

DROP TABLE IF EXISTS `departments_groups`;
CREATE TABLE `departments_groups` (
  `did` int(10) unsigned NOT NULL default '0',
  `gid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`did`,`gid`),
  KEY `did` (`did`),
  KEY `gid` (`gid`)
);

DROP TABLE IF EXISTS `chain`;
CREATE TABLE `chain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `chain_item`;
CREATE TABLE `chain_item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `chain` int(10) unsigned NOT NULL default '0',
  `item` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `place` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `chain_agreement`;
CREATE TABLE `chain_agreement` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `subject` int(10) unsigned NOT NULL default '0',
  `object` int(10) unsigned NOT NULL default '0',
  `place` int(10) unsigned NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `eventtools_weight`;
CREATE TABLE `eventtools_weight` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `event` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `weight` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `departments_tracks`;
CREATE TABLE `departments_tracks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `did` int(10) unsigned NOT NULL default '0',
  `track` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `did` (`did`),
  KEY `track` (`track`)
);

DROP TABLE IF EXISTS `mod_attempts`;
CREATE TABLE `mod_attempts` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ModID` INT UNSIGNED NOT NULL ,
`mid` INT UNSIGNED NOT NULL ,
`start` DATETIME NOT NULL
);

DROP TABLE IF EXISTS `departments_courses`;
CREATE TABLE `departments_courses` (
`did` INT UNSIGNED NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `did` , `cid` ),
  KEY `cid` (`cid`),
  KEY `did` (`did`)
);

DROP TABLE IF EXISTS `schedule_locations`;
CREATE TABLE `schedule_locations` (
  `sheid` int(10) unsigned NOT NULL default '0',
  `location` int(10) unsigned NOT NULL default '0',
  `teacher` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`sheid`,`location`)
);

DROP TABLE IF EXISTS `posts3_mids`;
CREATE TABLE `posts3_mids` (
`postid` INT UNSIGNED NOT NULL ,
`mid` INT UNSIGNED NOT NULL ,
PRIMARY KEY ( `postid` , `mid` )
);

DROP TABLE IF EXISTS `hacp_debug`;
CREATE TABLE `hacp_debug` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `message` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `direction` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
);


DROP TABLE IF EXISTS `help`;
CREATE TABLE `help` (
  `help_id` int(10) unsigned NOT NULL auto_increment,
  `role` varchar(255) NOT NULL default '',
  `module` varchar(255),
  `app_module` varchar(25),
  `controller` varchar(255),
  `action` varchar(255),
  `link_subject` tinyint(3) unsigned NOT NULL default '0',
  `is_active_version` tinyint(3),
  `link` varchar(255),
  `title` varchar(255),
  `text` text NOT NULL,
  `moderated` tinyint(3) unsigned NOT NULL default '0',
  `lang` varchar(3) NOT NULL default '',
  PRIMARY KEY  (`help_id`)
);

DROP TABLE IF EXISTS `holidays`;
CREATE TABLE `holidays` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `date` DATE NOT NULL,
  `type` TINYINT(4) NOT NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `htmlpage`;
CREATE TABLE `htmlpage` (
  `page_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned,
  `name` varchar(255) NOT NULL default '',
  `ordr` int(11) NOT NULL default '10',  
  `text` text NOT NULL,
  `url` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`page_id`)
);

DROP TABLE IF EXISTS `htmlpage_groups`;
CREATE TABLE `htmlpage_groups` (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `lft` int(10) unsigned,
  `rgt` int(10) unsigned,
  `level` int(10) unsigned,
  `name` varchar(255) NOT NULL default '',
  `ordr` int(11) NOT NULL default '10',  
  `role` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`group_id`)
);

DROP TABLE IF EXISTS `competence_roles`;
CREATE TABLE `competence_roles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `formula` int(10) unsigned NOT NULL default '0',
  `dynamic` tinyint(3) unsigned NOT NULL default '0',
  `courses` tinyint(4) NOT NULL default '0',
  `specialization` int(3) NOT NULL default '0',
  `description` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `formula` (`formula`),
  KEY `courses` (`courses`),
  KEY `specialization` (`specialization`)
);

DROP TABLE IF EXISTS `competence_roles_competences`;
CREATE TABLE `competence_roles_competences` (
  `role` int(10) unsigned NOT NULL default '0',
  `competence` int(10) unsigned NOT NULL default '0',
  `threshold` double NOT NULL default '0',
  `task` int(11) NOT NULL default '0',
  PRIMARY KEY  (`role`,`competence`),
  KEY `role` (`role`),
  KEY `competence` (`competence`),
  KEY `task` (`task`)
);

DROP TABLE IF EXISTS `competence_roles_specializations`;
CREATE TABLE `competence_roles_specializations` (
  `role` int(10) NOT NULL default '0',
  `competence` int(10) NOT NULL default '0',
  `specialization` int(10) NOT NULL default '0',
  PRIMARY KEY  (`role`,`competence`,`specialization`),
  KEY `role` (`role`),
  KEY `competence` (`competence`),
  KEY `specialization` (`specialization`)
);

DROP TABLE IF EXISTS `structure_of_organ_roles`;
CREATE TABLE `structure_of_organ_roles` (
  `soid` INT UNSIGNED NOT NULL ,
  `role` INT UNSIGNED NOT NULL ,
  PRIMARY KEY  (`soid`,`role`),
  KEY `soid` (`soid`),
  KEY `role` (`role`)
);

DROP TABLE IF EXISTS `polls`;
CREATE TABLE `polls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` text NULL,
  `begin` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `event` int(10) unsigned NOT NULL default '0',
  `formula` int(10) unsigned NOT NULL default '0',
  `data` text,
  `deleted` tinyint(3) unsigned NOT NULL default '0',
  `sequence` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ;

DROP TABLE IF EXISTS `polls_people`;
CREATE TABLE `polls_people` (
  `poll` INT UNSIGNED NOT NULL ,
  `mid` INT UNSIGNED NOT NULL ,
  `soid` INT UNSIGNED NOT NULL,
  `role` INT UNSIGNED NOT NULL ,
  `kod` VARCHAR( 100 ) NOT NULL ,
  PRIMARY KEY  (`poll`,`mid`,`soid`,`role`,`kod`),
  KEY `poll` (`poll`),
  KEY `mid` (`mid`),
  KEY `soid` (`soid`),
  KEY `role` (`role`)
) ;

DROP TABLE IF EXISTS `glossary`;
CREATE TABLE `glossary` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 255 ) NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
`description` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cid` (`cid`),
  KEY `name` (`name`)
) ;

DROP TABLE IF EXISTS `crontask`;
CREATE TABLE `crontask` (
  `crontask_id` varchar(255) NOT NULL default '',
  `crontask_runtime` int(11) unsigned default NULL
) ;

DROP TABLE IF EXISTS `courses_groups`;

CREATE TABLE `courses_groups` (
  `did` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `mid` int(10) unsigned NOT NULL default '0',
  `info` blob,
  `color` varchar(255) default NULL,
  `owner_did` int(11) unsigned NOT NULL default '0',
  `not_in` tinyint(3) unsigned NOT NULL default '0',
  `file_image` varchar(255) default NULL,
  PRIMARY KEY  (`did`),
  KEY `mid` (`mid`)
);

DROP TABLE IF EXISTS `library_cms_index`;

CREATE TABLE `library_cms_index` (
  `id` int(10) unsigned NOT NULL default '0',
  `word` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`,`word`)
);

DROP TABLE IF EXISTS `library_cms_index_words`;

CREATE TABLE `library_cms_index_words` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `word` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `polls_criteries`;

CREATE TABLE `polls_criteries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` text NOT NULL default '',
  `poll` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `soid` int(10) unsigned NOT NULL default '0',
  `role` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `poll` (`poll`),
  KEY `mid` (`mid`),
  KEY `soid` (`soid`),
  KEY `role` (`role`)
);

DROP TABLE IF EXISTS `managers`;

CREATE TABLE `managers` (
  `id` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mid` (`mid`)
);


DROP TABLE IF EXISTS `sequence_current`;
CREATE TABLE `sequence_current` (
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `current` varchar(255) NOT NULL default '',
  `subject_id` int(10) NOT NULL default '0',
  `lesson_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`,`subject_id`,`lesson_id`)
);

DROP TABLE IF EXISTS `sequence_history`;
CREATE TABLE `sequence_history` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`mid` INT UNSIGNED NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
`item` varchar(255) NOT NULL default '',
`date` DATETIME NOT NULL ,
`subject_id` int(10) NOT NULL default '0',
`lesson_id` int(10) NOT NULL default '0'
) ;

DROP TABLE IF EXISTS `personallog`;
CREATE TABLE `personallog` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`mid` INT UNSIGNED NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
`item` INT UNSIGNED NOT NULL ,
`session` VARCHAR( 255 ) NOT NULL ,
`datetime` DATETIME NOT NULL
) ;

DROP TABLE IF EXISTS `courses_links`;
CREATE TABLE `courses_links` (
  `cid` int(11) NOT NULL default '0',
  `with` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cid`,`with`)
);


DROP TABLE IF EXISTS `developers`;
CREATE TABLE `developers` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
);

DROP TABLE IF EXISTS `methodologist`;
CREATE TABLE `methodologist` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
);

DROP TABLE IF EXISTS `specializations`;

CREATE TABLE `specializations` (
  `spid` int(10) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `discription` text NOT NULL,
  PRIMARY KEY  (`spid`)
);

DROP TABLE IF EXISTS `reviewers`;
CREATE TABLE `reviewers` (
  `mid` int( 11 ) NOT NULL default '0',
  `cid` int( 11 ) NOT NULL default '0',
  PRIMARY KEY ( `mid` , `cid` )
) ;

DROP TABLE IF EXISTS `polls_state`;
CREATE TABLE `polls_state` (
  `pid` int(11),
  `mid` int(11),
  `state` int(11),
  `modified` datetime NULL,
  `inactive` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `pid_mid_state` (`pid`,`mid`,`state`),
  KEY `pid` (`pid`),
  KEY `mid` (`mid`),
  KEY `state` (`state`),
  KEY `inactive` (`inactive`)
) ;

DROP TABLE IF EXISTS `states`;
CREATE TABLE `states` (
  `scope` varchar(20) NULL,
  `scope_id` int(11) NULL,
  `state` int(11) NULL,
  `title` varchar(64) NULL,
  KEY `scope` (`scope`),
  KEY `scope_id` (`scope_id`),
  KEY `state` (`state`)
) ;

DROP TABLE IF EXISTS `report_templates`;
CREATE TABLE `report_templates` (
  `rtid` int(11) NOT NULL auto_increment,
  `template_name` varchar(255) NULL,
  `report_name` varchar(255) NULL,
  `created` int(11) NOT NULL default '0',
  `creator` int(11) NULL,
  `edited` int(11) NULL,
  `editor` int(11) NULL,
  `template` text,
  PRIMARY KEY  (`rtid`)
);

DROP TABLE IF EXISTS `structure_of_organ_courses`;
CREATE TABLE `structure_of_organ_courses` (
  `soid` int(10) unsigned NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`soid`,`cid`)
);

DROP TABLE IF EXISTS `supervisors`;

CREATE TABLE `supervisors` (
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
);

DROP TABLE IF EXISTS `employee`;

CREATE TABLE `employee` (
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
);


DROP TABLE IF EXISTS `providers`;
CREATE TABLE `providers` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `contacts` text,
  `description` text,
  PRIMARY KEY  (`id`)
);


DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `contacts` text,
  `description` text,
  PRIMARY KEY  (`supplier_id`)
);


DROP TABLE IF EXISTS `webinar_answers`;
CREATE TABLE `webinar_answers` (
  `aid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL,
  `text` varchar(255) default NULL,
  PRIMARY KEY  (`aid`)
);

DROP TABLE IF EXISTS `webinars`;
CREATE TABLE `webinars` (
  `webinar_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `create_date` datetime NULL,
  `subject_id` int(11) NULL,
  PRIMARY KEY  (`webinar_id`)
);


DROP TABLE IF EXISTS `webinar_chat`;
CREATE TABLE `webinar_chat` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `message` varchar(255) NOT NULL default '',
    `datetime` datetime NULL,
    `userId` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `webinar_files`;
CREATE TABLE `webinar_files` (
    `webinar_id` int(11) NOT NULL default '0',
    `file_id` int(11) NOT NULL default '0',
    `num` int(11) NOT NULL default '0'
);


DROP TABLE IF EXISTS `webinar_history`;
CREATE TABLE `webinar_history` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `userId` int(11) NOT NULL default '0',
    `action` varchar(255) NOT NULL default '',
    `item` varchar(255) NOT NULL default '',
    `datetime` datetime NULL,
    PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `webinar_plan`;
CREATE TABLE `webinar_plan` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `href` varchar(255) NOT NULL default '',
    `title` varchar(255) NOT NULL default '',
    `bid` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `webinar_plan_current`;
CREATE TABLE `webinar_plan_current` (
    `pointId` int(11) NOT NULL,
    `currentItem` int(11) NOT NULL default '0',
    PRIMARY KEY  (`pointId`)
);

DROP TABLE IF EXISTS `webinar_questions`;
CREATE TABLE `webinar_questions` (
  `qid` int(11) NOT NULL auto_increment,
  `text` varchar(255) default NULL,
  `type` tinyint(1) default NULL,
  `point_id` int(11) default NULL,
  `is_voted` tinyint(1) default NULL,
  PRIMARY KEY  (`qid`),
  UNIQUE KEY `text` (`text`,`point_id`)
);

DROP TABLE IF EXISTS `webinar_users`;
CREATE TABLE `webinar_users` (
    `pointId` int(11) NOT NULL,
    `userId` int(11) NOT NULL,
    `last` datetime NULL,
    PRIMARY KEY  (`pointId`,`userId`)
);

DROP TABLE IF EXISTS `webinar_votes`;
CREATE TABLE `webinar_votes` (
  `vid` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `qid` int(11) default NULL,
  `aid` int(11) default NULL,
  PRIMARY KEY  (`vid`)
);

DROP TABLE IF EXISTS `webinar_whiteboard`;
CREATE TABLE `webinar_whiteboard` (
  `actionId` int(11) NOT NULL auto_increment,
  `pointId` int(11) default NULL,
  `userId` int(11) default NULL,
  `actionType` varchar(255) default NULL,
  `datetime` datetime default NULL,
  `color` int(11) default NULL,
  `tool` int(11) default NULL,
  `text` text,
  `width` int(11) default NULL,
  `height` int(11) default NULL,
  PRIMARY KEY  (`actionId`)
);

DROP TABLE IF EXISTS `webinar_whiteboard_points`;
CREATE TABLE `webinar_whiteboard_points` (
  `pointId` int(11) NOT NULL auto_increment,
  `actionId` int(11) default NULL,
  `x` int(11) default NULL,
  `y` int(11) default NULL,
  `type` int(11) default NULL,
  PRIMARY KEY  (`pointId`)
);

DROP TABLE IF EXISTS `webinar_records`;
CREATE TABLE `webinar_records` (
  `id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NOT NULL default '0',
  `webinar_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `wiki_articles`;
CREATE TABLE `wiki_articles`
(
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` DATETIME,
  `title`   VARCHAR(255) NOT NULL,
  `subject_name` VARCHAR(255),
  `subject_id` INT(10) UNSIGNED NOT NULL,
  `lesson_id` INT(10) UNSIGNED NULL default NULL,
  `changed` DATETIME,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `wiki_archive`;
CREATE TABLE `wiki_archive`
(
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id`    INT(10) UNSIGNED NOT NULL,
  `created` DATETIME,
  `author`  INT(10) UNSIGNED NOT NULL,
  `body`    LONGTEXT,
  PRIMARY KEY (`id`)
);


DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `id` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `teacher` int(11) NOT NULL,
  `rating` double NOT NULL,
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL,
  `module` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `review` text,
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `exam_types`;
CREATE TABLE `exam_types` (
  `etid` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255),
  PRIMARY KEY (`etid`)
);

DROP TABLE IF EXISTS `video`;
CREATE TABLE `video` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `created` int(11) unsigned NOT NULL default '0',
  `title` VARCHAR(255) NOT NULL default '',
  `main_video` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `metadata_groups`;
CREATE TABLE `metadata_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `roles` text,
  `locked` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`group_id`)
);

DROP TABLE IF EXISTS `metadata_items`;
CREATE TABLE `metadata_items` (
  `item_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` tinyint(4) NOT NULL default '0',
  `value` text,
  `public` enum('0','1') NOT NULL default '0',
  `required` enum('0','1') NOT NULL default '0',
  `order` int(11) NOT NULL default '0',
  `editable` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`item_id`),
  KEY `group_id` (`group_id`)
);

DROP TABLE IF EXISTS `tracks2group`;
CREATE TABLE `tracks2group` (
  `id` int(11) NOT NULL auto_increment,
  `trid` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `updated` datetime default NULL,
  PRIMARY KEY  (`id`)
);
DROP TABLE IF EXISTS `qwerty`;

DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `resource_id` int(10) unsigned NOT NULL auto_increment,
  `resource_id_external` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NULL,
  `volume` varchar(255) NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `type` int(11) NOT NULL default '0',
  `filetype` int(11) NOT NULL default '0',
  `description` text,
  `content` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `services` int(11) default '0',
  `subject_id` int(11) default '0',
  `status` int(11) default '0',
  `location` int(11) default '0',
  `db_id` varchar(255) NOT NULL default '',
  `test_id` int(11) default '0',
  `activity_id` int(11) default '0',
  `activity_type` int(11) default '0',
  `related_resources` text,
  `parent_id` int(11) default '0',
  `parent_revision_id` int(11) default '0',
  PRIMARY KEY  (`resource_id`)
);

DROP TABLE IF EXISTS `scales`;
CREATE TABLE `scales` (
  `scale_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text,
  `type` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`scale_id`)
);

DROP TABLE IF EXISTS `scale_values`;
CREATE TABLE `scale_values` (
  `value_id` int(11) NOT NULL auto_increment,
  `scale_id` int(11) NOT NULL,
  `value` int(11) NOT NULL default '0',
  `text` varchar(255) default NULL,
  `description` text,  
  PRIMARY KEY  (`value_id`)
);

DROP TABLE IF EXISTS `resource_revisions`;
CREATE TABLE `resource_revisions` (
  `revision_id` int(10) unsigned NOT NULL auto_increment,
  `resource_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NULL,
  `volume` varchar(255) NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filetype` int(11) NOT NULL default '0',
  `content` text,
  `updated` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`revision_id`)
);


DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `subid` int(11) NOT NULL auto_increment,
  `external_id` varchar(45) default NULL,
  `code` varchar(255) default NULL,
  `name` varchar(255) default NULL,
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
  `price` int(11) default NULL,
  `price_currency` varchar(25) default NULL,
  `plan_users` int(11) default NULL,
  `services` int(11) default '0',
  `period` tinyint(4) default '0',
  `period_restriction_type` tinyint(4) NOT NULL default '0',
  `created` datetime default NULL,
  `last_updated` datetime default NULL,
  `access_mode` int(11) default '0',
  `access_elements` int(11) default NULL,
  `mode_free_limit` int(11) default NULL,
  `auto_done` int(11) default '0',
  `base` int(11) default '0',
  `base_id` int(11) default '0',
  `base_color` varchar(45) default NULL,
  `claimant_process_id` int(11) default '0',
  `state` tinyint(4) default '0',
  `default_uri` varchar(255) default NULL,
  `scale_id` int(11) default '0',
  `auto_mark` tinyint(4) default '0',
  `auto_graduate` tinyint(4) default '0',
  `formula_id` int(11) default NULL,
  `threshold` int(11) default NULL,
  PRIMARY KEY  (`subid`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `type` (`type`),
  KEY `reg_type` (`reg_type`)
);

DROP TABLE IF EXISTS `subjects_courses`;
CREATE TABLE `subjects_courses` (
  `subject_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`course_id`)
);

DROP TABLE IF EXISTS `subjects_tests`;
CREATE TABLE `subjects_tests` (
  `subject_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`test_id`)
);

DROP TABLE IF EXISTS `subjects_exercises`;
CREATE TABLE `subjects_exercises` (
  `subject_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`exercise_id`)
);

DROP TABLE IF EXISTS `subjects_resources`;
CREATE TABLE `subjects_resources` (
  `subject_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`resource_id`)
);

DROP TABLE IF EXISTS `subjects_quizzes`;
CREATE TABLE `subjects_quizzes` (
  `subject_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`quiz_id`)
);

DROP TABLE IF EXISTS `subjects_tasks`;
CREATE TABLE `subjects_tasks` (
  `subject_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`task_id`)
);

DROP TABLE IF EXISTS `state_of_process`;
CREATE TABLE `state_of_process` (
  `state_of_process_id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,
  `process_id` int(11) default NULL,
  `process_type` int(11) NOT NULL,
  `current_state` varchar(255) default NULL,
  `passed_states` text,
  `status` int(11) default NULL,
  `params` text,
  PRIMARY KEY  (`state_of_process_id`),
  KEY `item_id` (`item_id`),
  KEY `process_id` (`process_id`)
);

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions`  ( 
    `subscription_id` int(11) AUTO_INCREMENT NOT NULL,
    `user_id`         int(10) UNSIGNED NOT NULL DEFAULT '0',
    `channel_id`      int(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY(`subscription_id`)
);

DROP TABLE IF EXISTS `subscription_entries`;
CREATE TABLE `subscription_entries`  ( 
    `entry_id`    int(11) AUTO_INCREMENT NOT NULL,
    `channel_id`  int(10) UNSIGNED NOT NULL,
    `title`       varchar(255) NULL,
    `link`        varchar(255) NULL,
    `description` text NULL,
    `content`     text NULL,
    `author`      int(10) UNSIGNED NOT NULL,
    PRIMARY KEY(`entry_id`)
);

DROP TABLE IF EXISTS `subscription_channels`;
CREATE TABLE `subscription_channels`  ( 
    `channel_id`    int(11) AUTO_INCREMENT NOT NULL,
    `activity_name` varchar(45) NOT NULL,
    `subject_name`  varchar(45) NULL,
    `subject_id`    int(11) NOT NULL DEFAULT '0',
    `lesson_id`     int(11) NOT NULL DEFAULT '0',
    `title`         varchar(255) NULL,
    `description`   text NULL,
    `link`          varchar(255) NULL,
    PRIMARY KEY(`channel_id`)
);

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `from` int(11) NOT NULL default '0',
  `to` int(11) NOT NULL default '0',
  `subject` varchar(255) default NULL,
  `subject_id` int(11) unsigned default NULL,
  `message` text,
  `created` datetime default NULL,
  PRIMARY KEY  (`message_id`),
  KEY `from` (`from`),
  KEY `to` (`to`),
  KEY `subject_id` (`subject_id`)
);

DROP TABLE IF EXISTS `interface`;
CREATE TABLE `interface` (
  `interface_id` int(11) NOT NULL auto_increment,
  `role` varchar(255) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `block` varchar(255) NOT NULL default '',
  `necessity` int(11) default '0',
  `x` int(11) NOT NULL default '1',
  `y` int(11) NOT NULL default '1',
  `param_id` varchar(255) default NULL,
  PRIMARY KEY  (`interface_id`),
  KEY `role` (`role`),
  KEY `user_id` (`user_id`)
);

DROP TABLE IF EXISTS `captcha`;
CREATE TABLE `captcha` (
  `login` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL default '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`login`)
);

DROP TABLE IF EXISTS `videochat_users`;
CREATE TABLE `videochat_users` (
    `pointId` varchar(255) NOT NULL,
    `userId` int(11) NOT NULL,
    `last` datetime NULL,
    PRIMARY KEY  (`pointId`,`userId`)
);

DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
    `id` int(11) NOT NULL auto_increment,
    `event` varchar(255) NOT NULL,
    `receiver` int(11) NOT NULL,
    `title` varchar(255) NULL,
    `message` text NULL,
    `type` int(11) NOT NULL,
    `enabled` int(11) NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id`)
);


DROP TABLE IF EXISTS `storage_filesystem`;
CREATE TABLE `storage_filesystem` (
    id           int(10) UNSIGNED NOT NULL auto_increment,
    parent_id    int(10) UNSIGNED,
    subject_id   int(10) UNSIGNED NOT NULL,
    subject_name varchar(255),
    name         varchar(255),
    alias        varchar(255),
    is_file      tinyint(1) NOT NULL,
    description  varchar(255),
    user_id      int(10) UNSIGNED NULL DEFAULT NULL,
    created      DATETIME,
    changed      DATETIME,
    PRIMARY KEY (id),
	KEY parent_id (parent_id),
	KEY subject_id (subject_id)
);

DROP TABLE IF EXISTS `chat_channels`;
CREATE TABLE `chat_channels`
(
  id           int(10) UNSIGNED NOT NULL auto_increment,
  subject_name varchar(255),
  subject_id   int(10) UNSIGNED NOT NULL,
  lesson_id    int(10) UNSIGNED NULL default NULL,
  name         varchar(255) NOT NULL,
  start_date   DATE,
  end_date     DATE,
  show_history tinyint(1) UNSIGNED DEFAULT 1 NOT NULL,
  start_time   int(4) UNSIGNED,
  end_time     int(4) UNSIGNED,
  is_general   tinyint(1) UNSIGNED DEFAULT 0 NOT NULL,
  PRIMARY KEY (id),
  KEY subject_id (subject_id),
  KEY lesson_id (lesson_id)
);

DROP TABLE IF EXISTS `chat_history`;
CREATE TABLE `chat_history`
(
  id           int(10) UNSIGNED NOT NULL auto_increment,
  channel_id           int(10) UNSIGNED NOT NULL,
  sender           int(10) UNSIGNED NOT NULL,
  receiver           int(10) UNSIGNED NULL default NULL,
  message text,
  created   DATETIME,
  PRIMARY KEY (id),
  KEY channel_id (channel_id)
);

DROP TABLE IF EXISTS `chat_ref_users`;
CREATE TABLE `chat_ref_users`
(
  channel_id           int(10) UNSIGNED NOT NULL,
  user_id           int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (channel_id,user_id)
);

DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NOT NULL,
  `name` varchar(255),
  `order` tinyint(4) NULL,
  PRIMARY KEY  (`section_id`)
);

DROP TABLE IF EXISTS `reports_roles`;
CREATE TABLE `reports_roles`
(
  `role` varchar(100) NOT NULL default '',
  `report_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`role`, `report_id`)
);

DROP TABLE IF EXISTS `at_hh_regions`;
CREATE TABLE `at_hh_regions` (
  `id` int(11) unsigned NOT NULL,
  `parent` int(11) unsigned default NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
)
;

#
# ВНИМАНИЕ! НЕ ЗАБЫТЬ ИЗМЕНИТЬ ЗНАЧЕНИЯ VERSION И BUILD
#
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('version', '4.0');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('build', '2011-04-01');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('regnumber', '');

#
# Data for the `alt_mark` table  (LIMIT 0,500)
#

INSERT INTO `alt_mark` (`int`, `char`) VALUES
(-2,'+'),
(-3,'-');

#
# Data for the `Teachers` table  (LIMIT 0,500)
#

INSERT INTO `Teachers` (`PID`, `MID`, `CID`) VALUES
(1,1,1);

#
# Data for the `Students` table  (LIMIT 0,500)
#

INSERT INTO `Students` (`SID`, `MID`, `CID`, `cgid`, `Registered`) VALUES
(1,1,1,0,1);

INSERT INTO `managers` (mid) VALUES (1);

INSERT INTO `developers` (mid, cid) VALUES (1, 0);

#
# Data for the `Courses` table  (LIMIT 0,500)
#

INSERT INTO `Courses` (`CID`, `Title`, `Description`, `TypeDes`, `CD`, `cBegin`, `cEnd`, `Fee`, `valuta`, `Status`, `createby`, `createdate`, `longtime`, `did`) VALUES
(1,'Пример электронного курса','block=simple~name=description%END%type=fckeditor%END%title=%END%value=Пример описания курса%END%sub=%END%~[~~]',0,'','2011-01-01','2021-01-01',0,0,'2','elearn@hypermethod.com','2011-01-01',120,0);

INSERT INTO `subjects` (`subid`, `name`, `reg_type`, `begin`, `end`) VALUES (1, 'Пример учебного курса', 0, '2011-01-01', '2021-01-01');

INSERT INTO `organizations` (`title`, `cid`, `prev_ref`, `level`) VALUES ('<пустой элемент>','1','-1', '0');

#
# Data for the `admins` table  (LIMIT 0,500)
#

INSERT INTO `admins` (`AID`, `MID`) VALUES
(1,1);

#
# Data for the `deans` table  (LIMIT 0,500)
#

INSERT INTO `deans` (`DID`, `MID`) VALUES
(1, 1);
#
# Data for the `People` table  (LIMIT 0,500)
#

INSERT INTO `People` (`MID`, `LastName`, `FirstName`, `Password`, `Login`) VALUES (1, 'Администратор', 'Администратор', PASSWORD('pass'), 'admin');

#
# Data for the `OPTIONS` table  (LIMIT 0,500)
#

INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('dekanName', 'Учебная администрация');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('dekanEMail', 'some@e.mail');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('max_invalid_login', '0');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('chat_server_port', '50011');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('drawboard_port', '50012');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('import_ims_compatible', '1');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('question_edit_additional_rows', '3');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('answers_local_log_full', '1');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('course_description_format', 'simple');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('disable_copy_material', '0');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('enable_check_session_exist', '0');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('enable_eauthor_course_navigation', '0');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('enable_forum_richtext', '1');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('regform_email_required', '1');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('regform_items', 'a:1:{i:0;s:8:"add_info";}');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('grid_rows_per_page', '25');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('skin', 'redmond');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('headStructureUnitName', 'Организационная структура');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('edo_subdivision_root_name', 'Учебная структура');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('default_currency', 'RUB');


DROP TABLE IF EXISTS `213871298721`;


INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (1, 'Создание новой учетной записи пользователя', 'Вы зарегистрированы  в ИСДО', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (2, 'Назначение роли пользователю', 'Вам назначена роль [ROLE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (3, 'Назначение на учебный курс (в процессе обучения)', 'Вы назначены на обучение по курсу [COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (4, 'Назначение на учебный модуль (в процессе разработки учебного модуля)', 'Вы назначены в группу  разработчиков учебного модуля [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (5, 'Определенное количество дней до окончания обучения по курсу ', 'Заканчивается обучение по курсу [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (6, 'Перевод в пользователя прошедшие обучение по курсу', 'Вам назначена роль [ROLE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (7, 'Подача заявки на обучение по курсу', 'Новая заявка на обучение по курсу [URL_COURSE]', 1, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (8, 'Подача заявки на обучение по курсу', 'Ваша заявка зарегистрирована', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (9, 'Рассмотрение заявки на обучение по курсу: одобрение ', 'Ваша заявка на обучение по курсу [URL_COURSE] одобрена', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (10, 'Рассмотрение заявки на обучение по курсу: отклонение', 'Ваша заявка на обучение по курсу [URL_COURSE] отклонена', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (11, 'Смена пароля пользователя', 'Подтверждение смены пароля ', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (12, 'Новое личное сообщение', '[SUBJECT]', 0, '[TEXT]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (13, 'Обновление источника подписки', 'Подписка на источник [SOURCE]', 0, '[TEXT]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (14,'Опрос слушателей', 'Опрос слушателей по курсу [URL_COURSE]', 0, 'Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (15,'Опрос тьюторов', 'Опрос тьюторов по курсу [URL_COURSE]', 0, 'Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (16,'Опрос руководителей', 'Опрос руководителей по курсу [URL_COURSE]', 0, 'Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n- ФИО сотрудников, прошедших обучение: [SLAVES]\n');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (17,'Шаблон сгруппированых сообщений', 'Тема этого шаблона будет заменена темой шаблона события', 0, 'Сообщение этого шаблона будет заменено объединенными сообщениями шаблона события');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (18,'Новое сообщение на форуме', 'Новое сообщение от пользователя [MESSAGE_USER_NAME]', 0, 'В теме "[SECTION_NAME]" форума "[FORUM_NAME]" оставлен новый комментарий. [MESSAGE_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (19,'Новый скрытый ответ на форуме', 'Вы получили скрытый комментарий от пользователя [MESSAGE_USER_NAME]', 0, 'На ваше сообщение в теме "[SECTION_NAME]" форума "[FORUM_NAME]" оставлен новый скрытый комментарий. [MESSAGE_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (20,'Оценка сообщения на форуме', 'Вашему сообщению на форуме выставлена оценка', 0, 'В теме "[SECTION_NAME]" форума "[FORUM_NAME]" выставлена оценка на ваш комментарий. [MESSAGE_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (21,'Подтверждение email', 'Подтвердите email', 0, 'Для завершения регистрации и необходимо подтвердить Ваш email. Перейдите по ссылке: [EMAIL_CONFIRM_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (22,'Назначение сессии оценки персонала', 'Вы принимаете участие в сессии оценки персонала с [SESSION_BEGIN] по [SESSION_END]', 0, 'Вам необходимо авторизоваться на портале и пройти анкетирование');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (23,'Учётная запись разблокирована', 'Учётная запись разблокирована', 0, 'Ваша учетная запись была разблокирована. Для входа на портал, перейдите по ссылке: [URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (104,'Уведомление о назначенном мероприятии', 'Вакансия "[VACANCY]": назначено мероприятие', 0, '<p>Уважаемый [NAME]!</p><p>Предлагаем Вам пройти оценочное мероприятие [EVENT].</p><p>Для этого перейдите по ссылке: [URL]; для входа в систему используйте:</p><ul><li>логин - [LOGIN]</li><li>пароль - [NEW_PASSWORD] </li></ul>');

INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (1, 'ГиперМетод IBS', NULL, NULL, NULL);
INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (2, 'SkillSoft', NULL, NULL, NULL);

INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (1, 'Значения от 0 до 100', 'Любые значения в диапазоне от 0 до 100', 1);
INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (2, '2 состояния', 'Пройдено / Не пройдено', 2);
INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (3, '3 состояния', 'Пройдено успешно / Пройдено неуспешно / Не пройдено', 3);

INSERT INTO `classifiers_types` (`type_id`,`name`,`link_types`) VALUES (20,'Специальности','5');
INSERT INTO `classifiers_types` (`type_id`,`name`,`link_types`) VALUES (21,'ВУЗы','5');


### Итоговое мероприятие по адаптации ###

INSERT INTO `quest_clusters` (`cluster_id`, `quest_id`, `name`) VALUES 
  (1,1,'Подведение итогов'),
  (2,1,'Результаты прохождения испытательного срока и адаптации');

INSERT INTO `quest_question_quests` (`question_id`, `quest_id`) VALUES 
  (1,1),
  (2,1),
  (4,1),
  (5,1);

INSERT INTO `quest_question_variants` (`question_variant_id`, `question_id`, `variant`, `shorttext`, `file_id`, `is_correct`, `category_id`, `weight`) VALUES 
  (5,1,'Частично прошёл мероприятия, не адаптировался к должности и условиям труда (оценка неудовлетворительно)',NULL,NULL,0,0,0),
  (6,1,'Прошёл не все мероприятия, слабо адаптировался к должности и условиям труда (оценка удовлетворительно)',NULL,NULL,0,0,0),
  (7,1,'Прошёл все мероприятия, адаптировался к должности и условиям труда (оценка хорошо)',NULL,NULL,0,0,0),
  (8,1,'Успешно прошёл все мероприятия, хорошо адаптировался к должности и условиям труда (оценка отлично)',NULL,NULL,0,0,0),
  (11,4,'Программа адаптации пройдена полностью и успешно',NULL,NULL,0,0,0),
  (12,4,'Программа адаптации не пройдена',NULL,NULL,0,0,0);


INSERT INTO `quest_questions` (`question_id`, `type`, `cluster_id`, `question`, `shorttext`, `mode_scoring`, `show_free_variant`) VALUES 
  (1,'single',1,'<p>Оцените выполнение мероприятий по адаптации</p>','Оцените выполнение мероприятий',0,0),
  (2,'free',1,'<p>Отзыв Ответственного за адаптацию о работе Нового сотрудника</p>','Отзыв Ответственного за адаптацию',0,1),
  (4,'single',2,'<p>Итоговый результат прохождения программы адаптации</p>','Итоговый результат',0,1),
  (5,'free',2,'<p>Предложения по дальнейшей работе</p>','Предложения по дальнейшей работе',0,0);

INSERT INTO `questionnaires` (`quest_id`, `type`, `name`, `description`, `mode_display`, `mode_selection`, `mode_passing`, `show_result`, `show_log`, `limit_time`, `limit_attempts`, `mode_display_questions`, `mode_display_clusters`, `comments`, `status`) VALUES 
  (1,'form','Итоговая форма программы адаптации','',0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'<p>Уважаемый коллега!</p>\r\n<p>...</p>',1);

  
  ### Итоговое мероприятие по подбору ###

INSERT INTO `quest_clusters` (`cluster_id`, `quest_id`, `name`) VALUES 
  (3,2,'Результаты прохождения сессии подбора');

INSERT INTO `quest_question_quests` (`question_id`, `quest_id`) VALUES 
  (6,2),
  (7,2);
  
INSERT INTO `quest_question_variants` (`question_variant_id`, `question_id`, `variant`, `shorttext`, `file_id`, `is_correct`, `category_id`, `weight`) VALUES 
  (13,6,'Кандидат прошёл отбор и рекомендован к зачислению в должность',NULL,NULL,0,0,0),
  (14,6,'Кандидат не прошёл отбор, рекомендован к зачислению в кадровый резерв',NULL,NULL,0,0,0),
  (15,6,'Кандидат не прошёл отбор, не рекомендован к работе',NULL,NULL,0,0,0),
  (16,6,'Кандидат не прошёл отбор',NULL,NULL,0,0,0);

INSERT INTO `quest_questions` (`question_id`, `type`, `cluster_id`, `question`, `shorttext`, `mode_scoring`, `show_free_variant`) VALUES 
  (6,'single',3,'<p>Результат прохождения программы подбора</p>','Результат прохождения программы подбора',0,0),
  (7,'free',3,'<p>Предложения по дальнейшей работе с данным кандидатом</p>','Предложения по дальнейшей работе с данным кандидатом',0,0);

INSERT INTO `questionnaires` (`quest_id`, `type`, `name`, `description`, `mode_display`, `mode_selection`, `mode_passing`, `show_result`, `show_log`, `limit_time`, `limit_attempts`, `mode_display_questions`, `mode_display_clusters`, `comments`, `status`) VALUES 
  (2,'form','Итоговая форма программы подбора','',0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'<p>Уважаемый коллега!</p>\r\n<p>...</p>',1);

  ### Итоговое мероприятие по подбору end ###
  
  CREATE OR REPLACE VIEW `roles_source` AS 
SELECT `People`.`MID` AS `mid`,_utf8'enduser' AS `role` FROM `People`
UNION SELECT `Teachers`.`MID` AS `mid`,_utf8'teacher' AS `role` FROM `Teachers`
UNION SELECT `Students`.`MID` AS `mid`,_utf8'student' AS `role` FROM `Students`
UNION SELECT `admins`.`MID` AS `mid`,_utf8'admin' AS `role` FROM `admins`
UNION SELECT `developers`.`mid` AS `mid`,_utf8'developer' AS `role` FROM `developers`
UNION SELECT `managers`.`mid` AS `mid`,_utf8'manager' AS `role` FROM `managers`
UNION SELECT `at_managers`.`user_id` AS `mid`,_utf8'atmanager' AS `role` FROM `at_managers` LEFT JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NULL
UNION SELECT DISTINCT `at_managers`.`user_id` AS `mid`,_utf8'atmanager_local' AS `role` FROM `at_managers` INNER JOIN responsibilities ON at_managers.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL
UNION SELECT `supervisors`.user_id AS MID, _utf8'supervisor' AS `role` FROM `supervisors`
union select `employee`.`user_id` AS `MID`,_utf8'employee' AS `role` from `employee`
UNION SELECT `recruiters`.`user_id` AS `mid`,_utf8'recruiter' AS `role` from `recruiters` LEFT JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NULL
UNION SELECT DISTINCT `recruiters`.`user_id` AS `mid`,_utf8'recruiter_local' AS `role` FROM `recruiters` INNER JOIN responsibilities ON recruiters.user_id = responsibilities.user_id WHERE responsibilities.responsibility_id IS NOT NULL
UNION SELECT `deans`.`MID` AS `mid`,_utf8'dean' AS `role` FROM `deans`;

CREATE OR REPLACE VIEW `roles` AS 
SELECT MID, GROUP_CONCAT(role) AS role FROM roles_source GROUP BY MID;
 
CREATE OR REPLACE VIEW activities_source AS
SELECT MID AS MID, 'teacher' AS role, 'subject' AS subject_name, CID AS subject_id FROM Teachers WHERE CID > 0 
UNION SELECT MID AS MID, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id FROM Students WHERE CID > 0 
UNION SELECT MID AS MID, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id FROM graduated WHERE CID > 0 
UNION SELECT MID AS MID, 'dean' AS role, 'subject' AS subject_name, 0 AS subject_id FROM deans WHERE subject_id = 0
UNION SELECT MID AS MID, 'dean' AS role, 'subject' AS subject_name, subject_id AS subject_id FROM deans WHERE subject_id > 0
UNION SELECT mid AS MID, 'manager' AS role, 'course' AS subject_name, 0 AS subject_id FROM managers
UNION SELECT mid AS MID, 'developer' AS role, 'course' AS subject_name, 0 AS subject_id FROM developers
UNION SELECT mid AS MID, 'manager' AS role, 'resource' AS subject_name, 0 AS subject_id FROM managers
UNION SELECT mid AS MID, 'developer' AS role, 'resource' AS subject_name, 0 AS subject_id FROM developers
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
    `subjects`.`name` AS `subject_name`,
    count(`forums_messages`.`message_id`) AS `volume`,
    max(`forums_messages`.`created`) AS `updated`,
    `resources`.`resource_id` AS `resource_id`,
    `resources`.`status` AS `status` 
  from 
    ((((`forums_sections` join `forums_list` on((`forums_list`.`forum_id` = `forums_sections`.`forum_id`))) join `subjects` on((`forums_list`.`subject_id` = `subjects`.`subid`))) left join `forums_messages` on((`forums_list`.`forum_id` = `forums_messages`.`forum_id`))) left join `resources` on(((`resources`.`activity_id` = `forums_sections`.`section_id`) and (`resources`.`activity_type` = 2)))) 
  where 
    isnull(`forums_sections`.`lesson_id`) OR (`forums_sections`.`lesson_id` = 0)
  group by 
    `forums_list`.`forum_id` union 
  select 
    64 AS `activity_type`,
    `subjects`.`subid` AS `activity_id`,
    '' AS `activity_name`,
    `subjects`.`subid` AS `subject_id`,
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
