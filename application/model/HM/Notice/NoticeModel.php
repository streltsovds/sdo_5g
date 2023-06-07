<?php

class HM_Notice_NoticeModel extends HM_Model_Abstract
{
    const USER = 0;
    const ADMIN = 1;
    const MANAGER = 2;
    const CURATOR = 3;

    const CLUSTER_GENERAL = 'general';
    const CLUSTER_RECRUITING = 'recruiting';
    const CLUSTER_ADAPTATION = 'adaptation';
    const CLUSTER_PLANING = 'planing';
    const CLUSTER_ELEANING = 'elearning';
    const CLUSTER_LABOR_SAFETY = 'labor-safety';
    const CLUSTER_PROJECTS = 'projects';
    const CLUSTER_ASSESSMENT = 'assessment';
    const CLUSTER_RESERVE = 'reserve';
    const CLUSTER_ROTATION = 'rotation';
    const CLUSTER_ACTIVITIES = 'activities';

    const TEMPLATE_SENDALL = 17;

    static public function getReceivers()
    {
        return [
            self::USER => _('Пользователь'),
            self::MANAGER => _('Руководитель'),
            self::CURATOR => _('Куратор'),
            self::ADMIN => _('Администрация')
        ];
    }

    static public function getReceiver($id)
    {
        $receivers = self::getReceivers();
        return isset($receivers[$id]) ? $receivers[$id] : '';
    }

    static public function getClusters()
    {
        $clusters = [
            self::CLUSTER_GENERAL => _('Общего назначения'),
            self::CLUSTER_RECRUITING => _('Подбор'),
            self::CLUSTER_ADAPTATION => _('Адаптация'),
            self::CLUSTER_PLANING => _('Планирование обучения'),
            self::CLUSTER_ELEANING => _('Обучение'),
            self::CLUSTER_LABOR_SAFETY => _('Обучение (охрана труда)'),
            self::CLUSTER_PROJECTS => _('Конкурсы'),
            self::CLUSTER_ASSESSMENT => _('Оценка'),
            self::CLUSTER_RESERVE => _('Кадровый резерв'),
            self::CLUSTER_ROTATION => _('Ротация'),
            self::CLUSTER_ACTIVITIES => _('Взаимодействие'),
        ];

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_NOTICE_CLUSTERS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $clusters);

        return $event->getReturnValue();
    }

    static public function getDescription($id)
    {
        if ($variables = self::getVariables($id)) {
            array_walk($variables, function (&$value, $key) {
                $value = sprintf('<li>[%s] - %s; </li>', strtoupper($key), $value);
            });
            return sprintf(_('В тексте возможны следующие переменные: <br>%s'), implode($variables));
        }
        return false;
    }

    static public function getVariables($id)
    {
        // Шаблоны, в которых скрываем дефолтные переменные
        $excludedDefaultsTemplates = [
            HM_Messenger::TEMPLATE_ORDER,
        ];
        $defaultVariables = [
            'url' => _('ссылка на главную страницу Системы'),
            'name' => _('имя/отчество адресата (пользователя)'),
            'signature' => _('стандартная подпись отправителя'),
        ];
        $customVariables = [];
        switch ($id) {
            case HM_Messenger::TEMPLATE_REG:
            case HM_Messenger::TEMPLATE_PASS:
                $customVariables = [
                    'login' => _('логин'),
                    'password' => _('пароль')
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_ROLE:
                $customVariables = [
                    'role' => _('назначенная роль'),
                    'url_manual' => _('ссылка на руководство для роли')
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_SUBJECT:
                $customVariables = [
                    'course' => _('ссылка на назначенный курс')
                    // есть доп. переменные, но они могут не всегда передаваться
                ];
                break;
            case HM_Messenger::TEMPLATE_GRADUATED:
                $customVariables = [
                    //'role' => _('назначенная роль'),
                    'course' => _('ссылка на назначенный курс'),
                    'grade' => _('оценка'),
                    'certificate_link' => _('ссылка на сертификат')
                ];
                break;
            case HM_Messenger::TEMPLATE_ORDER:
                $customVariables = [
                    'course' => _('ссылка на курс'),
                    //'user_login' => _('логин пользователя'),
                    //'user_lastname' => _('фамилия пользователя'),
                    'user_name' => _('ФИО пользователя'),
                    'user_mail' => _('email пользователя'),
                    'user_phone' => _('телефон пользователя'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ORDER_REGGED:
                $customVariables = [
                    'course' => _('ссылка на курс'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ORDER_ACCEPTED:
                $customVariables = [
                    'course' => _('ссылка на курс')
                ];
                break;
            case HM_Messenger::TEMPLATE_ORDER_REJECTED:
                $customVariables = [
                    'course' => _('ссылка на курс'),
                    'comment' => _('комментарий')
                ];
                break;
            case HM_Messenger::TEMPLATE_PRIVATE:
                $customVariables = [
                    'subject' => _('тема сообщения'),
                    'text' => _('текст сообщения'),
                ];
                break;
            case HM_Messenger::TEMPLATE_POLL_STUDENTS:
                $customVariables = [
                    'poll' => _('ссылка на опрос'),
                    'title' => _('название опроса'),
                ];
                break;
            case HM_Messenger::TEMPLATE_POLL_LEADERS:
                $customVariables = [
                    'poll' => _('ссылка на опрос'),
                    'title' => _('название опроса'),
                ];
                break;
            case HM_Messenger::TEMPLATE_REG_CONFIRM_EMAIL:
            $customVariables = [
                'email_confirm_url' => _('ссылка для подтверждения e-mail')
            ];
            break;
            case HM_Messenger::TEMPLATE_UNBLOCK:
                $customVariables = [
                ];
                break;
            case HM_Messenger::TEMPLATE_SUPPORT_MESSAGE:
            case HM_Messenger::TEMPLATE_SUPPORT_STATUS:
            case HM_Messenger::TEMPLATE_SUPPORT_NEW:
                $customVariables = [
                    'id' => _('номер заявки'),
                    'title' => _('тема заявки'),
                    'lfname' => _('ФИО отправителя'),
                    'request' => _('описание проблемы и желаемый результат'),
                    'response' => _('ответ админитратора'),
                    'status' => _('статус заявки')
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_SUBJECT_SESSION:
                $customVariables = [
                    'course' => _('название сессии'),
                    'begin' => _('дата начала сессии'),
                    'end' => _('дата окончания сессии'),
                    'role' => _('назначенная роль'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_LESSON:
                $customVariables = [
                    'lesson' => _('ссылка на занятие'),
                    'course' => _('ссылка на курс'),
                ];
                break;
            case HM_Messenger::TEMPLATE_SUBJECT_MARK:
                $customVariables = [
                    'mark' => _('оценка'),
                    'course' => _('ссылка на курс'),
                ];
                break;
            case HM_Messenger::TEMPLATE_LESSON_MARK:
                $customVariables = [
                    'mark' => _('оценка'),
                    'lesson' => _('ссылка на занятие'),
                ];
                break;
            case HM_Messenger::TEMPLATE_STUDENT_SOLVE_TASK:
            case HM_Messenger::TEMPLATE_STUDENT_QUESTION_TASK:
                $customVariables = [
                    'fio' => _('ФИО слушателя'),
                    'lesson' => _('ссылка на занятие'),
                    'course' => _('ссылка на курс'),
                ];
                break;
            case HM_Messenger::TEMPLATE_TEACHER_ANSWER_TASK:
            case HM_Messenger::TEMPLATE_TEACHER_CONDITION_TASK:
                $customVariables = [
                    'lesson' => _('ссылка на занятие'),
                    'course' => _('ссылка на курс'),
                ];
                break;

            // Не используются. При включении проверять перменные, особо URL (чтобы не смешивалась с глобальной)
            
            case HM_Messenger::TEMPLATE_FORUM_NEW_ANSWER:
            case HM_Messenger::TEMPLATE_FORUM_NEW_HIDDEN_ANSWER:
                $customVariables = [
                    'message_user_name' => _('пользователь, приславший сообщение'),
                    'section_name' => _('название темы'),
                    'forum_name' => _('название форума'),
                    'message_url' => _('ссылка на сообщение')
                ];
                break;
            case HM_Messenger::TEMPLATE_FORUM_NEW_MARK:
                $customVariables = [
                    'section_name' => _('название темы'),
                    'forum_name' => _('название форума'),
                    'message_url' => _('ссылка на сообщение')
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_PROJECT:
                $customVariables = [
                    'course' => _('название конкурс')
                ];
                break;
            case HM_Messenger::TEMPLATE_PROJECT_STATE_CHANGED:
                $customVariables = [
                    'course' => _('название конкурс'),
                    'status' => _('статус конкурса')
                ];
                break;
            case HM_Messenger::TEMPLATE_RECRUIT_EVENT:
                $customVariables = [
                    'login' => _('логин (адресата)'),
                    'new_password' => _('новый пароль для входа (адресата)'),
                    'candidate_name' => _('имя кандидата'),
                    'vacancy' => _('название сессии подбора'),
                    'event' => _('название мероприятия'),
                    'date' => _('планируемая дата мероприятия'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_MANAGER_SESSION_STARTED:
                $customVariables = [
                    'period' => _('период участия в сессии планирования'),
                    'url_session' => _('ссылка на сессию планирования'),
                    'plan_end_date' => _('дата окончания сессии планирования')
                ];
                break;
            case HM_Messenger::TEMPLATE_LEARNING_PLANNED:
                $customVariables = [
                    'dep_org' => _('наименование подразделения')
                ];
                break;
            case HM_Messenger::TEMPLATE_PERSONAL_CLAIMANT_DELETE:
            case HM_Messenger::TEMPLATE_PERSONAL_CLAIMANT_EDIT:
                $customVariables = [
                    'user_name' => _('имя сотрудника'),
                    'subject_name' => _('название курса')
                ];
                break;
            case HM_Messenger::TEMPLATE_PERSONAL_CLAIMANT_NOTIFICATION:
                $customVariables = [
                    'report_url' => _('ссылка на отчет по обязательному обучению')
                ];
                break;
            case HM_Messenger::TEMPLATE_WELCOME_TRAINING_WORKER_NOTIFICATION:
                $customVariables = [
                    'name_patronymic' => _('имя/отчество пользователя'),
                    'date' => _('дата проведения семинара'),
                    'place' => _('место проведения семинара'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_WELCOME_TRAINING_MANAGER_NOTIFICATION:
                $customVariables = [
                    'name_patronymic' => _('имя/отчество руководителя'),
                    'fio_newcomer' => _('ФИО пользователя'),
                    'date' => _('дата проведения семинара'),
                    'place' => _('место проведения семинара'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ADAPTING_PLAN:
                $customVariables = [
                    'name_patronymic' => _('имя/отчество руководителя'),
                    'fio_newcomer' => _('ФИО пользователя, проходящего адаптацию'),
                    'url' => _('ссылка на план адаптации'), // ! необходимо заменить
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ADAPTING_KPIS:
                $customVariables = [
                    'name_patronymic' => _('имя/отчество пользователя'),
                    'url' => _('ссылка на план адаптации'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ADAPTING_START:
                $customVariables = [
                    'name_patronymic' => _('имя/отчество руководителя'),
                    'fio_adapt' => _('ФИО пользователя, проходящего адаптацию'),
                    'url' => _('ссылка на страницу оценки выполнения'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ADAPTING_STOP:
                $customVariables = [
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ROTATION_PLAN:
                $customVariables = [
                    'begin_date' => _('дата начала сессии ротации'),
                    'end_date' => _('дата окончания сессии ротации'),
                    'rotation_position' => _('позиция ротации'),
                    'rotation_department' => _('подразделение ротации'),
                    'rotation_manager' => _('руководитель подразделения ротации'),
                    'fill_plan_date' => _('дата заполнения плана ротации'),
                    'url' => _('ссылка на сессию ротации'), // ! необходимо заменить
                ];
                break;
            case HM_Messenger::TEMPLATE_ROTATION_REPORT:
                $customVariables = [
                    'begin_date' => _('дата начала сессии ротации'),
                    'end_date' => _('дата окончания сессии ротации'),
                    'rotation_position' => _('позиция ротации'),
                    'rotation_department' => _('подразделение ротации'),
                    'report_date' => _('дата заполнения отчёта о ротации'),
                    'url' => _('ссылка на сессию ротации'), // ! необходимо заменить
                ];
                break;
            case HM_Messenger::TEMPLATE_RESERVE_PLAN:
                $customVariables = [
                    'fill_plan_date' => _('дата заполнения ИПР'),
                    'url' => _('ссылка на сессию КР'),
                ];
                break;
            case HM_Messenger::TEMPLATE_RESERVE_REPORT:
                $customVariables = [
                    'report_date' => _('дата заполнения отчёта о прохождении ИПР'),
                    'url' => _('ссылка на сессию КР'), // ! необходимо заменить
                ];
                break;
            case HM_Messenger::TEMPLATE_VACANCY_RESUME_SEND:
                $customVariables = [
                    'initiator_firstname' => _('имя инициатора'),
                    'initiator_patronymic' => _('отчество инициатора'),
                    'vacancy' => _('заявка на вакансию'),
                    'candidates_list' => _('список резюме кандидатов'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_MANAGER_SESSION_QUARTER_STARTED:
                $customVariables = [
                    'period' => _('период участия в сессии квартального планирования'),
                    'url_session' => _('ссылка на сессию квартального планирования'),
                    'plan_end_date' => _('дата окончания сессии квартального планирования')
                ];
                break;
            case HM_Messenger::TEMPLATE_LEARNING_ASSIGNED:
                $customVariables = [
                    'course' => _('внешний курс'),
                    'begin' => _('дата начала'),
                    'end' => _('дата окончания'),
                    'info' => _('информация о внешнем курсе')
                ];
                break;
            case HM_Messenger::TEMPLATE_LESSON_NOTIFICATION:
                $customVariables = [
                    'course' => _('ссылка на курс'),
                    'date_end' => _('дата прохождения занятия'),
                    'lesson' => _('название занятия')
                ];
                break;
            case HM_Messenger::TEMPLATE_ADAPTATION_LABOR_SAFETY:
            case HM_Messenger::TEMPLATE_ADAPTATION_MANAGER:
            case HM_Messenger::TEMPLATE_ADAPTATION_CURATOR:
                $customVariables = [
                    'list' => _('список пользователей'),
                    'recruiter' => _('рекрутер'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_SESSION:
            case HM_Messenger::TEMPLATE_ASSIGN_SESSION_SELF:
            case HM_Messenger::TEMPLATE_ASSIGN_SESSION_PARENT:
            case HM_Messenger::TEMPLATE_ASSIGN_SESSION_SIBLINGS:
            case HM_Messenger::TEMPLATE_ASSIGN_SESSION_CHILDREN:
                $customVariables = [
                    'url_session' => _('ссылка на оценочную сессию'),
                    'begin' => _('дата начала сессии'),
                    'end' => _('дата окончания сессии'),
                    'contacts' => _('ответственный менеджер по оценке (ФИО, телефон, email)'),
                ];
                break;
            case HM_Messenger::TEMPLATE_ASSIGN_LABOR_SAFETY_EVENT:
                $customVariables = [
                    'course' => _('Название курса со ссылкой на него'),
                    'url_user' => _('Ссылка на карточку пользователя'),
                    'date_begin' => _('Дата начала курса'),
                    'date_end' => _('Дата окончания курса'),
                    'place' => _('Место проведения курса')
                ];
                break;
            case HM_Messenger::TEMPLATE_RECOVERY_LINK:
                $customVariables = [
                    'recovery_link' => _('ссылка для восстановления пароля')
                ];
                break;
        }

        if (in_array($id, $excludedDefaultsTemplates)) {
            $result = $customVariables;
        } else {
            $result = array_merge($defaultVariables, $customVariables);
        }

        return $result;
    }
}