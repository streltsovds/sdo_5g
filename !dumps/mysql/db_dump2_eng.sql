USE studium; # ��� ����

#
# ��������! �� ������ �������� �������� VERSION � BUILD
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
  (1,'Sample Module','block=simple~name=description%END%type=fckeditor%END%title=%END%value=Sample Module%END%sub=%END%~[~~]',0,'','2011-01-01','2021-01-01',0,0,'2','elearn@hypermethod.com','2011-01-01',120,0);

INSERT INTO `subjects` (`subid`, `name`, `reg_type`, `begin`, `end`) VALUES (1, 'Sample Training', 0, '2011-01-01', '2021-01-01');

INSERT INTO `organizations` (`title`, `cid`, `prev_ref`, `level`) VALUES ('<empty>','1','-1', '0');

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

INSERT INTO `People` (`MID`, `LastName`, `FirstName`, `Password`, `Login`) VALUES (1, 'Administrator', 'Administrator', PASSWORD('pass'), 'admin');

#
# Data for the `OPTIONS` table  (LIMIT 0,500)
#

INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('dekanName', 'Training manager');
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
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('headStructureUnitName', 'Structure of Organization');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('edo_subdivision_root_name', 'Study Structure');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('default_currency', 'RUB');


DROP TABLE IF EXISTS `213871298721`;


INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (1, 'Creation of a new user account', 'You have been registed in DLS', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (2, 'Assignment to the role', 'You are assigned to the role of [ROLE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (3, 'Assignment to the course (in process of training)', 'You are assigned to the course of [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (4, 'Assignment to electronic course (in process of development)', 'You are included in delelopers\' group of electronic course [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (5, 'There are some days before finishing of training course', 'It\'s time to finish the training course [URL_COURSE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (6, 'Completion of the training course', 'You are assigned to the role of  [ROLE]', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (7, 'Request to training course', 'New request to training course of [URL_COURSE]', 1, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (8, 'Request to training course', 'Your request has been registered', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (9, 'Agreement on request to training course: acceptance ', 'Your request to training course of [URL_COURSE] is accepted', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (10, 'Agreement on request to training course: declination', 'Your request to training course of [URL_COURSE] is declined', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (11, 'User\'s password change', 'Validation of password', 0, ' ');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (12, 'New personal message', '[SUBJECT]', 0, '[TEXT]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (13, 'Source subscription updating', 'Subscription to source of [SOURCE]', 0, '[TEXT]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (14,'Poll of trainees', 'Poll of trainees in the course of [URL_COURSE]', 0, 'You are welcome to take part in the poll in the course of [URL_COURSE]! Feedback is a mandatory step of company employees\' training. You can undergo poll in DLS [URL]) or can click the link of: [URL2]. More detailed information on the poll: \n- Poll name: [TITLE]\n- Poll dates: [BEGIN] - [END]\n');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (15,'Poll of teachers', 'Poll of teachers in the course of [URL_COURSE]', 0, 'You are welcome to take part in the poll in the course of [URL_COURSE]! Feedback is a mandatory step of company employees\' training. You can undergo poll in DLS [URL]) or can click the link of: [URL2]. More detailed information on the poll: \n- Poll name: [TITLE]\n- Poll dates: [BEGIN] - [END]\n');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (16,'Poll of chiefs', 'Poll of chiefs in the course of [URL_COURSE]', 0, 'You are welcome to take part in the poll in the course of [URL_COURSE]! Feedback is a mandatory step of company employees\' training. You can undergo poll in DLS [URL]) or can click the link of: [URL2]. More detailed information on the poll: \n- Poll name: [TITLE]\n- Poll dates: [BEGIN] - [END]\n- Names of emplyees, who finish the training: [SLAVES]\n');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (17,'Template of groupped messages', 'Topic of this template will be changed by topic of event template', 0, 'Message of this template will be changed by groupped messages of event template');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (18,'New message of forum', 'New message from [MESSAGE_USER_NAME]', 0, 'In topic"[SECTION_NAME]" of forum "[FORUM_NAME]" there is a new comment. [MESSAGE_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (19,'New hidden message of forum', 'You received a new hidden message from [MESSAGE_USER_NAME]', 0, 'Your message in topic of "[SECTION_NAME]" in forum of "[FORUM_NAME]" has new hidden comment. [MESSAGE_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (20,'Mark of forum message', 'Your forum message receive the mark', 0, 'Your comment in topic of "[SECTION_NAME]" in forum of  "[FORUM_NAME]" received the mark. [MESSAGE_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (21,'Validation of email', 'You should validate you email', 0, 'To complete the registration you should validate your  email. Click the link: [EMAIL_CONFIRM_URL]');
INSERT INTO notice (`type`, EVENT, TITLE, RECEIVER, MESSAGE) VALUES (22,'User account unlocked', 'User account unlocked', 0, 'Your account has been unblocked. For entering portal click the link: [URL]');

INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (1, 'HyperMethod IBS', NULL, NULL, NULL);
INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (2, 'SkillSoft', NULL, NULL, NULL);

INSERT INTO `processes` VALUES ('5', 'Agreement with the Training Manager', 'a:1:{s:18:"HM_Role_State_Dean";s:22:"HM_Role_State_Complete";}', '1');
INSERT INTO `processes` VALUES ('6', 'Agreement with the Training Manager, with a session selection', 'a:1:{s:21:"HM_Role_State_Session";s:22:"HM_Role_State_Complete";}', '1');

INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (1, 'A value from 0 to 100', 'Any value in the range from 0 to 100', 1);
INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (2, '2 states', 'Passed / Not passed', 2);
INSERT INTO `scales` (`scale_id`, `name`, `description`, `type`) VALUES (3, '3 states', 'Passed / Failed / Not attempted', 3);
