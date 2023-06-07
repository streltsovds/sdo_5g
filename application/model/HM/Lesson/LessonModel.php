<?php

/**
 * @property int $SHEID ID занятия
 * @property string $title
 * @property int $CID ID курса
 * @property int $typeID ID типа занятия см. @link HM_Activity_ActivityModel
 * @property int $vedomost
 * @property int $teacher
 * @property int $moderator
 * @property int $createID
 * @property int $recommend
 * @property int $all
 * @property int $cond_sheid
 * @property int $cond_mark
 * @property int $cond_progress
 * @property int $cond_avgbal
 * @property int $cond_sumbal
 * @property int $gid
 * @property int $notice
 * @property int $notice_days
 * @property int $activities
 * @property int $descript
 * @property int $tool
 * @property int $startday
 * @property int $stopday
 * @property string $begin
 * @property string $end
 * @property int $timetype
 */

abstract class HM_Lesson_LessonModel extends HM_Model_Abstract implements HM_Lesson_LessonModel_Interface, HM_Quest_Context_Interface
{
    /** @deprecated */
    const MODE_NONE = -1;
    const MODE_PLAN = 0;
    const MODE_FREE = 1;
    const MODE_FREE_BLOCKED = 2;

    const TIMETYPE_DATES      = 0;
    const TIMETYPE_RELATIVE   = 1;
    const TIMETYPE_TIMES      = 3;
    const TIMETYPE_FREE       = 2;

    const CONDITION_NONE      = 0;
    const CONDITION_PROGRESS  = 1;
    const CONDITION_AVGBAL    = 2;
    const CONDITION_SUMBAL    = 3;
    const CONDITION_LESSON    = 4;

    const ICON_LARGE    = 105;
    const ICON_MEDIUM   = 64;

    const DATE_UNLIMITED = 'unlimited';

    const SORT_ORDER_DEFAULT = 0;
    const SORT_ORDER_OVERDUE = 1;
    const SORT_ORDER_FREE = 2;

    const MARK_OFF = 0;
    const MARK_ON = 1;

    protected $_sortOrder;

    protected $_primaryName = 'SHEID';

    public $material;

    static public function getDateTypes()
    {
        return array(
            self::TIMETYPE_FREE      => _('Без ограничений'),
            self::TIMETYPE_DATES     => _('Диапазон дат'),
            self::TIMETYPE_TIMES     => _('Диапазон времени'),
            self::TIMETYPE_RELATIVE  => _('Относительный диапазон')
        );
    }

    static public function getConditionTypes()
    {
        return array(
            self::CONDITION_NONE      => _('Без условия'),
            self::CONDITION_PROGRESS  => _('Процент выполнения'),
            self::CONDITION_AVGBAL    => _('Средний балл по курсу'),
            self::CONDITION_SUMBAL    => _('Суммарный балл по курсу'),
            self::CONDITION_LESSON    => _('Выполнение другого занятия')
        );
    }



    static public function getModeTypes()
    {
        return array(
            self::MODE_NONE      => _('Нет доступа'),
            self::MODE_PLAN  => _('План занятий'),
            self::MODE_FREE    => _('Свободный доступ')
        );
    }

    static public function factory($data, $default = 'HM_Lesson_LessonModel')
    {
        if (isset($data['typeID']))
        {
            if ($data['typeID'] < 0) {
                return parent::factory($data, 'HM_Lesson_Custom_CustomModel');
            }

            switch($data['typeID']) {
                case HM_Event_EventModel::TYPE_POLL:
                    return parent::factory($data, 'HM_Lesson_Poll_PollModel');
                    break;
                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_EXERCISE:
                    return parent::factory($data, 'HM_Lesson_Test_TestModel');
                    break;
                case HM_Event_EventModel::TYPE_TASK:
                    return parent::factory($data, 'HM_Lesson_Task_TaskModel');
                    break;
                case HM_Event_EventModel::TYPE_LECTURE:
                    return parent::factory($data, 'HM_Lesson_Lecture_LectureModel');
                    break;
                case HM_Event_EventModel::TYPE_EMPTY:
                    return parent::factory($data, 'HM_Lesson_Empty_EmptyModel');
                    break;
                case HM_Event_EventModel::TYPE_COURSE:
                    return parent::factory($data, 'HM_Lesson_Course_CourseModel');
                    break;
                case HM_Event_EventModel::TYPE_WEBINAR:
                    return parent::factory($data, 'HM_Lesson_Webinar_WebinarModel');
                    break;
                case HM_Event_EventModel::TYPE_ECLASS:
                    return parent::factory($data, 'HM_Lesson_Eclass_EclassModel');
                    break;
                case HM_Event_EventModel::TYPE_RESOURCE:
                    return parent::factory($data, 'HM_Lesson_Resource_ResourceModel');
                    break;
                case HM_Event_EventModel::TYPE_FORUM:
                    return parent::factory($data, 'HM_Lesson_Forum_ForumModel');
                    break;
                // раньше это были одинаковые цифровые константы
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Leader_LeaderModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Student_StudentModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Teacher_TeacherModel');
                    break;
                default:
                    // Если занятие на основе сервиса взаимодействия
                    $activities = HM_Activity_ActivityModel::getActivityServices();
                    if (isset($activities[$data['typeID']])) {
                        $service = HM_Activity_ActivityModel::getActivityService($data['typeID']);

                        if (!Zend_Registry::get('serviceContainer')->hasService($service)) {
                            throw new HM_Exception(sprintf(_('Service %s not found'), $service));
                        }

                        if (!method_exists(Zend_Registry::get('serviceContainer')->getService($service), 'getLessonModelClass')) {
                            throw new HM_Exception(sprintf(_('Method getLessonModelClass not found in service %s'), $service));
                        }

                        $class = Zend_Registry::get('serviceContainer')->getService($service)->getLessonModelClass();
                        return parent::factory($data, $class);
                    }
                    break;
            }
        }
        if ($default != 'HM_Lesson_LessonModel') {
            return parent::factory($data, $default);
        }
    }

    // @todo
    public function getEditUrl()
    {
        return '/';
    }

    public function isCustomType()
    {
        return false;
    }

    public function getName()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->descript;
    }

    public function setAssigns($assigns)
    {
        if (!isset($this->assigns)) {
            $this->assigns = $assigns;
        }
        return true;
    }

    public function getAssigns()
    {
        if (isset($this->assigns)) {
            return $this->assigns;
        }
        return false;
    }

    public function getStudentScore($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign->getScore();
                    }
                }
            }
        }
        return false;
    }

    public function getStudentAssign($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign;
                    }
                }
            }
        }
        return false;
    }

    public function getStudentComment($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign->getComment();
                    }
                }
            }
        }
        return false;
    }

    public function getBeginDatetime($registered = null)
    {
        if ($this->isRelative() && (null !== $registered)) {
            $date = new Zend_Date(strtotime($registered) + $this->startday - 86400); // например, 172800 - это 2 полных дня; но относительная дата - это начало 2-го дня
            $this->begin = $date->get(Zend_Date::DATETIME);
        }

        return $this->dateTimeWithoutSeconds($this->begin);
    }

    public function getEndDatetime($registered = null)
    {
        if ($this->isRelative() && (null !== $registered)) {
            $date = new Zend_Date(strtotime($registered) + $this->stopday - 86400);
            $this->end = $date->get(Zend_Date::DATETIME);
        }

        return $this->dateTimeWithoutSeconds($this->end);
    }

    // $forUser - не рекомендуется! используйте getBeginDatePersonal
    public function getBeginDate($forUser = false)
    {
        if ($forUser) {
            foreach ($this->assigns as $lessonAssign) {
                if (($lessonAssign->MID == $forUser) && strtotime($lessonAssign->begin_personal)) {
                    return $this->date($lessonAssign->begin_personal);
                }
            }
        }
        return $this->date($this->begin);
    }

    // $forUser - не рекомендуется! используйте getEndDatePersonal
    public function getEndDate($forUser = false)
    {
        if ($forUser) {
            foreach ($this->assigns as $lessonAssign) {
                if (($lessonAssign->MID == $forUser) && (strtotime($lessonAssign->end_personal) > 0)) {
                    return $this->date($lessonAssign->end_personal);
                }
            }
        }
        return $this->date($this->end);
    }

    public function getBeginDatePersonal($mid = false, $date = null)
    {
        if ($date == null) {
            $assign = $this->getStudentAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->begin_personal;
        }
        return $date;
    }

    public function getEndDatePersonal($mid = false, $date = null)
    {
        if ($date == null) {
            $assign = $this->getStudentAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->end_personal;
        }

        return $date;
    }

    public function getBeginTime()
    {
        return $this->timeWithoutSeconds($this->begin);
    }

    public function getEndTime()
    {
        return $this->timeWithoutSeconds($this->end);
    }

    public function getModuleId()
    {
        $params = $this->getParams();
        return $params['module_id'];
    }

    public function getFormulaId()
    {
        $params = $this->getParams();
        if (isset($params['formula_id'])) {
            return $params['formula_id'];
        }
        return 0;
    }

    public function getFormulaGroupId()
    {
        $params = $this->getParams();
        if (isset($params['formula_group_id'])) {
            return $params['formula_group_id'];
        }
        return 0;
    }

    public function getFormulaPenaltyId()
    {
        $params = $this->getParams();
        if (isset($params['formula_penalty_id'])) {
            return $params['formula_penalty_id'];
        }
        return 0;
    }

    public function isStudentAssigned($studentId)
    {
        if (isset($this->assigns)) {
            foreach($this->assigns as $assign) {
                if ($assign->MID == $studentId) {
                    return true;
                }
            }
        }
        return false;
    }

    public function setParams($params)
    {
        $string = '';
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                if ($value === '') continue;
                $string .= sprintf("%s=%s;", $key, $value);
            }
        }
        $this->params = $string;
    }

    public function getParams()
    {
        $params = array();
        if (isset($this->params)) {
            $lines = explode(';', $this->params);
            if (count($lines)) {
                foreach($lines as $line) {
                    $tempArray = explode('=', $line);
                    if (count($tempArray) === 2) {
                        list($key, $value) = $tempArray;
                        $params[$key] = $value;
                    }
                }
            }
        }
        return array_filter($params);
    }

    public function isExecutable()
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        if ($serviceContainer->getService('Acl')->inheritsRole(
            $serviceContainer->getService('User')->getCurrentUserRole(), [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER
        ])) {
            return true;
        }

        // если это CustomModel
        if (isset($this->_lesson) && method_exists(get_class($this->_lesson), 'isExecutable')) {
            return call_user_func_array(array($this->_lesson, 'isExecutable'), func_get_args());
        }

        $now = new Zend_Date();

        if ($this->recommend) return true;

        // если просто материал, не занятие
        if ($this->isfree == self::MODE_FREE) return true;


        $currentUserId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $assign = $this->getStudentAssign($currentUserId);
        if (!$assign) {
            $lessonAssignService = Zend_Registry::get('serviceContainer')->getService('LessonAssign');
            $where = $lessonAssignService->quoteInto(
                array('SHEID = ?', ' AND MID = ?'),
                array($this->SHEID, $currentUserId)
            );
            $assign = $lessonAssignService->fetchAll($where)->current();
        }

        // теперь всё определяется begin_personal и end_personal
        if (
            ((strtotime($assign->begin_personal) <= 0) && (strtotime($assign->end_personal) <= 0)) ||
            ($now->isLater(new Zend_Date($assign->begin_personal)) && $now->isEarlier(new Zend_Date($assign->end_personal)))
        ) {
            return true;
        }

        return false;
    }

    public function getBeginDay()
    {
        return floor($this->startday / 24 / 60 / 60);
    }

    public function getEndDay()
    {
        return floor($this->stopday / 24 / 60 / 60);
    }

    public function getNecessaryLessonsId()
    {
        $ids = array();
        if (strlen($this->cond_sheid)) {
            $ids = explode('#', $this->cond_sheid);
        }

        return $ids;
    }

    public function getLessonId() {
        return $this->SHEID;
    }

    public function isConditionalLesson()
    {
        return ($this->cond_sheid || $this->cond_progress ||$this->cond_avgbal || $this->cond_sumbal);
    }

    public function isRelative()
    {
        return ($this->timetype == self::TIMETYPE_RELATIVE);
    }

    public function isTimeFree()
    {
        return ($this->timetype == self::TIMETYPE_FREE);
    }

    public function getServiceName()
    {
        return 'Lesson';
    }

    public function isResultInTable()
    {
        return true;
    }

    public function isFreeModeEnabled()
    {
        return false;
    }

    public function getUserIcon() {
        $user_ico = rtrim(Zend_Registry::get('config')->path->upload->lesson, '/') . '/' . $this->SHEID . '.jpg';
        if (file_exists($user_ico)) {
            return '/'. trim(Zend_Registry::get('config')->src->upload->lesson, '/') . '/' . $this->SHEID . '.jpg';
        } else {
            return null;
        }
    }

    public static function getIconClass($type)
    {

        /*
         * .grid_icon_chat,
            .grid_icon_course,
            .grid_icon_exercise,
            .grid_icon_forum,
            .grid_icon_lecture,
            .grid_icon_poll,
            .grid_icon_resource,
            .grid_icon_task,
            .grid_icon_test,
            .grid_icon_webinar,
            .grid_icon_wiki
         *
         *
         */
        $icons = array(
            HM_Event_EventModel::TYPE_COURSE => 'course',
            HM_Event_EventModel::TYPE_LECTURE => 'lecture',
            HM_Event_EventModel::TYPE_EXERCISE => 'exercise',
            HM_Event_EventModel::TYPE_POLL => 'poll',
            HM_Event_EventModel::TYPE_RESOURCE => 'resource',
            HM_Event_EventModel::TYPE_TASK => 'task',
            HM_Event_EventModel::TYPE_TEST=> 'test',
            HM_Event_EventModel::TYPE_WEBINAR => 'webinar'
        );
        return "tiny_icon_" . $icons[$type];
    }

    static public function getTypesFreeModeEnabled()
    {
        return array(
            HM_Event_EventModel::TYPE_COURSE,
            HM_Event_EventModel::TYPE_LECTURE,
            HM_Event_EventModel::TYPE_RESOURCE,
        );
    }

    public function setSortOrder($sortOrder)
    {
        $this->_sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return isset($this->_sortOrder) ? $this->_sortOrder : self::SORT_ORDER_DEFAULT;
    }

    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_CONTINUOUS;
    }

    public function getScale()
    {
        if ($this->getType() == HM_Event_EventModel::TYPE_POLL)
            return HM_Scale_ScaleModel::TYPE_BINARY;
        return $this->getDefaultScale();
    }

    /**
     * Публичные методы для поля order
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 17 january 2012
     */

    /**
     * Устанавливает значение поля order
     *
     * @param $newOrder
     *
     * @return HM_Lesson_LessonModel
     */
    public function setOrder($newOrder)
    {
        $this->order = intval($newOrder);
        return $this;
    }

    /**
     * Возвращает значение поля order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }


    public function getQuestContext()
    {
        return array(
            'context_type' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING,
            'context_event_id' => $this->SHEID
        );
    }

    // TODO!!!
    public function getRedirectUrl()
    {
        $sc = Zend_Registry::get('serviceContainer');

        $action = $sc->getService('Acl')->inheritsRole(
            $sc->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';

        return Zend_Registry::get('view')->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'));
    }

    public function isNewWindow()
    {
        return false;
    }

    public function getBeginEnd()
    {
        $return = false;
        switch ($this->timetype) {

            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                break;

            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                $begin = $this->getBeginDay();
                $end = $this->getEndDay();

                $strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
                if ($begin == $end)    {
                    $return['begin'] = $return['end'] = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
                } elseif (!$end) {
                    $return['begin'] = sprintf(_("%s день %s по курсу"), abs($begin), $strtime);
                } else {
                    $return['begin'] = sprintf(_("%s день"), abs($begin));
                    $return['end'] = sprintf(_("%s день %s по курсу"), abs($end), $strtime);
                }

                break;

            case HM_Lesson_LessonModel::TIMETYPE_DATES:
            case HM_Lesson_LessonModel::TIMETYPE_TIMES:

                $begin = $this->getBeginDate();
                $end = $this->getEndDate();

                $beginDate = HM_Model_Abstract::date($begin);
                $endDate = HM_Model_Abstract::date($end);

                if (!strtotime($begin) && !strtotime($end)) {
                    // false
                } elseif (!strtotime($begin)) {
                    $return['end'] = $endDate;
                } elseif (!strtotime($end)) {
                    $return['begin'] = $beginDate;
                }
//                elseif ($beginDate == $endDate)    {
//                    $return['begin'] = sprintf(_("%s %s"), $beginDate, $this->getBeginTime());
//                    $return['end'] = sprintf(_("%s %s"), $beginDate, $this->getEndTime());
//                }
                else {
                    $return['begin'] = $beginDate;
                    $return['end'] = $endDate;
                }

                break;
        }
        return $return;
    }

    // DEPRECATED!!!
    // для vue нужны структурированные данные, не строка
    // используйте getBeginEnd()
    public function formatBeginEnd()
    {
        $datetime = '';
        switch ($this->timetype) {

            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                $datetime = _('Не ограничено');
                break;

            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                $begin = $this->getBeginDay();
                $end = $this->getEndDay();

                $strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
                if ($begin == $end)    {
                    $datetime = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
                } elseif (!$end) {
                    $datetime = sprintf(_("с %s дня %s по курсу"), abs($begin), $strtime);
                } else {
                    $datetime = sprintf(_("с %s по %s день %s по курсу"), abs($begin), abs($end), $strtime);
                }

                break;

            case HM_Lesson_LessonModel::TIMETYPE_DATES:
            case HM_Lesson_LessonModel::TIMETYPE_TIMES:

                $begin = $this->getBeginDate();
                $end = $this->getEndDate();

                $beginDate = HM_Model_Abstract::date($begin);
                $endDate = HM_Model_Abstract::date($end);

                if (!strtotime($begin) && !strtotime($end)) {
                    $datetime = _('Не ограничено');
                } elseif (!strtotime($begin)) {
                    $datetime = sprintf(_("по %s"), $endDate);
                } elseif (!strtotime($end)) {
                    $datetime = sprintf(_("с %s"), $beginDate);
                } elseif ($beginDate == $endDate)    {
                    $datetime = sprintf(_("%s, с %s по %s"), $beginDate, HM_Model_Abstract::timeWithoutSeconds($begin), HM_Model_Abstract::timeWithoutSeconds($end));
                } else {
                    $datetime = sprintf(_("с %s по %s"), $beginDate, $endDate);
                }

                break;
        }
        return $datetime;
    }

    public function formatCondition($relationTitle)
    {
        if ($this->cond_sheid) {
            if ($relationTitle) {

                $url = sprintf('<a href="#lesson_%s">%s</a>', $this->cond_sheid, $relationTitle);
                $mark = $this->cond_mark ? sprintf('на оценку %s', $this->cond_mark) : '';
                return sprintf(_('Условие: выполнение занятия %s %s'), $url, $mark);
            }
        }

        if ($this->cond_progress) {
            return sprintf(_('Условие: процент выполнения %s%%'), $this->cond_progress);
        }

        if ($this->cond_avgbal) {
            return sprintf(_('Условие: средний балл %s'), $this->cond_avgbal);
        }

        if ($this->cond_sumbal) {
            return sprintf(_('Условие: сумма баллов %s'), $this->cond_sumbal);
        }

        if ($this->cond_sumbal) {
            return sprintf(_('Условие: сумма баллов %s'), $this->cond_sumbal);
        }

        return false;

    }

    public function getCachedLaunchCondition()
    {
        $relationTitle = $this->getCachedValue('lessonId2Title', $this->cond_sheid);
        if (!$relationTitle) return false;

        return $this->formatCondition($relationTitle);
    }


    /**
     * @param null $forUser
     * @param null $eventCollection
     * @param null $cols
     * @return array
     * @throws Zend_Exception
     */
    public function getDataLessonForPreview($forUser = NULL, $eventCollection = null, $cols = null)
    {
        $response = [];
        if (empty($cols)) {
            $cols = array(
                '72px',
                'auto',
                '110px',
                '256px',
            );
        }

        $serviceContainer = Zend_Registry::get('serviceContainer');

        /*        $this->view->allowEdit = $this->view->allowDelete = in_array(
                    Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
                    array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_TEACHER)
                );*/
        $response['allowEdit'] = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_TEACHER));

        $response['showScore'] = (
            (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ||
                //Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_STUDENT ||
                (((Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)) || Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) && $forUser)) &&
            $this->vedomost
        );
//#17849
        $types = HM_Event_EventModel::getTypes();
        $extTypes = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll();
        $extTypes = $extTypes->getList('event_id', 'title');
        foreach($extTypes as $i=>$e)
            $types[-$i] = $e;
//
        $aclService = Zend_Registry::get('serviceContainer')->getService('Acl');
        $userService = Zend_Registry::get('serviceContainer')->getService('User');

        // если смотрит сам юзер или препод/менеджер расписание пользователя
        // в этом случае показываем персональные даты из scheduleID
        if ($aclService->inheritsRole($userService->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) || $forUser) {

            $begin = $this->getBeginDatePersonal($forUser);
            $end = $this->getEndDatePersonal($forUser);

            $datetime = self::formatBeginEnd($begin, $end);

        } else {

            switch ($this->timetype) {

                case HM_Lesson_LessonModel::TIMETYPE_FREE:
                    $datetime = _('Не ограничено');
                    break;

                case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                    $begin = $this->getBeginDay();
                    $end = $this->getEndDay();

                    $strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
                    if ($begin == $end)    {
                        $datetime = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
                    } elseif (!$end) {
                        $datetime = sprintf(_("с %s дня %s по курсу"), abs($begin), $strtime);
                    } else {
                        $datetime = sprintf(_("с %s по %s день %s по курсу"), abs($begin), abs($end), $strtime);
                    }

                    break;

                case HM_Lesson_LessonModel::TIMETYPE_DATES:
                case HM_Lesson_LessonModel::TIMETYPE_TIMES:

                    $begin = $this->getBeginDate();
                    $end = $this->getEndDate();

                    $datetime = self::formatBeginEnd($begin, $end);

                    break;
            }
        }


        $details = 1;
        if ($this->getType() == HM_Event_EventModel::TYPE_TEST) {
            /** @var HM_Lesson_Test_TestService $lessonTestService */
            $lessonTestService = $serviceContainer->getService('LessonTest');
            $quest = $lessonTestService->getQuest($this);
            if ($quest->quest_id) {
                $questSettings = $quest->getSettings();
            }
            $details = $questSettings->show_log;

        } elseif ($this->getType() == HM_Event_EventModel::TYPE_RESOURCE) {
            $details = 0;
        }

        switch ($this->getType()) {
            case HM_Event_EventModel::TYPE_ECLASS: {
                $response['targetUrl'] = '_blank';
                break;
            }
            case HM_Event_EventModel::TYPE_COURSE: {
                $response['targetUrl'] = $this->isNewWindow() ?  '_blank' : '_self';
                break;
            }
            default: {
                $response['targetUrl'] = '_self';
            }

        }


        $response['currentUserId'] = ($forUser)? $forUser : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $response['isStudentPageForTeacher'] = ($forUser && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN)))? true : false;
        $response['type'] = $types[$this->typeID];
        $response['isEmpty'] = $this->typeID == HM_Event_EventModel::TYPE_EMPTY;
        $response['details'] = $details;
        $response['datetime'] = $datetime;
        $response['lesson'] = $this;
        $response['eventCollection'] = $eventCollection;
        $response['cols'] = $cols;
        $response['recommend'] = $this->recommend;
        $response['title'] = $this->title;


        if ($this->getIcon()) {
            $response['icon']['src'] = $this->getUserIcon() ? $this->getUserIcon() : $this->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM);
            $response['icon']['alt'] = $this->title;
            $response['icon']['title'] = $this->title;
        }
        $response['students'] = array();
        if (isset($this->lesson->students)) {
            foreach ($this->students as $key => $student) {
                $response['students'][$key]['fio'] = $student['fio'];
                if ($this->isRelative()) {
                    $response['students'][$key]['begin_personal'] = $this->date($student['begin_personal']);
                    if ($this->date($student['begin_personal']) != $this->date($student['end_personal'])) {
                        $response['students'][$key]['end_personal'] = $this->date($student['end_personal']);
                    }
                }
            }
        }


        if ($this->teacher && isset($this->teacher[0]) && ($this->teacher[0]->MID > 0)) {
            $response['teacher'] = array('user_id' => $this->teacher[0]->MID, 'fio' => trim($this->teacher[0]->LastName.' '.$this->teacher[0]->FirstName.' '.$this->teacher[0]->Patronymic));
        } else {
            $response['teacher'] = null;
        }
        return $response;
    }

    public function isUnaccessible()
    {
        try {
            $isExecutable = $this->getService('Lesson')->isExecutable($this);

            if ($this->typeID == HM_Event_EventModel::TYPE_EMPTY) {
                return 'Занятия с этим типом недоступны для выполнения';
            } elseif (!$isExecutable) {
                return 'Не выполнено условие доступа к занятию';
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function getEditPlainData($options = [])
    {
        $view = Zend_Registry::get('view');
        $teacher = [
            "photo" => null,
            "name"  => '',
        ];
        if ($this->lessonAssign && count($this->lessonAssign->teacher)) {
            $teacherUser = $this->lessonAssign->teacher->current();
            $teacher = [
                "photo" => $teacherUser->getPhoto() ? $teacherUser->getPhoto() : null,
                "name" => $teacherUser->getName(),
            ];
        } else {
            if($this->teacher instanceof HM_Collection) {
                $teacherModel = $this->teacher->current();
                $teacherId = $teacherModel ? $teacherModel->MID : 0;
            } else {
                $teacherId = $this->teacher;
            }

            $teacherModel = Zend_Registry::get('serviceContainer')
                ->getService('User')
                ->fetchRow(['MID = ?' => $teacherId]);

            if($teacherModel) {
                $teacher = [
                    "photo" => $teacherModel->getPhoto() ?: null,
                    "name" => $teacherModel->getName(),
                ];
            }
        }

        $executeUrl = $view->url([
            'module' => 'subject',
            'controller' => 'lesson',
            'action' => 'index',
            'lesson_id' => $this->SHEID,
            'subject_id' => $this->CID,
        ]);

        if (is_a($this, 'HM_Lesson_Task_TaskModel') && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
                Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), [
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER
            ])) {
            $executeUrl = $view->url([
                'module' => 'task',
                'controller' => 'index',
                'action' => 'preview',
                'task_id' => $this->material_id,
                'lesson_id' => $this->SHEID,
            ]);
        }

        $return = [
            'lessonId' => $this->SHEID,
            'lessonType' => HM_Event_EventModel::getTypeTitle($this),
            'lessonTitle' => $this->getName(),
            'lessonDescription' => trim($this->getDescription()),
            'lessonDate' => $this->getBeginEnd(),
            'lessonCondition' => $this->getCachedLaunchCondition(),

            'teacher' => $teacher,

            'isNewWindow' => $this->isNewWindow(),
            'isNotStrict' => $this->recommend,
            'isPenalty' => (bool)$this->getFormulaPenaltyId(),
            'isScoreable' => (bool) $this->vedomost,
            'isClickable' => $this->typeID !== HM_Event_EventModel::TYPE_EMPTY,

            'iconUrl' => $this->getUserIcon() ? $this->getUserIcon() : $this->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM),
            'executeUrl' => $executeUrl,
            'resultUrl' => $this->getResultsUrl(),
            'editUrl' => [
                'url' => $view->url(['module' => 'subject', 'controller' => 'lesson', 'action' => 'edit', 'lesson_id' => $this->SHEID]),
                'order' => 1,
            ],
            'editMaterialUrl' => [
                'url' => $view->url(['module' => 'subject', 'controller' => 'material', 'action' => 'edit', 'lesson_id' => $this->SHEID]),
                'order' => 2,
            ],
            'changeMaterialUrl' => [
                'url' => $view->url(['module' => 'subject', 'controller' => 'lesson', 'action' => 'change-material', 'lesson_id' => $this->SHEID]),
                'order' => 2,
            ],
            'editAssignUrl' => [
                'url' => $view->url(['module' => 'subject', 'controller' => 'lesson', 'action' => 'edit-assign', 'lesson_id' => $this->SHEID]),
                'order' => 3,
            ],
            'deleteUrl' => [
                'url' => $view->url(['module' => 'subject', 'controller' => 'lesson', 'action' => 'delete', 'lesson_id' => $this->SHEID]),
                'order' => 4,
            ],
            'resultsUrl' => [
                'url' => $view->url(['module' => 'subject', 'controller' => 'results', 'action' => 'index', 'lesson_id' => $this->SHEID]),
                'order' => 5,
            ],
        ];
        if ($this->typeID == HM_Event_EventModel::TYPE_ECLASS && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
            $return['videoUrl'] = [
                'url' => $view->url(['module' => 'eclass', 'controller' => 'video', 'action' => 'index', 'lesson_id' => $this->SHEID]),
                'order' => 6,
            ];
        }
        if ($this->has_proctoring == 1 && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
            $return['proctoringUrl'] = [
                'url' => $view->url(['module' => 'lesson', 'controller' => 'list', 'action' => 'proctored', 'lesson_id' => $this->SHEID, 'subject_id' => $this->CID,]),
                'order' => 7,
            ];
        }

        return $return;
    }

    public function getTeacherAssign()
    {
        if ($assigns = $this->getAssigns()) {
            foreach ($assigns as $assign) {
                if (intval($assign->MID) === 0) {
                    return $assign;
                }
            }
        }
    }

    public function getChatNamespace()
    {
        return HM_ChatMessage_ChatMessageModel::ROOM_TYPE_LESSON.$this->SHEID;
    }

    public function getDateInfo()
    {
		if($this->timetype == 2) $datetime = _('Не ограничено');
		elseif($this->timetype == 1){
			
			// если возможно, показываем сразу абсолютные даты
			if ((Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) ||
                (Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() ==HM_Role_Abstract_RoleModel::ROLE_TEACHER && $forUser))) {
				
                $begin = $this->getBeginDateRelative($forUser);
                $end = $this->getEndDateRelative($forUser);

				if(!$begin) {
					$datetime = sprintf(_("до %s "), $end);
				} elseif(!$end) {
					$datetime = sprintf(_("с %s "), $begin); 
				} elseif ($begin != $end) {
					$datetime = sprintf(_("с %s по %s"), $begin, $end);				
				} else {
					$datetime = $begin;
				}
				
			} else {
				$begin = $this->getBeginDay();
				$end = $this->getEndDay();
				$strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
				if ($begin == $end)	$datetime = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
				elseif(!$end)		$datetime = sprintf(_("с %s дня %s по курсу"), abs($begin), $strtime);
				else 				$datetime = sprintf(_("с %s по %s день %s по курсу"), abs($begin), abs($end), $strtime);
			}
		}
        else{
			$begin = $this->getBeginDate();
			$end = $this->getEndDate();
			if ($begin == $end)	$datetime = sprintf(_("%s, с %s по %s"), $begin, $this->getBeginTime(), $this->getEndTime());
			elseif(!$end)		$datetime = sprintf(_("с %s "), $begin);
			else $datetime = sprintf(_("с %s по %s"), $begin, $end);
        }

        return $datetime;
    }    

    public function getBeginDateRelative($mid = false, $date = null)
    {
        if($date == null){
    	    $assign = $this->getStudentAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->beginRelative;
        }
        return $this->date($date);
    }

    public function getEndDateRelative($mid = false, $date = null)
    {
        if($date == null){
    	    $assign = $this->getStudentAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->endRelative;
        }

    	return $this->date($date);
    }


    public function getBegin($time = false)
    {
        if (!strtotime($this->begin))
            return '';

        $return = $this->date($this->begin);
        if ($time)
            $return .= ' ' . $this->time($this->begin);

        return $return;
    }

    public function getEnd($time = false)
    {
        if (!strtotime($this->end))
            return '';

        $return = $this->date($this->end);
        if ($time)
            $return .= ' ' . $this->time($this->end);

        return $return;
    }

}
