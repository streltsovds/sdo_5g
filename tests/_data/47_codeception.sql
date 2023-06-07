#
# Structure for the `admins` table :
#

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `AID` int(11) NOT NULL auto_increment,
  `MID` int(11) NOT NULL default '0',
  UNIQUE KEY `AID` (`AID`),
  UNIQUE KEY `MID` (`MID`)
) ENGINE=MyISAM;

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
ENGINE=MyISAM;


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
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `classifiers_links`;
CREATE TABLE `classifiers_links` (
  `item_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY  (`item_id`,`classifier_id`, `type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `classifiers_images`;
CREATE TABLE `classifiers_images` (
  `classifier_image_id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`classifier_image_id`)
) ENGINE=MyISAM;



DROP TABLE IF EXISTS `classifiers_types`;

CREATE TABLE `classifiers_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `link_types` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM;

#
# Structure for the `comments` table :
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
) ENGINE=MyISAM;

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
  `emulate_scorm` tinyint(3) NOT NULL default '0',
  `extra_navigation` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`CID`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `deans_responsibilities`;

CREATE TABLE `deans_responsibilities` (
  `user_id` int(11) NOT NULL,
  `classifier_id` int(11) NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `tool` varchar(255) NOT NULL default '',
  `scale_id` int(11) NOT NULL default 1,
  `weight` tinyint(4) NOT NULL default 5,
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `faq`;

CREATE TABLE `faq` (
    `faq_id` int NOT NULL auto_increment,
    `question` TEXT,
    `answer` TEXT,
    `roles` varchar(255) NOT NULL default '',
    `published` ENUM('0','1') default '0',
  PRIMARY KEY  (`faq_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

#
# Structure for the `videoblock` table :
#

DROP TABLE IF EXISTS `videoblock`;

CREATE TABLE `videoblock` (
  `videoblock_id` int(11)  NOT NULL auto_increment,
  `file_id` int(11) NULL,
  `name` varchar(255) NOT NULL default '',
  `embedded_code` text,
  PRIMARY KEY  (`videoblock_id`)
) ENGINE=MyISAM;

#
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
) ENGINE=MyISAM;

#

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `forums_messages_showed`;

CREATE TABLE `forums_messages_showed`(
    `user_id` int unsigned NOT NULL,
    `message_id` int unsigned NOT NULL,
    `created` datetime NULL,
    PRIMARY KEY(`user_id`, `message_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

#
# Structure for the `certificates` table :
#

DROP TABLE IF EXISTS `certificates`;

CREATE TABLE `certificates` (
  `certificate_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `subject_id` int(11) NOT NULL default '0',
  `created` datetime default NULL,
  PRIMARY KEY  (`certificate_id`),
  KEY `USERID` (`user_id`),
  KEY `SUBJECTID` (`subject_id`),
  KEY `USER_SUBJECT` (`user_id`,`subject_id`)
) ENGINE=MyISAM;

#
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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;# Structure for the `list` table :
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
) ENGINE=MyISAM;

#
# Structure for the `list_files` table :
#

DROP TABLE IF EXISTS `list_files`;
CREATE TABLE `list_files` (
  `file_id` int(11) NOT NULL,
  `kod` varchar(255) NOT NULL,
  PRIMARY KEY  (`file_id`,`kod`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
ENGINE=MyISAM;

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
ENGINE=MyISAM;

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
ENGINE=MyISAM;

#
# Structure for the `options` table :
#

DROP TABLE IF EXISTS `OPTIONS`;

CREATE TABLE `OPTIONS` (
  `OptionID` tinyint(4) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`OptionID`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `organizations_bookmarks`;

CREATE TABLE `organizations_bookmarks` (
  `item_id` int(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  UNIQUE KEY `item_user_lesson` (`item_id`,`user_id`,`lesson_id`)
) ENGINE=MyISAM;

#
# Structure for the `people` table :
#

DROP TABLE IF EXISTS `password_history`;

CREATE TABLE `password_history` (
  `user_id` int(10) NULL,
  `password` varchar(255) default NULL,
  `change_date` datetime NOT NULL,
  KEY `user_id` (`user_id`) 
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `simple_auth`;

CREATE TABLE `simple_auth` (
	`auth_key` CHAR(32) NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL,
	`link` VARCHAR(255) NOT NULL,
	`valid_before` DATETIME NOT NULL,
	PRIMARY KEY (`auth_key`)
)ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

#
# Structure for the `permission2act` table :
#

DROP TABLE IF EXISTS `permission2act`;

CREATE TABLE `permission2act` (
  `pmid` int(11) unsigned NOT NULL default '0',
  `acid` varchar(8) NOT NULL default '',
  `type` varchar(255) NOT NULL default 'dean',
  PRIMARY KEY  (`pmid`,`acid`,`type`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `processes`;
CREATE TABLE `processes` (
  `process_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `chain` text,
  `type` int(11) default NULL,
  PRIMARY KEY  (`process_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `programm`;
CREATE TABLE `programm` (
  `programm_id` int(11) NOT NULL auto_increment,
  `programm_type` int(11) default NULL,  
  `name` varchar(255) default NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`programm_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `programm_events`;
CREATE TABLE `programm_events` (
  `programm_event_id` int(11) NOT NULL auto_increment,
  `programm_id` int(11) default NULL,
  `name` varchar(255) default NULL,
  `type` int(11) default NULL,
  `item_id` int(11) default NULL,
  `isElective` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`programm_event_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `programm_events_users`;
CREATE TABLE `programm_events_users` (
  `programm_event_id` int(11) default NULL,
  `user_id` int(11) default NULL,
  `begin_date` datetime default NULL,
  `end_date` datetime default NULL,
  `status` int(11) default '0'
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `programm_users`;
CREATE TABLE `programm_users` (
  `programm_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assign_date` datetime NOT NULL,
  PRIMARY KEY  (`programm_id`,`user_id`)
) ENGINE=MyISAM;

#
# Structure for the `rank` table :
#

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `quizzes_answers`;
CREATE TABLE `quizzes_answers` (
  `quiz_id` int(10) unsigned NOT NULL,
  `question_id` varchar(255) NOT NULL,
  `question_title` varchar(255)  NOT NULL default '',
  `theme` varchar(255)  NOT NULL default '',
  `answer_id` int(11) NOT NULL default '0',
  `answer_title` varchar(255)  NOT NULL default '',
  PRIMARY KEY  (`quiz_id`, `question_id`, `answer_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `responsibilities`;
CREATE  TABLE `responsibilities` (
  `responsibility_id` INT NOT NULL auto_increment,
  `user_id` INT NULL ,
  `item_type` INT NULL,
  `item_id` INT NULL,
  PRIMARY KEY (`responsibility_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
  `max_mark` int(10) NOT NULL DEFAULT '0',
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
  `threshold` int(11) default NULL,
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
) ENGINE=MyISAM;

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
  `created` datetime NULL,
  `updated` datetime NULL,
  `launched` datetime NULL,
  PRIMARY KEY  (`SSID`),
  KEY `MID` (`MID`),
  KEY `SHEID` (`SHEID`),
  KEY `SHEID_MID` (`SHEID`,`MID`)
) ENGINE=MyISAM;

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
)ENGINE=MyISAM;


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
) ENGINE=MyISAM;


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
  `org_id` int(10) DEFAULT '0',
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
) ENGINE=MyISAM;


#
# Structure for the `structure_organ_list` table : 
#
DROP TABLE IF EXISTS `structure_organ_list`;
CREATE TABLE `structure_organ_list` (
  `org_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`org_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


#
# Structure for the `study_groups` table : 
#
DROP TABLE IF EXISTS `study_groups`;
CREATE TABLE `study_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_auto` table : 
#

DROP TABLE IF EXISTS `study_groups_auto`;
CREATE TABLE `study_groups_auto` (
  `group_id` int(10) unsigned NOT NULL,
  `position_code` varchar(100) NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`position_code`,`department_id`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_courses` table : 
#

DROP TABLE IF EXISTS `study_groups_courses`;
CREATE TABLE `study_groups_courses` (
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

DROP TABLE IF EXISTS `study_groups_custom`;
CREATE TABLE `study_groups_custom` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM;

#
# Structure for the `study_groups_programms` table : 
#

DROP TABLE IF EXISTS `study_groups_programms`;
CREATE TABLE `study_groups_programms` (
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

DROP TABLE IF EXISTS `tag`;
CREATE TABLE tag
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `body`       varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tag_ref_blog`;
DROP TABLE IF EXISTS `tag_ref`;
CREATE TABLE `tag_ref`
(
  `tag_id`  INT(10) UNSIGNED NOT NULL,
  `item_type` INT(10) UNSIGNED NOT NULL,
  `item_id` INT(10) UNSIGNED NOT NULL,  
  PRIMARY KEY (`tag_id`, `item_type`, `item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `Teachers`;

CREATE TABLE `Teachers` (
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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `tests_questions`;
CREATE TABLE `tests_questions` (
  `subject_id` int(11) NOT NULL DEFAULT 0,
  `test_id` int(11) NOT NULL DEFAULT 0,
  `kod` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY  (`subject_id`, `test_id`, `kod`),
  KEY `kod` (`kod`),
  KEY `subject_id` (`subject_id`),
  KEY `test_id` (`test_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `scorm_report`;

CREATE TABLE `scorm_report` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `mid` int(10) unsigned NOT NULL default '0',
  `lesson_id` int(10) unsigned NOT NULL default '0',
  `report_data` blob NOT NULL,
  `updated` datetime default NULL,
  PRIMARY KEY  (`report_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `sessid` int(10) unsigned NOT NULL auto_increment,
  `sesskey` varchar(32) NOT NULL default '',
  `mid` int(10) unsigned NOT NULL default '0',
  `course_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lesson_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lesson_type` int(10) unsigned NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `session_guest`;
CREATE TABLE `session_guest` (
  `session_guest_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `start` DATETIME NULL DEFAULT NULL ,
  `stop` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`session_guest_id`) )
ENGINE=MyISAM;

DROP TABLE IF EXISTS `interesting_facts`;
CREATE TABLE `interesting_facts` (
  `interesting_facts_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `title` TEXT NULL DEFAULT NULL ,
  `text` TEXT NULL DEFAULT NULL ,
  `status` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`interesting_facts_id`) )
ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `interview_files`;
CREATE TABLE `interview_files` (
  `interview_id` int(11) NOT NULL,
  `file_id` varchar(45) NOT NULL,
  PRIMARY KEY  (`interview_id`,`file_id`)
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `library_categories`;
CREATE TABLE `library_categories` (
  `catid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `parent` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`catid`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `library_index`;
CREATE TABLE `library_index` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` int(10) NOT NULL default '0',
  `file` varchar(255) NOT NULL default '',
  `keywords` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `courses_marks`;
CREATE TABLE `courses_marks` (
  `cid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `mark` varchar(255) NOT NULL default '-1',
  `alias` varchar(255) NOT NULL default '',
  `confirmed` tinyint(4) NOT NULL default '0',
  `comments` text,
  PRIMARY KEY  (`cid`,`mid`),
  KEY `cid` (`cid`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `hacp_debug`;
CREATE TABLE `hacp_debug` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `message` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `direction` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `holidays`;
CREATE TABLE `holidays` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `date` DATE NOT NULL,
  `type` TINYINT(4) NOT NULL DEFAULT '0',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `htmlpage`;
CREATE TABLE `htmlpage` (
  `page_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned,
  `name` varchar(255) NOT NULL default '',
  `ordr` int(11) NOT NULL default '10',  
  `text` text NOT NULL,
  `url` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`page_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `crontask`;
CREATE TABLE `crontask` (
  `crontask_id` varchar(255) NOT NULL default '',
  `crontask_runtime` int(11) unsigned default NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `managers`;

CREATE TABLE `managers` (
  `id` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `sequence_current`;
CREATE TABLE `sequence_current` (
  `mid` int(10) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  `current` varchar(255) NOT NULL default '',
  `subject_id` int(10) NOT NULL default '0',
  `lesson_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`,`subject_id`,`lesson_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `sequence_history`;
CREATE TABLE `sequence_history` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`mid` INT UNSIGNED NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
`item` varchar(255) NOT NULL default '',
`date` DATETIME NOT NULL ,
`subject_id` int(10) NOT NULL default '0',
`lesson_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS `developers`;
CREATE TABLE `developers` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `methodologist`;
CREATE TABLE `methodologist` (
  `mid` int(11) NOT NULL default '0',
  `cid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`,`cid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `supervisors`;

CREATE TABLE `supervisors` (
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `employee`;

CREATE TABLE `employee` (
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `providers`;
CREATE TABLE `providers` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `contacts` text,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `address` text,
  `contacts` text,
  `description` text,
  PRIMARY KEY  (`supplier_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `webinar_answers`;
CREATE TABLE `webinar_answers` (
  `aid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL,
  `text` varchar(255) default NULL,
  PRIMARY KEY  (`aid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinars`;
CREATE TABLE `webinars` (
  `webinar_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `create_date` datetime NULL,
  `subject_id` int(11) NULL,
  PRIMARY KEY  (`webinar_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `webinar_chat`;
CREATE TABLE `webinar_chat` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `message` varchar(255) NOT NULL default '',
    `datetime` datetime NULL,
    `userId` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_files`;
CREATE TABLE `webinar_files` (
    `webinar_id` int(11) NOT NULL default '0',
    `file_id` int(11) NOT NULL default '0',
    `num` int(11) NOT NULL default '0'
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `webinar_history`;
CREATE TABLE `webinar_history` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `userId` int(11) NOT NULL default '0',
    `action` varchar(255) NOT NULL default '',
    `item` varchar(255) NOT NULL default '',
    `datetime` datetime NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_plan`;
CREATE TABLE `webinar_plan` (
    `id` int(11) NOT NULL auto_increment,
    `pointId` int(11) NOT NULL default '0',
    `href` varchar(255) NOT NULL default '',
    `title` varchar(255) NOT NULL default '',
    `bid` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_plan_current`;
CREATE TABLE `webinar_plan_current` (
    `pointId` int(11) NOT NULL,
    `currentItem` int(11) NOT NULL default '0',
    PRIMARY KEY  (`pointId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_questions`;
CREATE TABLE `webinar_questions` (
  `qid` int(11) NOT NULL auto_increment,
  `text` varchar(255) default NULL,
  `type` tinyint(1) default NULL,
  `point_id` int(11) default NULL,
  `is_voted` tinyint(1) default NULL,
  PRIMARY KEY  (`qid`),
  UNIQUE KEY `text` (`text`,`point_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_users`;
CREATE TABLE `webinar_users` (
    `pointId` int(11) NOT NULL,
    `userId` int(11) NOT NULL,
    `last` datetime NULL,
    PRIMARY KEY  (`pointId`,`userId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_votes`;
CREATE TABLE `webinar_votes` (
  `vid` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `qid` int(11) default NULL,
  `aid` int(11) default NULL,
  PRIMARY KEY  (`vid`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_whiteboard_points`;
CREATE TABLE `webinar_whiteboard_points` (
  `pointId` int(11) NOT NULL auto_increment,
  `actionId` int(11) default NULL,
  `x` int(11) default NULL,
  `y` int(11) default NULL,
  `type` int(11) default NULL,
  PRIMARY KEY  (`pointId`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `webinar_records`;
CREATE TABLE `webinar_records` (
  `id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NOT NULL default '0',
  `webinar_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `wiki_archive`;
CREATE TABLE `wiki_archive`
(
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id`    INT(10) UNSIGNED NOT NULL,
  `created` DATETIME,
  `author`  INT(10) UNSIGNED NOT NULL,
  `body`    LONGTEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `video`;
CREATE TABLE `video` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `created` int(11) unsigned NOT NULL default '0',
  `title` VARCHAR(255) NOT NULL default '',
  `main_video` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

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
  `external_viewer` varchar(16) NOT NULL,  
  PRIMARY KEY  (`resource_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `scales`;
CREATE TABLE `scales` (
  `scale_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text,
  `type` tinyint(4) NOT NULL default '0',
  `mode` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`scale_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `scale_values`;
CREATE TABLE `scale_values` (
  `value_id` int(11) NOT NULL auto_increment,
  `scale_id` int(11) NOT NULL,
  `value` int(11) NOT NULL default '0',
  `text` varchar(255) default NULL,
  `description` text,  
  PRIMARY KEY  (`value_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


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
  `price` float default NULL,
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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_courses`;
CREATE TABLE `subjects_courses` (
  `subject_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`course_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_quests`;
CREATE TABLE `subjects_quests` (
  `subject_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`quest_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_exercises`;
CREATE TABLE `subjects_exercises` (
  `subject_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`exercise_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_resources`;
CREATE TABLE `subjects_resources` (
  `subject_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`resource_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_quizzes`;
CREATE TABLE `subjects_quizzes` (
  `subject_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`quiz_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_tasks`;
CREATE TABLE `subjects_tasks` (
  `subject_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  PRIMARY KEY  (`subject_id`,`task_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subjects_feedback`;
CREATE TABLE `subjects_feedback` (
	`feedback_id` INT(11) NOT NULL AUTO_INCREMENT,
	`subject_id` INT(11) NULL DEFAULT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`quest_id` INT(11) NULL DEFAULT NULL,
	`status` INT(11) NOT NULL DEFAULT '0',
	`date_finished` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`feedback_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `state_of_process`;
CREATE TABLE `state_of_process` (
  `state_of_process_id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,
  `process_id` int(11) default NULL,
  `process_type` int(11) NOT NULL,
  `current_state` varchar(255) default NULL,
  `status` int(11) default NULL,
  `params` text,
  PRIMARY KEY  (`state_of_process_id`),
  KEY `item_id` (`item_id`),
  KEY `process_id` (`process_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions`  ( 
    `subscription_id` int(11) AUTO_INCREMENT NOT NULL,
    `user_id`         int(10) UNSIGNED NOT NULL DEFAULT '0',
    `channel_id`      int(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY(`subscription_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `interface`;
CREATE TABLE `interface` (
  `interface_id` int(11) NOT NULL auto_increment,
  `role` varchar(255) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `block` varchar(255) NOT NULL default '',
  `necessity` int(11) default '0',
  `x` int(11) NOT NULL default '1',
  `y` int(11) NOT NULL default '1',
  `width` int(11) NOT NULL default '100',
  `param_id` varchar(255) default NULL,
  PRIMARY KEY  (`interface_id`),
  KEY `role` (`role`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `captcha`;
CREATE TABLE `captcha` (
  `login` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL default '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`login`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `videochat_users`;
CREATE TABLE `videochat_users` (
    `pointId` varchar(255) NOT NULL,
    `userId` int(11) NOT NULL,
    `last` datetime NULL,
    PRIMARY KEY  (`pointId`,`userId`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;


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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `chat_ref_users`;
CREATE TABLE `chat_ref_users`
(
  channel_id           int(10) UNSIGNED NOT NULL,
  user_id           int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (channel_id,user_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `subject_id` int(11) NOT NULL,
  `name` varchar(255),
  `order` tinyint(4) NULL,
  PRIMARY KEY  (`section_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `reports_roles`;
CREATE TABLE `reports_roles`
(
  `role` varchar(100) NOT NULL default '',
  `report_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`role`, `report_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `support_requests`;
CREATE TABLE `support_requests` (
  `support_request_id` INT NOT NULL AUTO_INCREMENT,
  `date_` DATETIME NULL,
  `theme` VARCHAR(255) NULL,
  `status` INT NULL,
  `problem_description` TEXT NULL,
  `wanted_result` TEXT NULL,
  `user_id` INT NULL,
  `url` VARCHAR(255) NULL,
  PRIMARY KEY (`support_request_id`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `webinar_dbs`;
CREATE TABLE  `webinar_dbs` (
  `db_id` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
	`like_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`item_type` INT(11) NOT NULL,
	`item_id` INT(11) NOT NULL,
	`count_like` INT(11) NOT NULL DEFAULT '0',
	`count_dislike` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`like_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `like_user`;
CREATE TABLE `like_user` (
	`like_user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`item_type` INT(11) UNSIGNED NOT NULL,
	`item_id` INT(11) UNSIGNED NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL,
	`value` TINYINT(4) NOT NULL,
	`date` DATETIME NOT NULL,
	PRIMARY KEY (`like_user_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `questionnaires`;
CREATE TABLE `questionnaires` (
  `quest_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(16) DEFAULT NULL,
  `status` TINYINT(4) DEFAULT 0,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `subject_id` INTEGER(11) DEFAULT 0,
  `scale_id` INTEGER(11) DEFAULT 0,
  PRIMARY KEY (`quest_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_clusters`;
CREATE TABLE `quest_clusters` (
  `cluster_id` int(11) NOT NULL AUTO_INCREMENT,
  `quest_id` INTEGER(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cluster_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_questions`;
CREATE TABLE `quest_questions` (
  `question_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` INTEGER(11) DEFAULT NULL,
  `subject_id` INT(11) NOT NULL DEFAULT '0',  
  `type` VARCHAR(16) DEFAULT NULL,
  `quest_type` VARCHAR(16) DEFAULT NULL,
  `question` TEXT NOT NULL,
  `shorttext` VARCHAR(255) DEFAULT NULL,
  `mode_scoring` TINYINT(4) DEFAULT NULL,
  `show_free_variant` TINYINT(4) DEFAULT NULL,
  `file_id` INT(11) NOT NULL DEFAULT '0',
  `data` TEXT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_question_quests`;
CREATE TABLE `quest_question_quests` (
  `question_id` INTEGER(11) NOT NULL,
  `quest_id` INTEGER(11) NOT NULL,
  `cluster_id` INTEGER(11) NOT NULL,
  KEY (`question_id`, `quest_id`)
) ENGINE=MyISAM;

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
  `data` TEXT NULL,
  PRIMARY KEY (`question_variant_id`)
) ENGINE=MyISAM;

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
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_categories`;
CREATE TABLE `quest_categories` (
  `category_id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `quest_id` INTEGER(11) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_category_results`;
CREATE TABLE `quest_category_results` (
  `category_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `attempt_id` int(11) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `result` text,
  PRIMARY KEY (`category_result_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_question_results`;
CREATE TABLE `quest_question_results` (
  `question_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `variant` TEXT DEFAULT NULL,
  `free_variant` TEXT DEFAULT NULL,
  `is_correct` tinyint(4) DEFAULT NULL,
  `score_weighted` float(9,3) DEFAULT NULL,
  `score_raw` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`question_result_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `quest_settings`;
CREATE TABLE `quest_settings` (
  `quest_id` int(11) NOT NULL,
  `scope_type` tinyint(4) default NULL,
  `scope_id` int(11) default NULL,
  `info` text,
  `cluster_limits` text,
  `comments` text,
  `mode_selection` tinyint(4) default NULL,
  `mode_selection_questions` tinyint(4) default NULL,
  `mode_selection_all_shuffle` tinyint(4) default NULL,
  `mode_passing` tinyint(4) default NULL,
  `mode_display` tinyint(4) default NULL,
  `mode_display_clusters` tinyint(4) default NULL,
  `mode_display_questions` tinyint(4) default NULL,
  `show_result` tinyint(4) default NULL,
  `show_log` tinyint(4) default NULL,
  `limit_time` tinyint(4) default NULL,
  `limit_attempts` tinyint(4) default NULL,
  UNIQUE KEY (`quest_id`, `scope_type`, `scope_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `user_additional_fields`;
CREATE TABLE `user_additional_fields` (
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`field_id` INT(11) NOT NULL DEFAULT '0',
	`value` TEXT NOT NULL,
	UNIQUE KEY (`user_id`, `field_id`)
)
ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_events`;
CREATE TABLE `es_events` (
	event_id INT(11) NOT NULL AUTO_INCREMENT,
	event_type_id INT(8) NOT NULL,
	event_trigger_id INT(11) NOT NULL,
	event_group_id INT(8) DEFAULT NULL,
	description TEXT NOT NULL DEFAULT '',
	create_time DOUBLE(25,10) NOT NULL,
	PRIMARY KEY (event_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_event_users`;
CREATE TABLE `es_event_users` (
	event_id INT(11) NOT NULL,
	user_id INT(11) NOT NULL,
	views TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (event_id,user_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_event_group_types`;
CREATE TABLE `es_event_group_types` (
        event_group_type_id INT(8) NOT NULL,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (event_group_type_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_event_types`;
CREATE TABLE `es_event_types` (
	event_type_id INT(8) NOT NULL,
	name VARCHAR(255) NOT NULL,
        event_group_type_id INT(8) NOT NULL,
	PRIMARY KEY (event_type_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_event_groups`;
CREATE TABLE `es_event_groups` (
	event_group_id INT(8) NOT NULL AUTO_INCREMENT,
	trigger_instance_id INT(11) NOT NULL,
	type VARCHAR(255) NOT NULL,
	data TEXT NOT NULL,
	UNIQUE KEY group_name (trigger_instance_id,type),
	PRIMARY KEY (event_group_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_notify_types`;
CREATE TABLE `es_notify_types` (
        notify_type_id INT(8) NOT NULL,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (notify_type_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `es_user_notifies`;
CREATE TABLE `es_user_notifies` (
        user_id INT(11) NOT NULL,
        notify_type_id INT(8) NOT NULL,
        event_type_id INT(8) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (user_id,notify_type_id, event_type_id)
) ENGINE=MyISAM;

CREATE INDEX event_type_id ON es_events (event_type_id);



DROP TABLE IF EXISTS `tracks2group`;
CREATE TABLE `tracks2group` (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        trid INT(11) NOT NULL DEFAULT 0,
        level INT(11) NOT NULL DEFAULT 0,
        gid INT(11) NOT NULL DEFAULT 0,
        updated datetime
) ENGINE=MyISAM;

#
# ВНИМАНИЕ! НЕ ЗАБЫТЬ ИЗМЕНИТЬ ЗНАЧЕНИЯ VERSION И BUILD
#
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('version', '4.x');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('build', 'YYYYMMDD');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('regnumber', '');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('grid_rows_per_page', '25');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('headStructureUnitName', 'Организационная структура');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('edo_subdivision_root_name', 'Учебная структура');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('default_currency', 'RUB');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('disable_multiple_authentication', '0');

INSERT INTO `People` (`MID`, `LastName`, `FirstName`, `Password`, `Login`) VALUES (1, 'Администратор', 'Администратор', PASSWORD('pass'), 'admin');
INSERT INTO `Teachers` (`PID`, `MID`, `CID`) VALUES (1,1,1);
INSERT INTO `Students` (`SID`, `MID`, `CID`, `cgid`, `Registered`) VALUES (1,1,1,0,1);
INSERT INTO `managers` (mid) VALUES (1);
INSERT INTO `developers` (mid, cid) VALUES (1, 0);
INSERT INTO `admins` (`AID`, `MID`) VALUES (1,1);
INSERT INTO `deans` (`DID`, `MID`) VALUES (1, 1);

INSERT INTO `Courses` (`CID`, `Title`, `Description`, `TypeDes`, `CD`, `cBegin`, `cEnd`, `Fee`, `valuta`, `Status`, `createby`, `createdate`, `longtime`, `did`) VALUES (1,'Пример учебного модуля','',0,'','2011-01-01','2021-01-01',0,0,'2','elearn@hypermethod.com','2011-01-01',120,0);
INSERT INTO `subjects` (`subid`, `name`, `reg_type`, `begin`, `end`) VALUES (1, 'Пример учебного курса', 0, '2011-01-01', '2021-01-01');
INSERT INTO `organizations` (`title`, `cid`, `prev_ref`, `level`) VALUES ('<пустой элемент>','1','-1', '0');

INSERT INTO `webinar_dbs` (`db_id`, `host`, `port`, `name`, `login`, `pass`) VALUES
('1234', 'localhost', 3306, 'studium', 'root', 'SDmBOtVP');

INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (1, 'Создание новой учетной записи пользователя', 'Вы зарегистрированы  в ИСДО', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (2, 'Назначение роли пользователю', 'Вам назначена роль [ROLE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (3, 'Назначение на учебный курс (в процессе обучения)', 'Вы назначены на обучение по курсу [COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (4, 'Назначение на учебный модуль (в процессе разработки учебного модуля)', 'Вы назначены в группу  разработчиков учебного модуля [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (5, 'Определенное количество дней до окончания обучения по курсу ', 'Заканчивается обучение по курсу [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (6, 'Перевод пользователя в прошедшие обучение по курсу', '<p>Вы успешно прошли курс [URL_COURSE] <span>в </span><span>системе дистанционного обучения Базовый Элемент [URL]</span></p>  <p><span>  Ссылка на сертификат: [CERTIFICATE_LINK]. </span></p>', 0, ' ');
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
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (22,'Учётная запись разблокирована', 'Учётная запись разблокирована', 0, 'Ваша учетная запись была разблокирована. Для входа на портал, перейдите по ссылке: [URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (23,'Ответ на заявку','Вы получили ответ на заявку № [ID]',0,'Вы получили ответ на заявку № [ID].  Тема заявки: [TITLE]  ФИО отправителя: [LFNAME]  Описание проблемы и желаемый результат: [REQUEST]  Ответ админитратора: [RESPONSE]  Статус заявки: [STATUS]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (24,'Смена статуса и создание заявки','Статус вашей заявки № [ID] изменен на "[STATUS]"',0,'Статус вашей заявки № [ID] изменен на "[STATUS]".  Тема заявки: [TITLE]  ФИО отправителя: [LFNAME]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (25,'Новая заявка','Новая заявка № [ID]',0,'Создана новая заявка № [ID].  Тема заявки: [TITLE]  ФИО отправителя: [LFNAME]  Описание проблемы и желаемый результат: [REQUEST] Статус заявки: [STATUS]');

INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (1, 'ГиперМетод', NULL, NULL, NULL);
INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (2, 'SkillSoft', NULL, NULL, NULL);

INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (1, 'Значения от 0 до 100', 'Любые значения в диапазоне от 0 до 100', 1);
INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (2, '2 состояния', 'Пройдено / Не пройдено', 2);
INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (3, '3 состояния', 'Пройдено успешно / Пройдено неуспешно / Не пройдено', 3);

INSERT INTO `forums_list` (`forum_id`, `subject_id`, `user_id`, `user_name`, `user_ip`, `title`, `flags`) VALUES (1, 0, 1, 'Администратор Администратор', '127.0.0.1', 'Форум портала', 6);

INSERT INTO `es_event_group_types` (`event_group_type_id`, `name`) VALUES
(1, 'personalMessages'),
(2, 'discussions'),
(3, 'notifications');

INSERT INTO `es_event_types` (`event_type_id`,`name`, `event_group_type_id`) VALUES
(1,'forumAddMessage', 2),
(2, 'blogAddMessage', 2),
(3, 'wikiAddPage', 2),
(4, 'wikiModifyPage', 2),
(5, 'forumInternalAddMessage', 2),
(6, 'blogInternalAddMessage', 2),
(7, 'wikiInternalAddPage', 2),
(8, 'wikiInternalModifyPage', 2),
(9, 'courseAddMaterial', 3),
(10, 'courseAttachLesson', 3),
(11, 'courseScoreTriggered', 3),
(12, 'courseTaskAction', 3),
(13, 'commentAdd', 2),
(14, 'commentInternalAdd', 2),
(15, 'courseTaskScoreTriggered', 3),
(16, 'personalMessageSend', 1),
(17, 'courseFeedbackRequest', 3);

INSERT INTO `es_notify_types` (`notify_type_id`, `name`) VALUES
(1, 'Email notifications'),
(2, 'Weekly reports by email');



CREATE OR REPLACE VIEW `roles_source` AS 
SELECT `People`.`MID` AS `mid`,_utf8'enduser' AS `role` FROM `People`
UNION SELECT `Teachers`.`MID` AS `mid`,_utf8'teacher' AS `role` FROM `Teachers`
UNION SELECT `Students`.`MID` AS `mid`,_utf8'student' AS `role` FROM `Students`
UNION SELECT `admins`.`MID` AS `mid`,_utf8'admin' AS `role` FROM `admins`
UNION SELECT `developers`.`mid` AS `mid`,_utf8'developer' AS `role` FROM `developers`
UNION SELECT `managers`.`mid` AS `mid`,_utf8'manager' AS `role` FROM `managers`
UNION SELECT `supervisors`.user_id AS `mid`, _utf8'supervisor' AS `role` FROM `supervisors`
union select `employee`.`user_id` AS `mid`,_utf8'employee' AS `role` from `employee`
UNION SELECT `deans`.`MID` AS `mid`,_utf8'dean' AS `role` FROM `deans`;

CREATE OR REPLACE VIEW `roles` AS 
SELECT MID, GROUP_CONCAT(role) AS role FROM roles_source GROUP BY MID;
 
CREATE OR REPLACE VIEW activities_source AS
SELECT MID AS MID, 'teacher' AS role, 'subject' AS subject_name, CID AS subject_id FROM Teachers WHERE CID > 0
UNION SELECT MID AS MID, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id FROM Students WHERE CID > 0 
UNION SELECT MID AS MID, 'enduser' AS role, 'subject' AS subject_name, CID AS subject_id FROM graduated WHERE CID > 0 
UNION SELECT MID AS MID, 'dean' AS role, 'subject' AS subject_name, 0 AS subject_id FROM deans WHERE subject_id = 0
UNION SELECT user_id AS MID, 'supervisor' AS role, 'subject' AS subject_name, 0 AS subject_id FROM supervisors
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

CREATE OR REPLACE VIEW `study_groups_auto_users` AS 
SELECT `ga`.`group_id` AS `group_id`,`sou`.`mid` AS `user_id` 
FROM ((`study_groups_auto` `ga` join `structure_of_organ` `sod` on((`sod`.`soid` = `ga`.`department_id`))) join `structure_of_organ` `sou` on(((`sou`.`lft` >= `sod`.`lft`) and (`sou`.`rgt` <= `sod`.`rgt`) and (`sou`.`code` = `ga`.`position_code`))));

CREATE OR REPLACE VIEW `study_groups_users` AS 
SELECT `study_groups_custom`.`group_id` AS `group_id`,`study_groups_custom`.`user_id` AS `user_id`,1 AS `type` 
FROM `study_groups_custom` 
UNION 
SELECT `study_groups_auto_users`.`group_id` AS `group_id`,`study_groups_auto_users`.`user_id` AS `user_id`,2 AS `type` 
FROM `study_groups_auto_users`;
