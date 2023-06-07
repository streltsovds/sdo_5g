<?php

/**
 * @property int $meeting_id ID занятия
 * @property string $title
 * @property int $CID ID курса
 * @property int $typeID ID типа занятия см. @link HM_Activity_ActivityModel
 * @property int $vedomost
 * @property int $moderator
 * @property int $teacher
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

abstract class HM_Meeting_MeetingModel extends HM_Model_Abstract implements HM_Meeting_MeetingModel_Interface
{
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
    const CONDITION_MEETING    = 4;

    const ICON_LARGE    = 105;
    const ICON_MEDIUM   = 64;

    const DATE_UNLIMITED = 'unlimited';
    
    const SORT_ORDER_DEFAULT = 0;
    const SORT_ORDER_OVERDUE = 1;
    const SORT_ORDER_FREE = 2;
    
    protected $_sortOrder;
    
    public $material;

    static public function getDateTypes(){

        return array(
            self::TIMETYPE_FREE      => _('Без ограничений'),
            self::TIMETYPE_DATES     => _('Диапазон дат'),
            self::TIMETYPE_TIMES     => _('Диапазон времени'),
        );

    }

    static public function getConditionTypes(){

        return array(
            self::CONDITION_NONE      => _('Без условия'),
            self::CONDITION_PROGRESS  => _('Процент выполнения'),
            self::CONDITION_AVGBAL    => _('Средний балл по курсу'),
            self::CONDITION_SUMBAL    => _('Суммарный балл по курсу'),
            self::CONDITION_MEETING    => _('Выполнение другого занятия')
        );

    }

    static public function factory($data, $default = 'HM_Meeting_MeetingModel')
    {

        if (isset($data['typeID']))
        {
            if ($data['typeID'] < 0) {
                return parent::factory($data, 'HM_Meeting_Custom_CustomModel');
            }

            switch($data['typeID']) {
                case HM_Event_EventModel::TYPE_POLL:
                    return parent::factory($data, 'HM_Meeting_Poll_PollModel');
                    break;
                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_EXERCISE:
                    return parent::factory($data, 'HM_Meeting_Test_TestModel');
                    break;
                case HM_Event_EventModel::TYPE_TASK:
                    return parent::factory($data, 'HM_Meeting_Task_TaskModel');
                    break;
                case HM_Event_EventModel::TYPE_LECTURE:
                    return parent::factory($data, 'HM_Meeting_Lecture_LectureModel');
                    break;
                case HM_Event_EventModel::TYPE_EMPTY:
                    return parent::factory($data, 'HM_Meeting_Empty_EmptyModel');
                    break;
                case HM_Event_EventModel::TYPE_COURSE:
                    return parent::factory($data, 'HM_Meeting_Course_CourseModel');
                    break;
                case HM_Event_EventModel::TYPE_WEBINAR:
                    return parent::factory($data, 'HM_Meeting_Webinar_WebinarModel');
                    break;
                case HM_Event_EventModel::TYPE_RESOURCE:
                    return parent::factory($data, 'HM_Meeting_Resource_ResourceModel');
                    break;
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
                    return parent::factory($data, 'HM_Meeting_Poll_Curator_Leader_LeaderModel');
                    break;
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT:
                    return parent::factory($data, 'HM_Meeting_Poll_Curator_Participant_ParticipantModel');
                    break;
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                    return parent::factory($data, 'HM_Meeting_Poll_Curator_Moderator_ModeratorModel');
                    break;
                default:
                    // Если занятие на основе сервиса взаимодействия
                    $activities = HM_Activity_ActivityModel::getActivityServices();
                    if (isset($activities[$data['typeID']])) {
                        $service = HM_Activity_ActivityModel::getActivityService($data['typeID']);

                        if (!Zend_Registry::get('serviceContainer')->hasService($service)) {
                            throw new HM_Exception(sprintf(_('Service %s not found'), $service));
                        }

                        if (!method_exists(Zend_Registry::get('serviceContainer')->getService($service), 'getMeetingModelClass')) {
                            throw new HM_Exception(sprintf(_('Method getMeetingModelClass not found in service %s'), $service));
                        }

                        $class = Zend_Registry::get('serviceContainer')->getService($service)->getMeetingModelClass();
                        return parent::factory($data, $class);
                    }
                    break;
            }
        }
        if ($default != 'HM_Meeting_MeetingModel') {
            return parent::factory($data, $default);
        }
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

    public function getParticipantScore($participantId)
    {
        if ($participantId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $participantId) {
                        return $assign->getScore();
                    }
                }
            }
        }
        return false;
    }

    public function getParticipantAssign($participantId)
    {
        if ($participantId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $participantId) {
                        return $assign;
                    }
                }
            }
        }
        return false;
    }

    public function getParticipantComment($participantId)
    {
        if ($participantId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $participantId) {
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
            $date = new Zend_Date(strtotime($registered) + $this->startday);
            $this->begin = $date->get(Zend_Date::DATETIME);
        }

        return $this->dateTimeWithoutSeconds($this->begin);
    }

    public function getEndDatetime($registered = null)
    {
        if ($this->isRelative() && (null !== $registered)) {
            $date = new Zend_Date(strtotime($registered) + $this->stopday);
            $this->end = $date->get(Zend_Date::DATETIME);
        }

        return $this->dateTimeWithoutSeconds($this->end);
    }

    public function getBeginDate()
    {
        return $this->date($this->begin);
    }

    public function getEndDate()
    {
        return $this->date($this->end);
    }

    public function getBeginDateRelative($mid = false, $date = null)
    {
        if($date == null){
    	    $assign = $this->getParticipantAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->beginRelative;
        }
        return $this->date($date);
    }

    public function getEndDateRelative($mid = false, $date = null)
    {
        if($date == null){
    	    $assign = $this->getParticipantAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->endRelative;
        }

    	return $this->date($date);
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

    public function isParticipantAssigned($participantId)
    {
        if (isset($this->assigns)) {
            foreach($this->assigns as $assign) {
                if ($assign->MID == $participantId) {
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
                    list($key, $value) = explode('=', $line);
                    $params[$key] = $value;
                }
            }
        }
        return array_filter($params);
    }

    public function isExecutable()
    {

        $now = new Zend_Date();

        if ($this->recommend) return true;
        if ($this->timetype == self::TIMETYPE_FREE) return true; // занятие без ограничений
        if (!$this->timetype && $now->isLater(new Zend_Date($this->begin)) && $now->isEarlier(new Zend_Date($this->end))) {
            return true;
        }
        if ($this->timetype == self::TIMETYPE_RELATIVE) { // относительное занятие


            $assign = $this->getParticipantAssign(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            if ($now->isLater(new Zend_Date($assign->beginRelative)) && $now->isEarlier(new Zend_Date($assign->endRelative))) {
            	return true;
            }

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

    public function getNecessaryMeetingsId()
    {
        $ids = array();
        if (strlen($this->cond_sheid)) {
            $ids = explode('#', $this->cond_sheid);
        }

        return $ids;
    }

    public function getMeetingId(){
    	return $this->meeting_id;
    }

    public function isConditionalMeeting()
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
        return 'Meeting';
    }

    public function isResultInTable()
    {
        return true;
    }

    public function isFreeModeEnabled()
    {
        return false;
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
     * @return HM_Meeting_MeetingModel
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
}