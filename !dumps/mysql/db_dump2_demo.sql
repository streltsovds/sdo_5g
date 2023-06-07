﻿INSERT INTO `Courses` (`CID`, `Title`, `Description`, `TypeDes`, `CD`, `cBegin`, `cEnd`, `Fee`, `valuta`, `Status`, `createby`, `createdate`, `longtime`, `did`, `credits_student`, `credits_teacher`, `locked`, `chain`, `is_poll`, `is_module_need_check`, `type`, `tree`, `progress`, `sequence`, `provider`, `provider_options`, `planDate`, `developStatus`, `lastUpdateDate`, `archiveDate`, `services`, `has_tree`, `new_window`, `emulate`, `format`) VALUES 
  (1,'Пример электронного курса','block=simple~name=description%END%type=fckeditor%END%title=%END%value=Пример описания курса%END%sub=%END%~[~~]',0,'','2011-01-01','2021-01-01',0,0,'2','elearn@hypermethod.com','2011-01-01 00:00:00',120,'0',0,0,0,0,0,0,0,'a:3:{s:6:\"parent\";a:0:{}s:9:\"reference\";a:2:{i:0;a:2:{i:0;a:1:{s:3:\"oid\";i:0;}i:1;a:0:{}}i:1;a:1:{i:0;a:4:{s:3:\"oid\";s:1:\"1\";s:8:\"prev_ref\";s:2:\"-1\";s:5:\"level\";s:1:\"0\";s:6:\"parent\";i:0;}}}s:6:\"result\";a:1:{i:0;s:1:\"1\";}}',0,0,0,'',NULL,NULL,NULL,NULL,0,0,0,0,0),
  (2,'Демо Модуль','Демо Модуль',0,'','0000-00-00','0000-00-00',0,0,'1','','2011-11-23 12:51:37',10,'',0,0,0,1,0,0,0,'a:3:{s:6:\"parent\";a:0:{}s:9:\"reference\";a:2:{i:0;a:2:{i:0;a:1:{s:3:\"oid\";i:0;}i:1;a:0:{}}i:2;a:1:{i:0;a:4:{s:3:\"oid\";s:1:\"2\";s:8:\"prev_ref\";s:2:\"-1\";s:5:\"level\";s:1:\"0\";s:6:\"parent\";i:0;}}}s:6:\"result\";a:1:{i:0;s:1:\"2\";}}',0,0,0,'',NULL,'0','2011-11-23',NULL,0,0,0,0,999);

INSERT INTO `OPTIONS` (`OptionID`, `name`, `value`) VALUES 
  (1,'version','4.0'),
  (2,'build','2011-04-01'),
  (3,'regnumber',''),
  (4,'dekanName','Учебная администрация'),
  (5,'dekanEMail','some@e.mail'),
  (6,'max_invalid_login','0'),
  (7,'chat_server_port','50011'),
  (8,'drawboard_port','50012'),
  (9,'import_ims_compatible','1'),
  (10,'question_edit_additional_rows','3'),
  (11,'answers_local_log_full','1'),
  (12,'course_description_format','simple'),
  (13,'disable_copy_material','0'),
  (14,'enable_check_session_exist','0'),
  (15,'enable_eauthor_course_navigation','0'),
  (16,'enable_forum_richtext','1'),
  (17,'regform_email_required','1'),
  (18,'regform_items','s:0:\"\";'),
  (19,'grid_rows_per_page','25'),
  (20,'skin','redmond'),
  (21,'chat_messages_show_in_channel','20'),
  (22,'activity','a:5:{i:1;a:2:{s:4:\"name\";s:14:\"Новости\";s:3:\"url\";s:5:\"/news\";}i:2;a:2:{s:4:\"name\";s:10:\"Форум\";s:3:\"url\";s:6:\"/forum\";}i:128;a:2:{s:4:\"name\";s:4:\"Wiki\";s:3:\"url\";s:5:\"/wiki\";}i:512;a:2:{s:4:\"name\";s:6:\"Чат\";s:3:\"url\";s:5:\"/chat\";}i:8;a:2:{s:4:\"name\";s:35:\"Файловое хранилище\";s:3:\"url\";s:8:\"/storage\";}}'),
  (23,'template_report_header','Общий бланк'),
  (24,'template_report_footer','Общие&nbsp;данные'),
  (25,'windowTitle','Система управления обучением'),
  (26,'headStructureUnitName',''),
  (27,'webinar_media',''),
  (28,'template_order_header','Приказ 1'),
  (29,'template_order_text','Текст приказа'),
  (30,'template_order_footer','<p>Комментарии</p>');

INSERT INTO `People` (`MID`, `mid_external`, `LastName`, `FirstName`, `LastNameLat`, `FirstNameLat`, `Patronymic`, `Registered`, `Course`, `EMail`, `Phone`, `Information`, `Address`, `Fax`, `Login`, `Password`, `javapassword`, `BirthDate`, `CellularNumber`, `ICQNumber`, `Age`, `last`, `countlogin`, `rnid`, `Position`, `PositionDate`, `PositionPrev`, `invalid_login`, `isAD`, `polls`, `Access_Level`, `rang`, `preferred_lang`, `blocked`, `block_message`, `head_mid`, `force_password`, `lang`, `need_edit`) VALUES 
  (1,'','Администратор','Администратор','','','',NULL,1,'','','','','','admin','29bad1457ee5e49e','','0000-00-00','',0,0,20111125120856,35,0,'','0000-00-00','',0,0,NULL,5,0,0,0,NULL,0,0,'rus',0);

INSERT INTO `Students` (`SID`, `MID`, `CID`, `cgid`, `Registered`, `time_registered`, `offline_course_path`, `time_ended`) VALUES 
  (1,1,1,0,1,'2011-11-21 03:47:39','','0000-00-00 00:00:00'),
  (2,1,2,0,1321967151,'2011-11-22 07:05:51','','0000-00-00 00:00:00');

INSERT INTO `Teachers` (`PID`, `MID`, `CID`) VALUES 
  (1,1,1);

INSERT INTO `admins` (`AID`, `MID`) VALUES 
  (1,1);

INSERT INTO `alt_mark` (`id`, `int`, `char`) VALUES 
  (NULL,-2,'+'),
  (NULL,-3,'-');

INSERT INTO `chat_channels` (`id`, `subject_name`, `subject_id`, `lesson_id`, `name`, `start_date`, `end_date`, `show_history`, `start_time`, `end_time`, `is_general`) VALUES 
  (1,NULL,0,NULL,'Общий канал',NULL,NULL,1,NULL,NULL,1);

INSERT INTO `chat_history` (`id`, `channel_id`, `sender`, `receiver`, `message`, `created`) VALUES 
  (1,1,1,0,'x','2011-11-21 12:56:10'),
  (2,1,1,0,'проверка работы чата.','2011-11-23 00:58:49'),
  (3,1,1,0,'y','2011-11-25 10:58:05');

INSERT INTO `classifiers_types` (`type_id`, `name`, `link_types`) VALUES 
  (1,'Тематический классификатор федерального портала \"Российское образование\"','1'),
  (2,'Направление обучения','0 1');

INSERT INTO `conf_cid` (`cid`, `autoindex`) VALUES 
  (1,2);

INSERT INTO `deans` (`DID`, `MID`, `subject_id`) VALUES 
  (1,1,0),
  (2,1,2);

INSERT INTO `developers` (`mid`, `cid`) VALUES 
  (1,0);

INSERT INTO `forumcategories` (`id`, `name`, `cid`, `create_by`, `create_date`, `cms`) VALUES 
  (1,'',0,0,'2011-11-23 00:56:01',0),
  (2,'subject',1,0,NULL,0);

INSERT INTO `forummessages` (`id`, `thread`, `posted`, `icon`, `name`, `email`, `sendmail`, `message`, `is_topic`, `mid`, `type`, `oid`, `parent`) VALUES 
  (1,1,'1321995361',1,'Обсуждение общих вопросов по организации обучения',NULL,0,'<p>Здесь обсуждение общих вопросов по организации обучения.</p>',1,1,0,0,0);

INSERT INTO `forumthreads` (`thread`, `category`, `course`, `lastpost`, `answers`, `private`) VALUES 
  (1,1,NULL,'1321995361',0,0);

INSERT INTO `groupname` (`gid`, `cid`, `name`, `owner_gid`) VALUES 
  (1,1,'Демо группа',NULL);


INSERT INTO `htmlpage` (`page_id`, `group_id`, `name`, `text`) VALUES 
  (1,0,'О Портале','Текст страницы'),
  (2,1,'Пример страницы','<p>текст страницы</p>');

INSERT INTO `htmlpage_groups` (`group_id`, `lft`, `rgt`, `level`, `name`, `role`) VALUES 
  (1,1,2,0,'Группа страниц','guest');

INSERT INTO `interesting_facts` (`interesting_facts_id`, `title`, `text`, `status`) VALUES 
  (1,'Министерство образования предлагает временно перейти на дистанционное обучение (Беларусь)','<p>Во время вируса гриппа школьники будут учиться по телефону</p>\r\n<p>Министерство образования предлагает временно перейти на дистанционное обучение.</p>\r\n<p>На карантин сейчас ушли школьники Минска, Бреста, Бресткой области, Могилева и некоторых других городах страны. Понятно, что учебную программу за все это время детям придется как-то неверстывать. А ведь нагрузки на сегодняшних учеников и так довольно приличные.</p>\r\n<p>Как пишет БелТА, Министерство образования предлагает на время прекращения учебного процесса перейти на дистанционную форму обучения. Для обучения предлагается использовать Интернет и телефон.</p>',1);

INSERT INTO `interface` (`interface_id`, `role`, `user_id`, `block`, `necessity`, `x`, `y`, `param_id`) VALUES 
  (51,'admin',0,'screencastBlock',0,2,1,NULL),
  (50,'admin',0,'lastNewsBlock',0,1,1,NULL),
  (49,'admin',0,'interestingFactBlock',0,0,1,NULL),
  (48,'admin',0,'activitydevBlock',0,1,0,NULL),
  (47,'admin',0,'usersSystemCounterBlock',0,0,0,NULL),
  (75,'student',1,'topSubjectsBlock',0,0,1,NULL),
  (74,'student',1,'randomSubjects',0,2,0,NULL),
  (73,'student',1,'subjectsClassifiers',0,1,0,NULL),
  (72,'student',1,'scheduleDailyBlock',0,0,0,NULL),
  (132,'teacher',1,'faqBlock',0,1,1,NULL),
  (85,'dean',1,'activityBlock',0,0,1,NULL),
  (84,'dean',1,'claimsBlock',0,0,0,NULL),
  (90,'developer',1,'news',0,0,1,'1'),
  (89,'developer',1,'lastNewsBlock',0,0,0,NULL),
  (95,'manager',1,'lastNewsBlock',0,0,1,NULL),
  (94,'manager',1,'topSubjectsBlock',0,0,0,NULL),
  (133,'teacher',1,'topSubjectsBlock',0,2,1,NULL),
  (131,'teacher',1,'lastNewsBlock',0,0,1,NULL),
  (129,'teacher',1,'scheduleDailyBlock',0,1,0,NULL),
  (130,'teacher',1,'activityBlock',0,2,0,NULL),
  (128,'teacher',1,'subjectsClassifiers',0,0,0,NULL),
  (134,'teacher',1,'screencastBlock',0,3,1,NULL);

INSERT INTO `list` (`kod`, `qtype`, `qdata`, `qtema`, `qmoder`, `adata`, `balmax`, `balmin`, `url`, `last`, `timelimit`, `weight`, `is_shuffled`, `created_by`, `timetoanswer`, `prepend_test`, `is_poll`) VALUES 
  ('1-1',6,'Выберите правильный вариант ответа','Тест',1,'',1,0,'',1322032291,NULL,'',0,1,0,'',0);

INSERT INTO `managers` (`id`, `mid`) VALUES 
  (1,1);

INSERT INTO `messages` (`message_id`, `from`, `to`, `subject`, `subject_id`, `message`, `created`) VALUES 
  (1,0,1,'',0,' ','2011-11-22 16:59:33');

INSERT INTO `news` (`id`, `created`, `author`, `created_by`, `announce`, `message`, `subject_name`, `subject_id`) VALUES 
  (1,'2011-11-23 00:50:34','Администратор Администратор ',1,'Работодатели отправляют выпускников вузов на дополнительное обучение','<p style=\"FONT-WEIGHT:bold\">Винить систему профессионального образования в том, что она продолжает действовать по инерции и плохо \"улавливает\" сигналы рынка, в последние годы стало чуть ли не правилом хорошего тона. Дескать, вузам не хватает информации о реальных потребностях экономики, поэтому многие обладатели дипломов работают не по своей специальности.</p>\r\n<p><br></p>\r\n<p><br></p>\r\n<p>Недавний опрос группы компаний <a href=\"http://hh.ru/\">HeadHunter </a>выявил новый тренд: уровень подготовки выпускников 2009 года ниже, чем у их коллег-выпускников, к примеру, 2006 или 1999 годов. 51% опрошенных социологами НR-менеджеров со всех регионов страны сознались, что их не устраивает уровень профессиональных знаний вчерашних студентов, а 68% работодателей недовольны уровнем их практических навыков. Участники опроса практически единодушны во мнении: молодым специалистам надо пройти дополнительное обучение. Непосредственно в компаниях на специальных тренингах они будут \"добирать\" профессиональные знания, а также развивать личностные качества и общий культурный уровень (недостаток которого, как считают эксперты, сегодня порождает низкий уровень клиентского обслуживания и креативности). Кстати, по словам создателя и первого руководителя корпоративного университета МТС, владельца компании \"<strong>Территория Тренинга</strong>\" Александра Зайцева, корпоративное обучение сегодня является абсолютно органической потребностью любого бизнеса, постоянно нуждающегося в развитии новых компетенций своего персонала и непременным требованием изменчивой рыночной среды.</p>\r\n<ol>\r\n<li>Работодатели&nbsp;</li>\r\n<li>отправляют <br></li>\r\n<li>выпускников <br></li></ol>','',0);

INSERT INTO `news2` (`nID`, `date`, `Title`, `author`, `message`, `lang`, `show`, `standalone`, `application`, `soid`, `type`) VALUES 
  (1,NULL,'о программе',NULL,'<p><em>«Проведение данной программы доказывает понимание нашей индустрией того факта, <br>что технологические достижения ничего не значат, если учителя не знают, <br>как их эффективно использовать. Чудеса творят не компьютеры, а учителя».</em> <br><br><em>Крейг Барретт,</em> <br><em>председатель совета директоров Intel</em></p>\r\n<div>&nbsp;</div>\r\n<div>&nbsp;</div>\r\n<div>«Intel® Обучение для будущего» - всемирная благотворительная программа профессионального развития учителей, которая на сегодня охватывает более 5 миллионов учителей в 40 странах мира, их число постоянно растет.<br><br>Программа призвана помочь учителям глубже освоить новейшие информационные и педагогические технологии, расширить их использование в повседневной работе с учащимися и при подготовке учебных материалов к урокам, в проектной работе и самостоятельных исследованиях школьников.<br><br>Ведущая идея Программы: эффективное комплексное использование информационных и образовательных технологий в классе с целью развития у учащихся ключевых компетентностей, основанных на ценностях, знаниях и умениях, необходимых человеку в 21 веке.<br><br><strong>Достижения Программы Intel ® «Обучение для будущего» в России (2002-2010 гг):</strong> <br>-&nbsp; с 2002 по декабрь 2010 года было обучено более 700 000 слушателей – школьных учителей, работников институтов повышения квалификации, администраторов образования, преподавателей и студентов педагогических колледжей и вузов (в том числе в 2010 году около 90 тысяч человек)<br>- в 2010 году программа проводилась на базе 125 обучающих центров (педагогические университеты и колледжи, ИПКРО, образовательные центры) в 80 регионах России. Каждый год к программе присоединяются новые партнеры.<br>- в июле 2005 г. компания Intel заключила соглашение с Министерством образования и науки РФ о поддержке проведения обучения учителей по программе в течение 5 лет.<br><br>Программа в России получила признание широкой педагогической общественности и считается одной из лучших по освоению педагогических технологий и ориентации на внедрение ИКТ в учебный процесс.</div>','',1,0,0,NULL,0);

INSERT INTO `notice` (`id`, `event`, `receiver`, `title`, `message`, `type`) VALUES 
  (1,'Создание новой учетной записи пользователя',0,'Вы зарегистрированы  в ИСДО',' ',1),
  (2,'Назначение роли пользователю',0,'Вам назначена роль [ROLE]',' ',2),
  (3,'Назначение на учебный курс (в процессе обучения)',0,'Вы назначены на обучение по курсу [COURSE]',' ',3),
  (4,'Назначение на электронный курс (в процессе разработки электронного курса)',0,'Вы назначены в группу  разработчиков электронного курса [URL_COURSE]',' ',4),
  (6,'Перевод в пользователя прошедшие обучение по курсу',0,'Вам назначена роль [ROLE]',' ',6),
  (7,'Подача заявки на обучение по курсу',1,'Новая заявка на обучение по курсу [URL_COURSE]',' ',7),
  (8,'Подача заявки на обучение по курсу',0,'Ваша заявка зарегистрирована',' ',8),
  (9,'Рассмотрение заявки на обучение по курсу: одобрение ',0,'Ваша заявка на обучение по курсу [URL_COURSE] одобрена',' ',9),
  (10,'Рассмотрение заявки на обучение по курсу: отклонение',0,'Ваша заявка на обучение по курсу [URL_COURSE] отклонена',' ',10),
  (11,'Смена пароля пользователя',0,'Подтверждение смены пароля ',' ',11),
  (12,'Новое личное сообщение',0,'[SUBJECT]','[TEXT]',12),
  (13,'Обновление источника подписки',0,'Подписка на источник [SOURCE]','[TEXT]',13),
  (14,'Опрос слушателей',0,'Опрос слушателей по курсу [URL_COURSE]','Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n',14),
  (15,'Опрос преподавателей',0,'Опрос преподавателей по курсу [URL_COURSE]','Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n',15),
  (16,'Опрос руководителей',0,'Опрос руководителей по курсу [URL_COURSE]','Приглашаем Вас пройти анкетирование по курсу [URL_COURSE]! Отзыв о курсе (сбор обратной связи) является обязательным этапом обучения сотрудников компании. Пройти анкетирование можно в СДО ([URL]) или по ссылке на опрос: [URL2]. Данные о мероприятии, по которому проводится сбор обратной связи: \n- Название опроса: [TITLE]\n- Даты проведения опроса: [BEGIN] - [END]\n- ФИО сотрудников, прошедших обучение: [SLAVES]\n',16);

INSERT INTO `organizations` (`oid`, `title`, `cid`, `root_ref`, `level`, `next_ref`, `prev_ref`, `mod_ref`, `status`, `vol1`, `vol2`, `metadata`, `module`) VALUES 
  (1,'<пустой элемент>',1,NULL,0,NULL,-1,NULL,NULL,NULL,NULL,NULL,0),
  (2,'Пустой элемент',2,NULL,0,NULL,-1,NULL,NULL,NULL,NULL,NULL,0);

INSERT INTO `permission_groups` (`pmid`, `name`, `default`, `type`, `rang`, `application`) VALUES 
  (1,'Методист','0','teacher',0,0);

INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES 
  (1,'ГиперМетод',NULL,NULL,NULL),
  (2,'SkillSoft',NULL,NULL,NULL);

INSERT INTO `quizzes` (`quiz_id`, `title`, `status`, `description`, `created`, `updated`, `created_by`, `questions`, `data`, `subject_id`, `location`) VALUES 
  (1,'Опрос1',0,'12345','2011-11-23 01:25:35','2011-11-23 01:25:35',1,0,'',0,1),
  (2,'Опрос2',1,'12345','2011-11-23 10:31:23','2011-11-23 10:31:23',1,0,'',0,1);

INSERT INTO `reports` (`report_id`, `domain`, `name`, `fields`, `created`, `created_by`, `status`) VALUES 
  (1,'StudyGeneral','Отчет успеваемости','a:6:{i:0;a:3:{s:5:\"field\";s:28:\"StudyGeneral.Person.personId\";s:5:\"title\";s:50:\"№ пользователя; Количество\";s:7:\"options\";a:4:{s:5:\"hiden\";s:1:\"0\";s:5:\"input\";s:1:\"0\";s:11:\"aggregation\";s:5:\"count\";s:5:\"title\";s:50:\"№ пользователя; Количество\";}}i:1;a:3:{s:5:\"field\";s:23:\"StudyGeneral.Person.fio\";s:5:\"title\";s:6:\"ФИО\";s:7:\"options\";a:3:{s:5:\"hiden\";s:1:\"0\";s:5:\"input\";s:1:\"0\";s:5:\"title\";s:6:\"ФИО\";}}i:2;a:3:{s:5:\"field\";s:30:\"StudyGeneral.Subject.subjectId\";s:5:\"title\";s:14:\"№ курса\";s:7:\"options\";a:3:{s:5:\"hiden\";s:1:\"0\";s:5:\"input\";s:1:\"0\";s:5:\"title\";s:14:\"№ курса\";}}i:3;a:3:{s:5:\"field\";s:33:\"StudyGeneral.Subject.subjectTitle\";s:5:\"title\";s:27:\"Название курса\";s:7:\"options\";a:3:{s:5:\"hiden\";s:1:\"0\";s:5:\"input\";s:1:\"0\";s:5:\"title\";s:27:\"Название курса\";}}i:4;a:3:{s:5:\"field\";s:43:\"StudyGeneral.Graduated.graduatedSubjectMark\";s:5:\"title\";s:51:\"Итоговая оценка; Выставлена\";s:7:\"options\";a:4:{s:5:\"hiden\";s:1:\"0\";s:5:\"input\";s:1:\"0\";s:8:\"function\";s:8:\"notempty\";s:5:\"title\";s:51:\"Итоговая оценка; Выставлена\";}}i:5;a:3:{s:5:\"field\";s:40:\"StudyGeneral.Graduated.certificateNumber\";s:5:\"title\";s:33:\"Номер сертификата\";s:7:\"options\";a:3:{s:5:\"hiden\";s:1:\"0\";s:5:\"input\";s:1:\"0\";s:5:\"title\";s:33:\"Номер сертификата\";}}}','2011-11-23 00:42:24',1,1);

INSERT INTO `resources` (`resource_id`, `title`, `url`, `volume`, `filename`, `type`, `filetype`, `description`, `content`, `created`, `updated`, `created_by`, `services`, `subject_id`, `status`, `location`) VALUES 
  (1,'Спрос и предложение',NULL,'35.59kB','talkinHead1.swf',0,6,'','','2011-11-25 11:28:19','2011-11-25 11:28:19',1,0,0,1,1);

INSERT INTO `rooms` (`rid`, `name`, `volume`, `status`, `type`, `description`) VALUES 
  (1,'demo',100,1,1,'');

INSERT INTO `schedule` (`SHEID`, `title`, `url`, `descript`, `begin`, `end`, `createID`, `typeID`, `vedomost`, `CID`, `CHID`, `startday`, `stopday`, `timetype`, `isgroup`, `cond_sheid`, `cond_mark`, `cond_progress`, `cond_avgbal`, `cond_sumbal`, `cond_operation`, `period`, `rid`, `teacher`, `gid`, `perm`, `pub`, `sharepointId`, `connectId`, `recommend`, `notice`, `notice_days`, `all`, `params`, `activities`, `order`) VALUES 
  (1,'Тест Демо',NULL,'','2011-11-23 00:00:00','2011-11-23 23:59:00',1,2048,1,1,NULL,0,0,2,'0','','','0','0','0',0,'-1',0,0,-1,0,0,0,'','0',0,0,'1','module_id=1;',NULL,0),
  (2,'Лекция 1',NULL,'','2011-11-25 11:30:19','2011-11-25 11:30:19',1,2052,1,1,NULL,0,0,2,'0','','','0','0','0',0,'-1',0,1,0,0,0,0,'','0',0,0,'1','module_id=1;','a:4:{i:0;s:1:\"2\";i:1;s:3:\"128\";i:2;s:3:\"512\";i:3;s:1:\"8\";}',0);

INSERT INTO `scheduleID` (`SSID`, `SHEID`, `MID`, `gid`, `isgroup`, `V_STATUS`, `V_DESCRIPTION`, `DESCR`, `SMSremind`, `ICQremind`, `EMAILremind`, `ISTUDremind`, `test_corr`, `test_wrong`, `test_date`, `test_answers`, `test_tries`, `toolParams`, `comments`, `chief`, `created`, `updated`) VALUES 
  (1,1,0,NULL,'0',-1,'',NULL,0,0,0,0,0,0,'0000-00-00 00:00:00',NULL,0,NULL,NULL,0,'2011-11-23 11:07:18','2011-11-23 11:07:18'),
  (2,1,1,NULL,'0',-1,'',NULL,0,0,0,0,0,0,'0000-00-00 00:00:00',NULL,0,NULL,NULL,0,'2011-11-23 11:07:18','2011-11-23 11:07:18'),
  (3,2,0,NULL,'0',-1,'',NULL,0,0,0,0,0,0,'0000-00-00 00:00:00',NULL,0,NULL,NULL,0,'2011-11-25 11:30:19','2011-11-25 11:30:19'),
  (4,2,1,NULL,'0',-1,'',NULL,0,0,0,0,0,0,'0000-00-00 00:00:00',NULL,0,NULL,NULL,0,'2011-11-25 11:30:19','2011-11-25 11:30:19');

INSERT INTO `session_guest` (`session_guest_id`, `start`, `stop`) VALUES 
  (1,'2011-11-21 12:50:23','2011-11-21 12:51:08'),
  (2,'2011-11-21 12:56:07','2011-11-21 12:56:07'),
  (3,'2011-11-21 13:22:00','2011-11-21 13:22:00'),
  (4,'2011-11-21 17:39:08','2011-11-21 17:41:43'),
  (5,'2011-11-21 18:26:16','2011-11-21 18:26:16'),
  (6,'2011-11-22 01:23:45','2011-11-22 01:23:45'),
  (7,'2011-11-22 10:34:40','2011-11-22 10:36:17'),
  (8,'2011-11-22 13:57:19','2011-11-22 13:57:19'),
  (9,'2011-11-22 14:01:21','2011-11-22 16:04:49'),
  (10,'2011-11-22 14:12:39','2011-11-22 14:12:39'),
  (11,'2011-11-22 14:56:43','2011-11-22 14:56:43'),
  (12,'2011-11-22 19:28:57','2011-11-22 19:28:57'),
  (13,'2011-11-23 06:01:18','2011-11-23 06:01:18'),
  (14,'2011-11-23 14:42:47','2011-11-23 14:42:47'),
  (15,'2011-11-23 14:43:39','2011-11-23 14:44:03'),
  (16,'2011-11-24 01:30:48','2011-11-24 01:30:48'),
  (17,'2011-11-24 09:11:16','2011-11-24 09:11:16'),
  (18,'2011-11-24 13:03:05','2011-11-24 13:03:05'),
  (19,'2011-11-24 14:35:00','2011-11-24 14:35:00'),
  (20,'2011-11-24 14:58:20','2011-11-24 14:58:20'),
  (21,'2011-11-24 18:52:22','2011-11-24 18:52:22'),
  (22,'2011-11-25 01:19:24','2011-11-25 01:19:24'),
  (23,'2011-11-25 08:08:48','2011-11-25 08:08:48'),
  (24,'2011-11-25 11:07:28','2011-11-25 11:07:28'),
  (25,'2011-11-25 10:55:14','2011-11-25 10:55:18'),
  (26,'2011-11-25 11:18:44','2011-11-25 11:20:56');

INSERT INTO `sessions` (`sessid`, `sesskey`, `mid`, `start`, `stop`, `ip`, `logout`) VALUES 
  (1,'',1,'2011-11-21 14:50:33','2011-11-21 14:51:03','109.188.128.156',0),
  (2,'',1,'2011-11-21 12:51:08','2011-11-21 14:56:10','109.188.128.156',0),
  (3,'',1,'2011-11-21 13:39:25','2011-11-21 15:39:33','109.188.128.156',0),
  (4,'',1,'2011-11-21 13:39:38','2011-11-21 15:40:34','109.188.128.156',0),
  (5,'',1,'2011-11-21 15:52:41','2011-11-21 17:52:53','109.188.128.156',0),
  (6,'',1,'2011-11-21 17:41:43','2011-11-21 20:24:06','213.87.135.190',0),
  (7,'',1,'2011-11-22 14:07:58','2011-11-22 16:08:04','109.188.128.156',0),
  (8,'',1,'2011-11-22 16:04:49','2011-11-22 19:06:02','217.74.47.124',0),
  (9,'',1,'2011-11-22 23:49:20','2011-11-23 03:35:29','213.87.130.17',0),
  (10,'',1,'2011-11-23 10:16:35','2011-11-23 13:12:07','217.74.47.124',0),
  (11,'',1,'2011-11-23 12:49:19','2011-11-23 14:52:06','217.74.47.124',0),
  (12,'',1,'2011-11-24 18:17:01','2011-11-24 20:17:10','109.188.128.156',0),
  (13,'',1,'2011-11-25 11:08:48','2011-11-25 13:09:32','109.188.128.156',0),
  (14,'',1,'2011-11-25 10:55:18','2011-11-25 10:58:14','109.188.128.156',0),
  (15,'',1,'2011-11-25 11:20:56','2011-11-25 11:31:57','80.254.52.34',0),
  (16,'',1,'2011-11-25 11:47:26','2011-11-25 11:47:47','80.254.52.34',0),
  (17,'',1,'2011-11-25 12:07:32','2011-11-25 12:08:50','80.254.52.34',0),
  (18,'',1,'2011-11-25 12:08:56','2011-11-25 12:19:31','80.254.52.34',0);

INSERT INTO `storage_filesystem` (`id`, `parent_id`, `subject_id`, `subject_name`, `name`, `alias`, `is_file`, `description`, `user_id`, `created`, `changed`) VALUES 
  (1,NULL,0,NULL,NULL,NULL,0,NULL,NULL,'2011-11-23 00:59:15','2011-11-23 00:59:15'),
  (2,1,0,NULL,'Личные папки','personal-folders',0,NULL,NULL,'2011-11-23 00:59:15','2011-11-23 00:59:15'),
  (3,2,0,NULL,NULL,NULL,0,NULL,1,'2011-11-23 00:59:15','2011-11-23 00:59:15'),
  (4,3,0,NULL,'Wildlife.wmv','Wildlife.wmv',1,NULL,1,'2011-11-23 01:04:25','2011-11-23 01:04:25');

INSERT INTO `subjects` (`subid`, `external_id`, `code`, `name`, `shortname`, `supplier_id`, `description`, `type`, `reg_type`, `begin`, `end`, `price`, `plan_users`, `services`, `period`,`created`, `last_updated`, `access_mode`, `access_elements`, `mode_free_limit`) VALUES
  (1,NULL,NULL,'Пример учебного курса',NULL,NULL,NULL,NULL,'0','2011-01-01','2021-01-01',NULL,NULL,0,0,'2011-11-25 11:30:19','2011-11-25 11:30:19',NULL,NULL,NULL),
  (2,NULL,'','Демонстрационный курс','',0,'','1','0','2011-11-01','2011-12-31',0,0,0,0,NULL,0,7,100);

INSERT INTO `subjects_courses` (`subject_id`, `course_id`) VALUES 
  (1,2);

INSERT INTO `subjects_resources` (`subject_id`, `resource_id`) VALUES 
  (1,1);

INSERT INTO `subjects_tasks` (`subject_id`, `task_id`) VALUES 
  (1,1);

INSERT INTO `subjects_tests` (`subject_id`, `test_id`) VALUES 
  (1,1);

INSERT INTO `tasks` (`task_id`, `title`, `status`, `description`, `created`, `updated`, `created_by`, `questions`, `data`, `subject_id`, `location`) VALUES 
  (1,'Задание Демо',0,'Задание Демо','2011-11-23 11:09:55','2011-11-23 11:11:31',1,1,'1-1',1,0);

INSERT INTO `test` (`tid`, `cid`, `cidowner`, `title`, `datatype`, `data`, `random`, `lim`, `qty`, `sort`, `free`, `skip`, `rating`, `status`, `questres`, `endres`, `showurl`, `showotvet`, `timelimit`, `startlimit`, `limitclean`, `last`, `lastmid`, `cache_qty`, `random_vars`, `allow_view_log`, `created_by`, `comments`, `mode`, `is_poll`, `poll_mid`, `test_id`, `lesson_id`, `type`) VALUES 
  (1,0,0,'Тест Демо',1,'',0,0,1,0,0,0,0,1,0,0,1,0,0,0,0,0,0,0,NULL,'1',0,NULL,0,0,0,1,1,0),
  (2,0,0,'Задание Демо',1,'',0,0,1,0,0,1,0,1,1,1,0,0,0,0,0,0,0,0,NULL,'0',0,'',0,0,0,1,0,3);

INSERT INTO `test_abstract` (`test_id`, `title`, `status`, `description`, `created`, `updated`, `created_by`, `questions`, `data`, `subject_id`, `location`) VALUES 
  (1,'Тест Демо',0,'Демо Тест','2011-11-23 11:07:18','2011-11-23 11:07:18',1,0,'',1,0);

INSERT INTO `user_login_log` (`login`, `date`, `event_type`, `status`, `comments`, `ip`) VALUES 
  ('admin','2011-11-21 12:51:08',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-21 13:39:25',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-21 13:39:34',1,1,'Пользователь успешно вышел из системы.',1841070236),
  ('admin','2011-11-21 13:39:38',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-21 15:52:34',0,0,'Вы неверно ввели имя пользователя или пароль.',1841070236),
  ('admin','2011-11-21 15:52:41',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-21 17:41:43',0,1,'Пользователь успешно авторизован.',2147483647),
  ('admin','2011-11-22 14:07:58',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-22 14:08:04',1,1,'Пользователь успешно вышел из системы.',1841070236),
  ('admin','2011-11-22 16:04:49',0,1,'Пользователь успешно авторизован.',2147483647),
  ('admin','2011-11-22 23:49:20',0,1,'Пользователь успешно авторизован.',2147483647),
  ('admin','2011-11-23 10:16:35',0,1,'Пользователь успешно авторизован.',2147483647),
  ('admin','2011-11-23 12:49:19',0,1,'Пользователь успешно авторизован.',2147483647),
  ('admin','2011-11-24 18:17:01',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-24 18:17:10',1,1,'Пользователь успешно вышел из системы.',1841070236),
  ('admin','2011-11-25 11:08:48',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-25 11:09:32',1,1,'Пользователь успешно вышел из системы.',1841070236),
  ('admin','2011-11-25 10:55:18',0,1,'Пользователь успешно авторизован.',1841070236),
  ('admin','2011-11-25 10:58:14',1,1,'Пользователь успешно вышел из системы.',1841070236),
  ('admin','2011-11-25 11:20:56',0,1,'Пользователь успешно авторизован.',1358836770),
  ('admin','2011-11-25 11:47:26',0,1,'Пользователь успешно авторизован.',1358836770),
  ('admin','2011-11-25 12:07:32',0,1,'Пользователь успешно авторизован.',1358836770),
  ('admin','2011-11-25 12:08:50',1,1,'Пользователь успешно вышел из системы.',1358836770),
  ('admin','2011-11-25 12:08:56',0,1,'Пользователь успешно авторизован.',1358836770);

INSERT INTO `webinars` (`webinar_id`, `name`, `create_date`, `subject_id`) VALUES 
  (1,'Демо вебинар','2011-11-23 12:52:00',1);

