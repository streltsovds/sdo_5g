<?php

class HM_Messenger implements SplSubject
{
    const TECH_SUPPORT_USER_ID = -1;
    const SYSTEM_USER_ID = 0;

    const TEMPLATE_REG = 1;
    const TEMPLATE_ASSIGN_ROLE = 2;
    const TEMPLATE_ASSIGN_SUBJECT = 3;
    const TEMPLATE_BEFORE_END_TRAINING = 5; // ?
    const TEMPLATE_GRADUATED = 6;
    const TEMPLATE_ORDER = 7;
    const TEMPLATE_ORDER_REGGED = 8;
    const TEMPLATE_ORDER_ACCEPTED = 9;
    const TEMPLATE_ORDER_REJECTED = 10;
    const TEMPLATE_PASS = 11;
    const TEMPLATE_RECOVERY_LINK = 26;
    const TEMPLATE_PRIVATE = 12;
    const TEMPLATE_SUBSCRIPTION_UPDATED = 13;

    const TEMPLATE_POLL_STUDENTS = 14;
//    const TEMPLATE_POLL_TEACHERS = 15; // DEPRECATED!
    const TEMPLATE_POLL_LEADERS = 16;

    const TEMPLATE_MULTIMESSAGE = 17;
    const TEMPLATE_FORUM_NEW_ANSWER = 18;
    const TEMPLATE_FORUM_NEW_HIDDEN_ANSWER = 19;
    const TEMPLATE_FORUM_NEW_MARK = 20;
    const TEMPLATE_REG_CONFIRM_EMAIL = 21;
    const TEMPLATE_UNBLOCK = 22;
    const TEMPLATE_SUPPORT_MESSAGE = 23;
    const TEMPLATE_SUPPORT_STATUS = 24;
    const TEMPLATE_SUPPORT_NEW = 25;
    const TEMPLATE_ASSIGN_SUBJECT_SESSION = 33;
    const TEMPLATE_ASSIGN_LESSON = 34;
    const TEMPLATE_SUBJECT_MARK = 35;
    const TEMPLATE_LESSON_MARK = 36;
    const TEMPLATE_STUDENT_SOLVE_TASK = 37;
    const TEMPLATE_STUDENT_QUESTION_TASK = 38;
    const TEMPLATE_TEACHER_ANSWER_TASK = 39;
    const TEMPLATE_TEACHER_CONDITION_TASK = 40;

    const TEMPLATE_ASSIGN_PROJECT = 54;
    const TEMPLATE_PROJECT_STATE_CHANGED = 55;
    const TEMPLATE_LEASING_EXCEED_SESSIONS = 80;
    const TEMPLATE_LEASING_EXCEED_HDD = 81;
//    const TEMPLATE_ASSIGN_SESSION = 99;
    const TEMPLATE_RECRUIT_START_INTERNAL = 100;
    const TEMPLATE_RECRUIT_START_EXTERNAL = 101;
    const TEMPLATE_RECRUIT_FAIL = 102;
    const TEMPLATE_RECRUIT_SUCCESS = 103;
    const TEMPLATE_RECRUIT_EVENT = 104;
    const TEMPLATE_MANAGER_SESSION_STARTED = 105; // уведомление руководителя о сессии годового планирования
    const TEMPLATE_LEARNING_PLANNED = 106; // уведомление сотрудника о том, что его обучение на следующий год окончательно и бесповоротно согласовано
    const TEMPLATE_PERSONAL_CLAIMANT_DELETE = 108; // не используется
    const TEMPLATE_PERSONAL_CLAIMANT_EDIT = 109; // не используется
    const TEMPLATE_PERSONAL_CLAIMANT_NOTIFICATION = 110;
    const TEMPLATE_WELCOME_TRAINING_WORKER_NOTIFICATION = 111;
    const TEMPLATE_WELCOME_TRAINING_MANAGER_NOTIFICATION = 112;

    const TEMPLATE_ADAPTING_PLAN = 210; // о необходимости заполнить план
    const TEMPLATE_ADAPTING_START = 211; // оценка kpi - за 21 день до окончания
    const TEMPLATE_ADAPTING_KPIS = 353; // перевод на 2-й этап
    const TEMPLATE_ADAPTING_STOP = 212;
    const TEMPLATE_ROTATION_PLAN = 213;
    const TEMPLATE_ROTATION_REPORT = 214;
    const TEMPLATE_RESERVE_PLAN = 215;
    const TEMPLATE_RESERVE_REPORT = 216;

    const TEMPLATE_ADAPTING_WELCOME = 217; // не используется, вместо этого шлём типовое уведомление об опросе

    const TEMPLATE_VACANCY_RESUME_SEND = 221;

    const TEMPLATE_MANAGER_SESSION_QUARTER_STARTED = 301; // уведомление руководителя о сессии квартального планирования
    const TEMPLATE_LEARNING_ASSIGNED = 302; // уведомление сотрудника о конкретной назначенной сессии в рамках кварт.планирования
    const TEMPLATE_LESSON_NOTIFICATION = 303; // Напоминание о необходимости пройти занятие
    const TEMPLATE_ADAPTATION_LABOR_SAFETY = 350; // напоминание Специалисту по ОТ о необходимости проведения вводных инструктажей для отмеченных сотрудников
    const TEMPLATE_ADAPTATION_MANAGER = 351; // напоминание Руководителю
    const TEMPLATE_ADAPTATION_CURATOR = 352; // напоминание Куратору

    const TEMPLATE_ASSIGN_SESSION = 991;
    const TEMPLATE_ASSIGN_SESSION_SELF = 991; // DEPRECATED
    const TEMPLATE_ASSIGN_SESSION_PARENT = 992; // DEPRECATED
    const TEMPLATE_ASSIGN_SESSION_SIBLINGS = 993; // DEPRECATED
    const TEMPLATE_ASSIGN_SESSION_CHILDREN = 994; // DEPRECATED

    const TEMPLATE_ASSIGN_SESSION_FILL_FORMS = 995; // автоматически (раз в сутки по cron'у) рассылаеся всем респондентам оценочных сессий, не заполнившим свои формы на 100%, начиная с момента перевода сессии в активное состояние и до перевода в завершенное состояние
    const TEMPLATE_ASSIGN_LABOR_SAFETY_EVENT = 996; // Назначение на мероприятие по охране труда

    const TEMPLATE_EMPTY = 1000;

    const TEMPLATE_LEASING_PRE_EXCEED_SESSIONS = 820;
    const TEMPLATE_LEASING_PRE_EXCEED_HDD = 830;

    const DEFAULT_MESSAGE = "[TEXT]";
    const DEFAULT_SUBJECT = "[SUBJECT]";

    const DEFAULT_MESSAGE_DELIMITER = "\n<br/>\n<br/>";

    private $_observers = array();
    private $_templateId = null;
    private $_template = null;

    private $_replacements  = array();
    private $_message = null;
    private $_subject = null;
    private $_senderId = null;
    private $_sender = null;
    private $_receiverId = null;
    private $_receiver = null;

    private $_roomSubject = null;
    private $_roomSubjectId = null;

    private $_cache = array();
    private $_msgChannels  = array();

    private $_files = array();

    private $_ical = null;

    private $_templatesEnabled  = array();

    private $_forceCustomTemplate = false;

    public $_bNoPush = false;

    public function __construct()
    {
        //$this->_view = new Zend_View();
        //$this->_view->setScriptPath(APPLICATION_PATH . '/mails');
    }

    public function isPriorityMail()
    {
        $template = $this->fetchTemplate($this->getTemplateId());
        return $template->priority;
    }

    public function addFile($filePath, $name, $mime = Zend_Mime::TYPE_OCTETSTREAM, $disposition = Zend_Mime::DISPOSITION_ATTACHMENT, $encoding = Zend_Mime::ENCODING_BASE64)
    {
        $this->_files[] = array(
            'name' => $name,
            'path' => $filePath,
            'mime' => $mime,
            'disposition' => $disposition,
            'encoding' => $encoding
        );
    }

    public function clearFiles()
    {
        $this->_files = array();
    }

    public function getFiles()
    {
        return $this->_files;
    }

    public function assign($values)
    {
        $url = Zend_Registry::get('view')->serverUrl('/');
        $urlNoSlash = rtrim($url, '/');
        $subjectUrl = '';

        if (is_array($values) && count($values)) {
            foreach ($values as $key => $value) {
                switch (strtolower($key)) {
                    case 'subject_id':
                        if (!isset($this->_cache['SUBJECT'][$value])) {
                            $this->_cache['SUBJECT'][$value] = $this->getOne($this->getService('Subject')->findManyToMany('Room', 'SubjectRoom', $value));
                        }

                        if (is_a($this->_cache['SUBJECT'][$value], 'HM_Subject_SubjectModel')) {
                            $subjectUrl = $urlNoSlash . $this->_cache['SUBJECT'][$value]->getDefaultUri(true);
                        }

                        $this->_replacements['COURSE'] = '<a href="' . $subjectUrl . '" target="_blank">' . $this->_cache['SUBJECT'][$value]->name . '</a>';
                        $this->_replacements['URL_USER'] = '<a href="' . $urlNoSlash . '/user/reg/subject/subid/' .
                            $this->_cache['SUBJECT'][$value]->subid . '" target="_blank">' . $value . '</a>';

                        if ($date = $this->_cache['SUBJECT'][$value]->begin) {
                            $date = new HM_Date($date);
                            $this->_replacements['DATE_BEGIN'] = $date->get('dd.MM.Y');
                            $this->_replacements['BEGIN'] = $date->get('dd.MM.Y');
                        }

                        if ($date = $this->_cache['SUBJECT'][$value]->end) {
                            $date = new HM_Date($date);
                            $this->_replacements['DATE_END'] = $date->get('dd.MM.Y');
                            $this->_replacements['END'] = $date->get('dd.MM.Y');
                        }
                        if (count($this->_cache['SUBJECT'][$value]->room)) {
                            $places = $this->_cache['SUBJECT'][$value]->room->getList('name');
                            $this->_replacements['PLACE'] = implode(', ', $places);
                        }

                        $value = $this->_cache['SUBJECT'][$value]->name;
                        if (!isset($this->_replacements['COURSE'])) $this->_replacements['COURSE'] = $value;

                        break;
                    case 'course_id':
                        if (!isset($this->_cache['COURSE'][$value])) {
                            $this->_cache['COURSE'][$value] = $this->getOne($this->getService('Course')->find($value));
                        }

                        $value = $this->_cache['COURSE'][$value]->Title;
                        $this->_replacements['COURSE'] = $value;
                        break;
                    case 'lesson_id':
                        if (!isset($this->_cache['LESSON'][$value])) {
                            $this->_cache['LESSON'][$value] = $this->getOne($this->getService('Lesson')->find($value));
                        }

                        $lessonUrl = $urlNoSlash . $this->_cache['LESSON'][$value]->getExecuteUrl();
                        $this->_replacements['LESSON'] = '<a href="' . $lessonUrl . '" target="_blank">' . $this->_cache['LESSON'][$value]->title . '</a>';

                        break;
                    case 'user_id':
                        if (!isset($this->_cache['USER'][$value])) {
                            $this->_cache['USER'][$value] = $this->getOne($this->getService('User')->find($value));
                        }

                        $this->_replacements['LOGIN'] = $this->_cache['USER'][$value]->Login;
                        break;

                    default:
                        if ((strtoupper($key) == 'COURSE')
                            && isset($values['subject_id'])
                            && $values['subject_id']
                            && isset($this->_replacements['COURSE'])
                            && !empty($this->_replacements['COURSE'])) break;

                        $this->_replacements[strtoupper($key)] = $value;
                        break;
                }
            }
        }
    }

    public function assignValue($key, $value)
    {
        $this->_replacements[strtoupper($key)] = $value;
    }

    public function attach(SplObserver $obs)
    {
        $id = spl_object_hash($obs);
        $this->_observers[$id] = $obs;
    }

    public function detach(SplObserver $obs)
    {
        $id = spl_object_hash($obs);
        unset($this->_observers[$id]);
    }

    public function notify()
    {

        try {
            if ($this->_template && $this->_template->enabled == false)
                return;
        } catch (Exception $e) {

        }

        foreach ($this->_observers as $observer) {
            $result = $observer->update($this);
        }
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getOne($collection)
    {
        if ($collection && count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function setTemplate($templateId)
    {
        $this->_templateId = $templateId;
        $this->_template = $this->fetchTemplate($templateId);
    }

    public function fetchTemplate($templateId)
    {
        $cacheName = 'fetchTemplate';

        if ($this->_cache[$cacheName][$templateId]) {
            $output = $this->_cache[$cacheName][$templateId];
        } else {
            /** @var HM_Notice_NoticeService $noticeService */
            $noticeService = $this->getService('Notice');

            $output = $noticeService->fetchRow($noticeService->quoteInto('type = ?', $templateId));
            $this->_cache[$cacheName][$templateId] = $output;
        }

        return $output;
    }

    public function forceTemplate($template)
    {
        $this->_template = $template;
    }

    public function getTemplateId()
    {
        return $this->_templateId;
    }

    public function replace($text, $addQuotes = false)
    {
        if (is_array($this->_replacements) && count($this->_replacements)) {
            foreach ($this->_replacements as $key => $value) {
                $value = $addQuotes ? '"' . $value . '"' : $value;
                $text = str_replace('[' . $key . ']', $value, $text);
            }
        }
        // Оставляем только для обратной совместимости
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('User')->find($this->_receiverId))) {
            $user = $collection->current();
            $text = str_replace('[NAME_PATRONYMIC]', implode(' ', array($user->FirstName, $user->Patronymic)), $text);
        }

        return $text;
    }

    /*
     * ВАЖНО!
     *
     * Если $senderId != HM_Messenger::SYSTEM_USER_ID письмо шлётся через ES
     */
    public function send($senderId, $receiverId = 0, $replacements = null, $bNoPush = false)
    {
        $this->_preprocessData($senderId, $receiverId, $replacements);

        $this->_bNoPush = $bNoPush;

        $this->notify();
    }

    public function push($senderId, $receiverId = 0, $replacements = null, $addInfo = false, $buttons = false)
    {
        $this->_preprocessData($senderId, $receiverId, $replacements);

        if ($this->_template && $this->_template->enabled == false)
            return;
        $this->getService('User')->sendPushMessage($this->getReceiverId(), $this->getMessage(), $addInfo, $this->getSubject(), $buttons);
    }

    private function _preprocessData($senderId, $receiverId = 0, $replacements = null)
    {
        if (!$this->_template) {
            throw new HM_Messenger_Exception(sprintf(_('Шаблон системных сообщений #%d не найден.'), $this->_templateId));
        }

        $this->_subject = self::DEFAULT_SUBJECT;

        $this->_receiverId = $receiverId;
        $this->_receiver = null;
        $this->_senderId = $senderId;
        $this->_sender = null;

        if (null !== $replacements) {
            $this->assign($replacements);
        }

        if (strlen($this->_template->title)) {
            $this->_subject = $this->_template->title;
        }

        // могли принудительно установить через setMessage()
        if (!$this->_forceCustomTemplate) {
            if (strlen($this->_template->message)) {
                $this->_message = $this->_template->message;
            } else {
                $this->_message = self::DEFAULT_MESSAGE;
            }
        }

        // подставляем глобальные переменные здесь (в assign() могут быть доступны не все данные)
        $this->_assignGlobalVars();

        $this->_subject = strip_tags($this->replace($this->_subject, $addQuotes = false));
        $this->_message = $this->replace($this->_message);
    }

    private function _assignGlobalVars()
    {
        $url = Zend_Registry::get('view')->serverUrl('/');
        $this->_replacements['URL'] = '<a href="' . $url . '" target="_blank">' . $url . '</a>';

        $defaultSignature = $this->getService('Option')->getOption('message_signature');
        $this->_replacements['SIGNATURE'] = !empty($defaultSignature) ? $defaultSignature : '';

        $receiver = $this->getReceiver();
        $this->_replacements['NAME'] = implode(' ', [$receiver->FirstName, $receiver->Patronymic]);
    }

    public function getSenderId()
    {
        return $this->_senderId;
    }

    public function getSender()
    {
        if (null === $this->_sender) {
            $this->_sender = $this->getOne($this->getService('User')->find($this->getSenderId()));
        }

        if (!$this->_sender) {
            $this->_sender = $this->getDefaultUser();
        }

        return $this->_sender;
    }

    public function getReceiverId()
    {
        return $this->_receiverId;
    }

    public function getReceiver()
    {
        $receiverId = $this->getReceiverId();

        if (null === $this->_receiver) {
            $this->_receiver = $this->getOne($this->getService('User')->find($this->getReceiverId()));
        }

        if (!$this->_receiver) {
            switch ($receiverId) {
                case self::TECH_SUPPORT_USER_ID:
                    $this->_receiver = $this->getTechSupportUser();
                    break;
                default:
                    $this->_receiver = $this->getDefaultUser();
                    break;
            }
        }

        return $this->_receiver;
    }

    public function getDefaultUser()
    {
        $user = new HM_User_UserModel(
            array(
                'EMail' => $this->getService('Option')->getOption('dekanEMail') ? : Zend_Registry::get('config')->mailer->default->email,
                'FirstName' => $this->getService('Option')->getOption('dekanName') ? : Zend_Registry::get('config')->mailer->default->name,
                'LastName' => '',
                'Patronymic' => '',
                'MID' => 0
            )
        );

        return $user;
    }

    public function getTechSupportUser()
    {
        $user = new HM_User_UserModel(
            array(
                'EMail' => Zend_Registry::get('config')->mailer->support->email,
                'FirstName' => Zend_Registry::get('config')->mailer->support->name,
                'LastName' => '',
                'Patronymic' => '',
                'MID' => -1
            )
        );

        return $user;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function setMessage($message)
    {
        $this->_message = $message;
        $this->_forceCustomTemplate = true;
        return $this;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function setRoom($subject, $subjectId)
    {
        $this->_roomSubject = $subject;
        $this->_roomSubjectId = $subjectId;
    }

    /**
     * @return array
     */
    public function getRoom()
    {
        return array($this->_roomSubject, $this->_roomSubjectId);
    }

    public function setOptions($templateId, $values = array(), $subject = '', $subjectId = 0)
    {
        $this->setTemplate($templateId);
        $this->assign($values);
        $this->setRoom($subject, $subjectId);
    }

    /**
     * Добавление сообщения в очередь.
     * @param int $senderId
     * @param int $receiverId
     * @param $templateId
     * @param array $values
     * @param string $subject
     * @param int $subjectId
     */
    public function addMessageToChannel($senderId, $receiverId = 0, $templateId, $values = array(), $subject = '', $subjectId = 0)
    {
        if (!isset($this->_templatesEnabled[$templateId])) {
            $template = $this->getOne($this->getService('Notice')->fetchAll(array('type = ?'=>$templateId)));
            if (!$template) return;

            $this->_templatesEnabled[$templateId] = $template->enabled;
        }
        if (!$this->_templatesEnabled[$templateId]) return;


        $this->setOptions($templateId, $values, $subject, $subjectId);
        $this->_preprocessData($senderId, $receiverId);

        $classOptions = $this->__toArray();
        $key = $classOptions['templateId'] . '_' . $classOptions['receiverId'];

        if (isset($this->_msgChannels[$key])) {
            $this->_msgChannels[$key]['messages'][] = $classOptions['message'];
        } else {
            $this->_msgChannels[$key] = $classOptions;
            $this->_msgChannels[$key]['messages'][] = $classOptions['message'];
        }


    }

    /**
     * Отправка очереди сообщений
     * @param $type указывается, если нужно отправить сообщения только данного типа.
     */
    public function sendAllFromChannels($type = null)
    {
        foreach ($this->_msgChannels as $channelKey => $messageItem) {
            if ($type !== null && $type != $messageItem['templateId']) continue;

            $messageItem['message'] = implode(self::DEFAULT_MESSAGE_DELIMITER, array_unique($messageItem['messages']));
            $this->__fromArray($messageItem);
            $this->setTemplate(self::TEMPLATE_MULTIMESSAGE);
            $this->_template->title = $messageItem['template']->title;
            $this->notify();
            unset($this->_msgChannels[$channelKey]);
        }
    }

    public function serialize()
    {
        $classData = $this->__toArray();

        unset(
            $classData['receiver'],
            $classData['sender'],
            $classData['template']
        );

        return $classData;
    }

    public function __toArray()
    {
        return array('message'       => $this->_message,
            'receiver' => $this->_receiver,
            'receiverId' => $this->_receiverId,
            'replacements' => $this->_replacements,
            'roomSubject' => $this->_roomSubject,
            'roomSubjectId' => $this->_roomSubjectId,
            'sender' => $this->_sender,
            'senderId' => $this->_senderId,
            'template' => $this->_template,
            'templateId' => $this->_templateId,
                     'subject'       => $this->_subject);
    }

    public function __fromArray($dataArray)
    {

        $this->_message = (isset($dataArray['message'])) ? $dataArray['message'] : NULL;
        $this->_receiver = (isset($dataArray['receiver'])) ? $dataArray['receiver'] : NULL;
        $this->_receiverId = (isset($dataArray['receiverId'])) ? $dataArray['receiverId'] : NULL;
        $this->_replacements = (isset($dataArray['replacements'])) ? $dataArray['replacements'] : NULL;
        $this->_roomSubject = (isset($dataArray['roomSubject'])) ? $dataArray['roomSubject'] : NULL;
        $this->_roomSubjectId = (isset($dataArray['roomSubjectId'])) ? $dataArray['roomSubjectId'] : NULL;
        $this->_sender = (isset($dataArray['sender'])) ? $dataArray['sender'] : NULL;
        $this->_senderId = (isset($dataArray['senderId'])) ? $dataArray['senderId'] : NULL;
        $this->_template = (isset($dataArray['template'])) ? $dataArray['template'] : NULL;
        $this->_templateId = (isset($dataArray['templateId'])) ? $dataArray['templateId'] : NULL;
        $this->_subject = (isset($dataArray['subject'])) ? $dataArray['subject'] : NULL;
    }

    public function setIcal(HM_Ical_Calendar $ical)
    {
        $this->_ical = $ical;
    }

    public function getIcal()
    {
        return $this->_ical;
    }

    public static function getCalendar($summary, $subject, $start, $end = null, $place = null)
    {
        $calendar = new HM_Ical_Calendar();
        $start = new HM_Date($start);

        $calendar->addTimezone(HM_Ical_Timezone::fromTimezoneId(Zend_Registry::get('config')->timezone->default));
        $calendar->properties()->add(new HM_Ical_Property('METHOD', HM_Ical_Property_Value_Text::fromString('REQUEST')));

        $event = new HM_Ical_Event();
        $event->properties()->add(new HM_Ical_Property('UID', HM_Ical_Property_Value_Text::fromString(md5(rand(0, 10000) . time()))));
        $event->properties()->add(new HM_Ical_Property('SUMMARY', HM_Ical_Property_Value_Text::fromString(strip_tags($summary))));
        $event->properties()->add(new HM_Ical_Property('DESCRIPTION', HM_Ical_Property_Value_Text::fromString(strip_tags($subject))));
        $event->properties()->add(new HM_Ical_Property('ORGANIZER', HM_Ical_Property_Value_Text::fromString(
            'MAILTO:' . Zend_Registry::get('config')->mailer->default->email)));

        if ($place) $event->properties()->add(new HM_Ical_Property('LOCATION', HM_Ical_Property_Value_Text::fromString($place)));

        $event->properties()->add(new HM_Ical_Property('DTSTART', HM_Ical_Property_Value_DateTime::fromString($start->toString('YYYYMMdd'))));

        if($end) {
            $end = new HM_Date($end);
            $event->properties()->add(new HM_Ical_Property('DTEND', HM_Ical_Property_Value_DateTime::fromString($end->toString('YYYYMMdd'))));
        }

        $now = new HM_Date();
        $event->properties()->add(new HM_Ical_Property('DTSTAMP', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMdd'))));
        $event->properties()->add(new HM_Ical_Property('CREATED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMdd'))));
        $event->properties()->add(new HM_Ical_Property('LAST-MODIFIED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMdd'))));

        $calendar->addEvent($event);
        return $calendar;
    }

    public function isNoPush()
    {
        return $this->_bNoPush;
    }

}
