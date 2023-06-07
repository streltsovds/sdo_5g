/* Параметры системы и другие настройки */

INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('version', '5.1');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('build', 'YYYYMMDD');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('enable_email', '0');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('windowTitle', 'eLearning Server 5G');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('headStructureUnitName', 'Организационная структура');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('grid_rows_per_page', 25);
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('maxUserEvents', 50);

INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('ideaDaysExpired', '30');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('ideaVoices2Support', '20');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('loginStart', '1');

INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('images_allowed_domains', '*');
INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('ext_pages_videos_allowed_domains', '*');

/*INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('facebook', 'https://www.facebook.com/hypermethod.ru/');*/
/*INSERT INTO `OPTIONS` (`name`, `value`) VALUES ('youtube', 'https://www.youtube.com/user/MrHyperMethod/');*/


/* Дефолтная учётная запись и её роли*/

INSERT INTO People (MID, LastName, FirstName, Password, Login) VALUES (1, 'Администратор', 'Администратор', PASSWORD('pass'), 'admin');

INSERT INTO admins (AID, MID) VALUES
  (1,1);

INSERT INTO deans (DID, MID) VALUES
  (1,1);

INSERT INTO at_managers (user_id) VALUES
  (1);


/* Пример курса */

INSERT INTO `subjects` (`subid`, `name`, `type`, `reg_type`, `begin`, `end`) VALUES (1, 'Пример учебного курса', 1, 0, '2011-01-01', '2021-01-01');


/* Разные справочники (где есть хардкод ID в моделях)*/

INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (1, 'ГиперМетод', NULL, NULL, NULL);
INSERT INTO `providers` (`id`, `title`, `address`, `contacts`, `description`) VALUES (2, 'SkillSoft', NULL, NULL, NULL);

INSERT INTO recruit_providers (provider_id, name, status, locked) VALUES
	(1, 'Персонал', 'actual', 1),
	(2, 'HeadHunter', 'actual', 1),
	(3, 'SuperJob', 'actual', 1),
	(4, 'E-Staff', 'actual', 1),
	(5, 'excel', 'not_actual', 1),
	(6, 'other', 'not_actual', 1);

INSERT INTO tc_providers
  (`name`, `status`, `type`, `created_by`, `department_id`, `dzo_id`, `pass_by`, `prefix_id`)
VALUES
  ('Внутреннее обучение компании', 1, 0, 1, 0, 0, 0, 0);


INSERT INTO scales (scale_id, name, description, `type`, mode) VALUES (1, 'Значения от 0 до 100', 'Любые значения в диапазоне от 0 до 100', 1, 0);
INSERT INTO scales (scale_id, name, description, `type`, mode) VALUES (2, '2 состояния', 'Пройдено / Не пройдено', 2, 0);
INSERT INTO scales (scale_id, name, description, `type`, mode) VALUES (3, '3 состояния', 'Пройдено успешно / Пройдено неуспешно / Не пройдено', 3, 0);


INSERT INTO `classifiers_types` (`type_id`, `name`, `link_types`) VALUES (6, 'Направления обучения', '0');
INSERT INTO `classifiers_types` (`type_id`, `name`, `link_types`) VALUES (7, 'Виды деятельности для виджета `Учёт рабочего времени`', '0');

INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (5, 6, 0, 6, 'Производственное обучение', NULL);
INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (7, 8, 0, 6, 'Охраны труда, промышленной безопасности и охраны окружающей среды', NULL);
INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (9, 10, 0, 6, 'Повышение квалификации', NULL);
INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (11, 12, 0, 6, 'Корпоративное обучение', NULL);

INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (13, 14, 0, 7, 'Производственные совещания', NULL);
INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (15, 16, 0, 7, 'Работа с документами', NULL);
INSERT INTO `classifiers` (`lft`, `rgt`, `level`, `type`, `name`, `classifier_id_external`) VALUES (17, 18, 0, 7, 'Непроизводственное время', NULL);


/* Форум на уровне Портала*/

INSERT INTO forums_list (forum_id, subject_id, user_id, user_name, user_ip, title, flags) VALUES (1, 0, 1, 'Администратор', '127.0.0.1', 'Форум портала', 6);
INSERT INTO forums_sections (section_id, lesson_id, subject, forum_id, parent_id, user_id, user_name, user_ip, title, flags) VALUES (1, 0, 'subject', 1, 0, 1, 'Администратор', '127.0.0.1', 'Общие вопросы', 2);

  /* Шаблоны системных сообщений */

INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (1, 'general', 'Создание новой учетной записи пользователя', 0, 'Вы зарегистрированы  в ИСДО', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (2, 'general', 'Назначение роли пользователю', 0, 'Вам назначена роль [ROLE]', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (3, 'elearning', 'Назначение на учебный курс', 0, 'Вы назначены на обучение по курсу "[COURSE]"', '<p>Уважаемый коллега!</p><p>Вы назначены на обучение по курсу "[COURSE]" на Портале развития персонала.</p><p>При возникновении вопросов по использованию ПРП обращайтесь:</p><ul><li></li><li></li></ul><p> </p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (6, 'elearning', 'Перевод пользователя в прошедшие обучение по курсу', 0, 'Вы успешно прошли курс "[COURSE]" в системе дистанционного обучения [URL]. Ссылка на сертификат: [CERTIFICATE_LINK].', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (7, 'elearning', 'Подача заявки на обучение по курсу', 1, 'Новый запрос на обучение по курсу "[COURSE]"', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (8, 'elearning', 'Подача заявки на обучение по курсу', 0, 'Ваша заявка зарегистрирована', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (9, 'elearning', 'Рассмотрение заявки на обучение по курсу: одобрение', 0, 'Ваша заявка на обучение по курсу "[COURSE]" одобрена', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (10, 'elearning', 'Рассмотрение заявки на обучение по курсу: отклонение', 0, 'Ваша заявка на обучение по курсу "[COURSE]" отклонена', ' ', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (11, 'general', 'Смена пароля пользователя', 0, 'Подтверждение смены пароля', '<p>Ваш пароль изменён. Новый пароль: [PASSWORD]</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (12, 'activities', 'Новое личное сообщение', 0, '[SUBJECT]', '[TEXT]', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (14, 'activities', 'Опрос пользователей', 0, 'Опрос пользователей', 'Вам назначено мероприятие по сбору обратной связи. Пожалуйста, пройдите по ссылке [URL] и заполните анкеты, приведенные в блоке "Обратная связь".', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (16, 'activities', 'Опрос руководителей', 0, 'Опрос для руководителя', 'Опрос пользователей'', ''Вам назначено мероприятие по сбору обратной связи. Пожалуйста, пройдите по ссылке [URL], переключитесь в "Кабинет руководителя" и заполните анкеты, приведенные в блоке "Обратная связь".', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (21, 'general', 'Подтверждение email', 0, 'Подтвердите email', 'Для завершения регистрации и необходимо подтвердить Ваш email. Перейдите по ссылке: [EMAIL_CONFIRM_URL]', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (22, 'general', 'Учётная запись разблокирована', 0, 'Учётная запись разблокирована', 'Ваша учетная запись была разблокирована. Для входа на портал, перейдите по ссылке: [URL]', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (23, 'general', 'Ответ на запрос в техподдержку', 0, 'Вы получили ответ на запрос № [ID]', 'Вы получили ответ на запрос № [ID].  Тема заявки: [TITLE]  ФИО отправителя: [LFNAME]  Описание проблемы и желаемый результат: [REQUEST]  Ответ админитратора: [RESPONSE]  Статус заявки: [STATUS]', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (24, 'general', 'Новый запрос / измемение статуса запроса в техподдержку', 0, 'Статус вашего запроса № [ID] изменен на "[STATUS]"', 'Статус вашего запроса № [ID] изменен на "[STATUS]".  Тема запроса: [TITLE]  ФИО отправителя: [LFNAME]', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (25, 'general', 'Новый запрос в техподджерку (для администратора)', 1, 'Новый запрос № [ID]', 'Создан новый запрос № [ID].  Тема запроса: [TITLE]  ФИО отправителя: [LFNAME]  Описание проблемы и желаемый результат: [REQUEST] Статус запроса: [STATUS]', 1, 1);

INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (33, 'elearning', 'Назначение на учебную сессию', 0, 'Вы назначены на обучение по учебной сессии "[COURSE]"', '<p>Вы назначены на обучение по учебной сессии "[COURSE]".</p><p>Дата: [DATE_BEGIN]</p><p>Пройдите по ссылке: [URL]</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (34, 'elearning', 'Назначение пользователя на занятие', 0, 'Вам назначено занятие "[LESSON]" в курсе "[COURSE]"', '<p>Уважаемый коллега!</p><p>Вам назначено занятие "[LESSON]" в курсе "[COURSE]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (35, 'elearning', 'Выставление оценки за курс', 0, 'Вам выставлена оценка "[MARK]" за курс "[COURSE]"', '<p>Уважаемый коллега!</p><p>Вам выставлена оценка "[MARK]" за курс "[COURSE]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (36, 'elearning', 'Выставление оценки за занятие', 0, 'Вам выставлена оценка "[MARK]" за занятие "[LESSON]"', '<p>Уважаемый коллега!</p><p>Вам выставлена оценка "[MARK]" за занятие "[LESSON]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (37, 'elearning', 'Выполнение задания студентом', 0, 'Cтудент [FIO] выполнил задание "[LESSON]" в курсе "[COURSE]"', '<p>Cтудент [FIO] выполнил задание "[LESSON]" в курсе "[COURSE]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (38, 'elearning', 'Вопрос студента в задании', 0, 'Cтудент [FIO] задал вопрос в задании "[LESSON]" в курсе "[COURSE]"', '<p>Cтудент [FIO] задал вопрос в задании "[LESSON]" в курсе "[COURSE]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (39, 'elearning', 'Ответ преподавателя в задании', 0, 'Преподаватель ответил на вопрос в задании "[LESSON]" в курсе "[COURSE]"', '<p>Преподаватель ответил на вопрос в задании "[LESSON]" в курсе "[COURSE]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (40, 'elearning', 'Новые требования преподавателя в задании', 0, 'Преподаватель сформулировал новые требования в задании "[LESSON]" в курсе "[COURSE]"', '<p>Преподаватель сформулировал новые требования в задании "[LESSON]" в курсе "[COURSE]"</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (26, 'general', 'Восстановление пароля пользователя', 0, 'Ссылка для изменения пароля', '<p>Для изменения пароля перейдите по ссылке: [RECOVERY_LINK]<br>Обратите внимание: срок действия ссылки ограничен.</p>', 1, 1);


/************************ Хардкод для модуля Адаптация ************************/

/*
INSERT INTO programm (programm_id, programm_type, item_id, item_type, mode_strict, mode_finalize, name) VALUES
  (10,3,0,0,0,0,'Программа адаптации');

INSERT INTO programm_events (programm_event_id, programm_id, name, type, item_id, day_begin, day_end, ordr) VALUES
  (5,10,'Оценка выполнения задач руководителем',0,5,1,1,0),
  (6,10,'Итоговая форма программы адаптации',0,6,2,2,1);

INSERT INTO at_evaluation_type (evaluation_type_id, name, comment, scale_id, category_id, profile_id, vacancy_id, newcomer_id, method, submethod, methodData, relation_type, programm_type) VALUES
  (5,'Оценка выполнения задач руководителем',NULL,0,0,NULL,NULL,NULL,'kpi','kpi_180',NULL,180,3),
  (6,'Итоговая форма программы адаптации',NULL,0,0,NULL,NULL,NULL,'finalize','finalize_3',NULL,NULL,3);

INSERT INTO questionnaires (quest_id, type, name, description, status) VALUES
  (1,'form','Итоговая форма программы адаптации','',1);

INSERT INTO quest_clusters (cluster_id, quest_id, name) VALUES
  (1,1,'Результаты прохождения программы адаптации');

INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (1,'single','form','<p>Результат прохождения программы адаптации</p>','Результат прохождения программы адаптации',0,0);
INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (2,'free','form','<p>Продлить адаптацию до (дд.мм.гггг)</p>','Продлить адаптацию до (дд.мм.гггг)',0,0);
INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (3,'free','form','<p>Предложения по дальнейшей работе</p>','Предложения по дальнейшей работе',0,0);

INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (1,1,'Кандидат успешно прошёл программу адаптации',NULL,NULL,0,0,0);
INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (2,1,'Кандидат не прошёл программу адаптации',NULL,NULL,0,0,0);
INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (3,1,'Необходимо продлить срок адаптации',NULL,NULL,0,0,0);

INSERT INTO quest_question_quests (question_id, quest_id, cluster_id) VALUES
  (1,1,1),
  (2,1,1),
  (3,1,1);

INSERT INTO feedback (feedback_id, subject_id, user_id, quest_id, status, date_finished, name, respondent_type, assign_type, assign_days, assign_new, assign_anonymous, assign_anonymous_hash) VALUES
	(1, 0, NULL, 4, 0, NULL, 'Обратная связь по итогам welcome-тренинга', 0, 1, 0, NULL, 0, NULL);
INSERT INTO feedback (feedback_id, subject_id, user_id, quest_id, status, date_finished, name, respondent_type, assign_type, assign_days, assign_new, assign_anonymous, assign_anonymous_hash) VALUES
	(2, 0, NULL, 5, 0, NULL, 'Обратная связь по итогам прохождения испытательного срока', 0, 1, 0, NULL, 0, NULL);

INSERT INTO questionnaires (quest_id, `type`, name, description, status) VALUES
  (4,'poll','Обратная связь по итогам welcome-тренинга','',1),
  (5,'poll','Обратная связь по итогам прохождения испытательного срока','',1);

INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (111, 'adaptation', 'Уведомление о welcome-тренинге для пользователя', 0, 'Адаптационный семинар `Добро пожаловать в Компанию`', '<p><span style=`font-size:small;`>Добрый день, </span><span style=`font-size:small;`>[NAME_PATRONYMIC]</span><span style=`font-size:small;`>.</span></p><p><span style=`font-size:small;`>Приглашаем  Вас  посетить адаптационный семинар «Добро пожаловать в Компанию», который состоится<strong> </strong><span style=`font-size:small;`>[DATE]</span> в 16:00 [PLACE].</span></p><p><span style=`font-size:small;`>                Во время тренинга мы расскажем:</span></p><p><span style=`font-size:small;`>- о нашей компании, ее истории, структуре и достижениях;</span></p><p><span style=`font-size:small;`>- об офисе, рабочем месте, корпоративной культуре, этике и ценностях;</span></p><p><span style=`font-size:small;`>- о том, что нужно знать, приступая к работе; на что обратить внимание во время рабочего процесса;</span></p><p><span style=`font-size:small;`>- о том, что  ждет в компании; какие возможности и перспективы открываются перед нашими пользователями.</span></p><p><span style=`font-size:small;`> </span></p><p><span style=`font-size:small;`>Просим Вас подтвердить  свое присутствие обратным письмом.</span></p><p><span style=`font-size:small;`> </span></p><p><span style=`font-size:small;`>Заранее спасибо.</span></p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (112, 'adaptation', 'Уведомление о welcome-тренинге для руководителя', 2, 'Приглашение на адаптационный семинар `Добро пожаловать` для нового пользователя', '<p>Добрый день, <span>[NAME_PATRONYMIC]</span>.</p><p>Приглашаем  Вашего нового пользователя   посетить адаптационный семинар «Добро пожаловать в Компанию», который состоится [DATE]  2018 г. в 16:00 в [PLACE].</p><p>                Во время тренинга мы расскажем:</p><p>- о нашей компании, ее истории, структуре и достижениях;</p><p>- об офисе, рабочем месте, корпоративной культуре, этике и ценностях;</p><p>- о том, что нужно знать, приступая к работе; на что обратить внимание во время рабочего процесса;</p><p>- о том, что  ждет в компании; какие возможности и перспективы открываются перед нашими пользователями.</p><p> </p><p>Просим Вас подтвердить  свое присутствие обратным письмом.</p><p> </p><p>Заранее спасибо.</p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (210, 'adaptation', 'Уведомление о необходимости составления плана адаптации', 2, 'Необходимо составить план адаптации', '<p>Уважаемый [NAME_PATRONYMIC]!</p><p>Информируем Вас о необходимости составления плана адаптации на испытательный срок для нового сотрудника: [FIO_NEWCOMER].</p><p> </p><p>Для составления плана перейдите по ссылке – [URL].</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (353, 'adaptation', 'Уведомление об утвержденном плане адаптации', 0, 'Утвержден план адаптации', '<p>Уважаемый [NAME_PATRONYMIC]!</p><p>Ваш план адаптации на испытательный срок утверждён.</p><p> </p><p>Для просмотра перейдите по ссылке – [URL].</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (211, 'adaptation', 'Уведомление о необходимости оценки выполнения плана адаптации', 2, 'Необходимо оценить выполнение плана адаптации', '<p>Уважаемый [NAME_PATRONYMIC]!</p> [FIO_ADAPT] [URL]', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (351, 'adaptation', 'Уведомление руководителя подразделения о сессии адаптации пользователя', 2, 'Уведомление о сессии адаптации пользователя', '<p>Уважаемый(ая) [NAME_PATRONYMIC]!<br>В Вашем подразделении следующие пользователи проходят сессии адаптации: <br>[LIST].</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (352, 'adaptation', 'Уведомление куратора о сессии адаптации пользователя', 3, 'Уведомление о сессии адаптации пользователя', '<p>Уважаемый(ая) [NAME_PATRONYMIC]!<br>Следующие пользователи проходят сессии адаптации: <br>[LIST].</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (1000, 'adaptation', 'Дополнительное уведомление сотрудника о сессии адаптации (пустой шаблон)', 0, '', '', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (212, 'adaptation', 'Сессия адаптации завершена', 0, 'Завершение сессии адаптации', '<p>Уважаемый [FIO]!</p>\r\n<p>Информируем Вас о необходимости предоставить обратную связь по результатам прохождения испытательного срока.</p>\r\n<p>Для предоставления обратной связи необходимо:</p>\r\n<ul><li>перейти по ссылке на портал обучения [URL]</li>\r\n<li>ввести логин и пароль</li>\r\n<li>пройти опрос по теме `Обратная связь по итогам прохождения испытательного срока` в разделе `Обратная связь`</li>\r\n</ul>', 1, 1);

*/


/************************ Хардкод для модуля регулярной оценки ************************/

/*
INSERT INTO programm (programm_id, programm_type, item_id, item_type, mode_strict, mode_finalize, name) VALUES
  (1,1,0,0,0,0,'Программа регулярной оценки');

INSERT INTO programm_events (programm_event_id, programm_id, name, type, item_id, day_begin, day_end, ordr) VALUES
  (1,1,'360&deg; Самооценка',0,1,1,1,0),
  (2,1,'360&deg; Оценка подчиненными',0,2,1,1,1),
  (3,1,'360&deg; Оценка коллегами',0,3,1,1,2),
  (4,1,'360&deg; Оценка руководителем',0,4,1,1,3);

INSERT INTO at_evaluation_type (evaluation_type_id, name, comment, scale_id, category_id, profile_id, vacancy_id, newcomer_id, method, submethod, methodData, relation_type, programm_type) VALUES
  (1,'360&deg; Самооценка',NULL,10,0,NULL,NULL,NULL,'competence','competence_90',NULL,90,1),
  (2,'360&deg; Оценка подчиненными',NULL,10,0,NULL,NULL,NULL,'competence','competence_360',NULL,360,1),
  (3,'360&deg; Оценка коллегами',NULL,10,0,NULL,NULL,NULL,'competence','competence_270',NULL,270,1),
  (4,'360&deg; Оценка руководителем',NULL,10,0,NULL,NULL,NULL,'competence','competence_180',NULL,180,1);
*/
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (991, 'assessment', 'Назначение сессии оценки персонала', 0, 'Вы принимаете участие в сессии оценки персонала', '<p>Уважаемый коллега!</p><p>Вы принимаете участие в сессии регулярной оценки персонала. </p><p>Анкеты необходимо заполнить в срок до [END]. </p><p>Обращаем Ваше внимание, что анкетирование проводится анонимно. Результаты будут представлены в обобщенном виде.</p><p>Менеджер по оценке: [CONTACTS]</p><p> </p><p>Пройдите по ссылке: [URL_SESSION]</p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);


/************************ Хардкод для модуля подбора ************************/
/*
INSERT INTO questionnaires (quest_id, type, name, description, status) VALUES
  (2,'form','Итоговая форма программы подбора','',1);

INSERT INTO quest_clusters (cluster_id, quest_id, name) VALUES
  (2,2,'Результаты прохождения программы подбора');

INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (4,'single','form','<p>Результат прохождения программы подбора</p>','Результат прохождения программы подбора',0,0);
INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (8,'free','form','<p>Предложения по дальнейшей работе с данным кандидатом</p>','Предложения по дальнейшей работе с данным кандидатом',0,0);
  INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (5,'reservepositions','form','<p>Кандидат подтвердил согласие на участие в программе кадрового резерва на должность:</p>','Кандидат подтвердил согласие на участие в программе кадрового резерва на должность',0,0);

INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (4,4,'Кандидат прошёл отбор и рекомендован к зачислению в должность',NULL,NULL,0,0,0);
INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (5,4,'Кандидат не прошёл отбор, включен в кадровый резерв',NULL,NULL,0,0,0);
INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (6,4,'Кандидат не прошёл отбор, включен в чёрный список',NULL,NULL,0,0,0);
INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (7,4,'Кандидат не прошёл отбор',NULL,NULL,0,0,0);

INSERT INTO quest_question_quests (question_id, quest_id, cluster_id) VALUES
  (4,2,2),
  (5,2,2),
  (8,2,2);

INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (104, 'recruiting', 'Уведомление о назначенном мероприятии в сессии подбора', 0, 'Вакансия `[VACANCY]`: назначено мероприятие', '<p>Уважаемый [NAME]!</p><p>В рамках программы подбора `[VACANCY]` запланировано мероприятие [EVENT], дата - [DATE].</p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (221, 'recruiting', 'Уведомление об отправка резюме инициатору', 0, 'Новые резюме', '<p>Уважаемый [INITIATOR_FIRSTNAME] [INITIATOR_PATRONYMIC]!</p><p>По Вашей заявке на вакансию [VACANCY] поступили следующие резюме: [CANDIDATES_LIST]</p><p>Рекрутер: [RECRUITER]</p>', 1, 1);

*/

/************************ Хардкод для модуля кадрового резерва ************************/

/*
INSERT INTO questionnaires (quest_id, type, name, description, status) VALUES
  (3,'form','Итоговая форма программы развития кадрового резерва','',1);

INSERT INTO quest_clusters (cluster_id, quest_id, name) VALUES
  (3,3,'Результаты программы развития');

INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (6,'single','form','<p>Решение по итогам участия в программе кадрового резерва</p>','Результат прохождения программы развития',0,0);
INSERT INTO quest_questions (question_id, type, quest_type, question, shorttext, mode_scoring, show_free_variant) VALUES
  (7,'free','form','<p>Предложения по дальнейшей работе</p>','Предложения по дальнейшей работе',0,0);

INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (8,6,'Сохранить статус резервиса',NULL,NULL,0,0,0);
INSERT INTO quest_question_variants (question_variant_id, question_id, variant, shorttext, file_id, is_correct, category_id, weight) VALUES
  (9,6,'Исключить из состава кадрового резерва',NULL,NULL,0,0,0);

INSERT INTO quest_question_quests (question_id, quest_id, cluster_id) VALUES
  (6,3,3),
  (7,3,3);

INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (215, 'reserve', 'Уведомление о заполнении ИПР', 0, 'Уведомление о заполнении ИПР', '<p>Уважаемый [NAME]!</p><p> </p><p>В рамках программы кадрового резерва Компании Вам необходимо в срок до [FILL_PLAN_DATE]/nсоставить Индивидуальный план развития.</p><p> </p><p>Шаблон ИПР можно скачать на странице: [URL].</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (216, 'reserve', 'Уведомление о предоставлении отчёта о прохождении ИПР', 0, 'Уведомление о предоставлении отчёта о прохождении ИПР', '<p>Уважаемый [NAME]!</p>/n/n<p>В рамках программы кадрового резерва Компании Вам необходимо в срок до [REPORT_DATE]/nсоставить отчёт о выполнении ИПР.</p>/n/n<p>Шаблон отчёта о выполнении ИПР можно скачать на странице: [URL].</p>', 1, 1);
*/


/************************ Хардкод для модуля планирования обучения ************************/

/*
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (105, 'planing', 'Уведомление о сессии планирования для руководителей подразделений', 0, 'Планирование обучения и развития сотрудников', '<p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Уважаемый коллега!</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Приглашаем Вас принять участие в сессии планирования обучения и развития сотрудников на период [PERIOD].</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Для начала работы Вам необходимо перейти по ссылке [URL_SESSION]</span> <span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>в личный кабинет.</span></p><p><span style=`font-family:''Times New Roman'';font-size:medium;`>Далее следуйте инструкции, полученной от сотрудников Службы обучения и развития. </span></p><p><span style=`font-family:''Times New Roman'';font-size:medium;`>Сессия планирования продлится до [PLAN_DATE_END].</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Заранее благодарим Вас за вовлеченность в процесс.</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`> </span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Авторизация в системе происходит автоматически.</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Если этого не произошло или Вы столкнулись с другой проблемой технического характера, сообщите об этом в службу Технической поддержки.</span></p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (106, 'planing', 'Уведомление о запланированном обучении для специалиста', 0, 'Запланированно обучение', '<p>Уважаемый специалист, добрый день. Руководитель подразделения [DEP_ORG] запланировал обучение для своих сотрудников.</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (108, 'planing', 'Уведомление об удалении персональной заявки', 0, 'Заявка удалена', 'Уважаемый руководитель ваша заявка по сотруднику [USER_NAME] на курс [SUBJECT_NAME] удалена', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (109, 'planing', 'Уведомление о редактировании персональной заявки', 0, 'Заявка отредактирована', '<p>Уважаемый руководитель ваша заявка по сотруднику [USER_NAME] на курс [SUBJECT_NAME] отредактирована</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (110, 'planing', 'Уведомление для специалиста об обязательном обучении', 0, 'Запланированно обязательное обучение', '<p>Уважаемый специалист! Просим проверить достоверность данных по обязательному обучению для сотрудников в вашей зоне ответственности. Ссылка на отчет по обязательному обучению: <a href=`%5BREPORT_URL%5D`>[REPORT_URL]</a></p>', 1, 1);

INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (301, 'planing', 'Уведомление о сессии квартального планирования для руководителей подразделений', 0, 'Планирование обучения и развития сотрудников', '<p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Уважаемый коллега!</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Приглашаем Вас принять участие в сессии планирования обучения и развития сотрудников на [PERIOD].</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Для начала работы Вам необходимо перейти по ссылке </span><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>в личный кабинет [URL_SESSION].</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Далее следуйте инструкции, полученной от сотрудников Службы обучения и развития. </span></p><p><span style=`font-family:''Times New Roman'';font-size:medium;`>Сессия планирования продлится до [PLAN_DATE_END].</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Заранее благодарим Вас за вовлеченность в процесс.</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`> </span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Авторизация в системе происходит автоматически.</span></p><p><span style=`color:#000000;font-family:''Times New Roman'';font-size:medium;`>Если этого не произошло или Вы столкнулись с другой проблемой технического характера, сообщите об этом в службу Технической поддержки.</span></p><p>---</p><p>С уважением,</p><p>управление развития персонала</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (302, 'planing', 'Уведомление о назначенном обучении на внешнем курсе', 0, 'Вам назначено обучение на внешнем курсе [COURSE]', '[COURSE][BEGIN][END][INFO]', 1, 1);

*/

/************************ Хардкод для модуля ротации ************************/

/*
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (213, 'rotation', 'Уведомление о заполнении плана ротации', 0, 'Уведомление о заполнении плана ротации', '<p>Уважаемый(-ая) [NAME]!</p>\n<p>В рамках процедуры ротации в период с [BEGIN_DATE] по [END_DATE] Вы будете работать в должности [ROTATION_POSITION] в подразделении [ROTATION_DEPARTMENT], руководитель - [ROTATION_MANAGER].</p>/n<p>Индивидуальный план ротации необходимо заполнить до [FILL_PLAN_DATE].</p>\n<p>Ссылка на сессию ротации: [URL].</p>', 1, 1);
INSERT INTO `notice` (`type`, `cluster`, `event`, `receiver`, `title`, `message`, `enabled`, `priority`) VALUES (214, 'rotation', 'Уведомление о предоставлении отчёта о ротации', 0, 'Уведомление о предоставлении отчёта о ротации', '<p>Уважаемый(-ая) [NAME]!</p>\n<p>В рамках процедуры ротации в период с [BEGIN_DATE] по [END_DATE] Вы работали в должности [ROTATION_POSITION] в подразделении [ROTATION_DEPARTMENT].</p>/n<p>Вам необходимо заполнить отчёт по итогам ротации и сдать его до [REPORT_DATE].</p>\n<p>Ссылка на сессию ротации: [URL].</p>', 1, 1);
*/
