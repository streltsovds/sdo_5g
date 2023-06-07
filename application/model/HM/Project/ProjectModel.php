<?php
class HM_Project_ProjectModel extends HM_Model_Abstract
{
    // Тип обучения
    const TYPE_DEFAULT = 0; //Очное
    const TYPE_FULLTIME = 1; //Очное
    const TYPE_DISTANCE = 2; // Дистанционное

    // Базовый/ учебный
    const BASETYPE_PRACTICE = 0;
    const BASETYPE_BASE     = 1;
    const BASETYPE_SESSION  = 2;

    // Тип регистрации
    const REGTYPE_FREE  = 0; // deprecated
    const REGTYPE_SELF_ASSIGN = 1; // самостоятельная регистация и назначение
    const REGTYPE_ASSIGN_ONLY   = 2; // только назначение

    /**
     * Диапазон дат
     * @const int PERIOD_DATES
     */
    const PERIOD_DATES = 0;
    /**
     * Без ограничений
     * @const int PERIOD_FREE
     */
    const PERIOD_FREE  = 1;

    /**
     * Фиксированная длительность
     * @const int PERIOD_FIXED
     */
    const PERIOD_FIXED = 2;


    // Режим просмотра
    const MODE_REGULATED = 0; // Регулярный
    const MODE_FREE    = 1; // Свободный

    // Доступные элементы для свободного просмотра
    const MODE_FREE_COURSES   = 1;
    const MODE_FREE_RESOURCES = 2;
    const MODE_FREE_TESTS     = 4;

    // статусы прохождения
    const MODE_FREE_ELEMENT_STATUS_INCOMPLETE = 0;
    const MODE_FREE_ELEMENT_STATUS_COMPLETE = 1;

    // Количество показываемых элементов в аккордеоне
    const MODE_FREE_ELEMENT_AMOUNT = 5;

    const STATUS_CLAIMANT = 0;
    const STATUS_PARTICIPANT = 1;
    const STATUS_GRADUATED = 2;

    // Перевод в прошедшие обучение
    const MODE_SET_GRADUATE_STATUS_MODER = 0;
    const MODE_SET_GRADUATE_STATUS_AUTO  = 1;

    // Статус курсов
    const MSG_STATUS_ACTIVE	= 'Идёт';
    const MSG_STATUS_END	= 'Курс завершён';

    // Тип ограничения времени
    /**
     * Строгое ограничение
     * @const int PERIOD_RESTRICTION_STRICT
     */
    const PERIOD_RESTRICTION_STRICT = 0;

    /**
     * Нестрогое ограничение
     * @const int PERIOD_RESTRICTION_DECENT
     */
    const PERIOD_RESTRICTION_DECENT = 1;

    /**
     * Ручной старт
     * @const int PERIOD_RESTRICTION_MANUAL
     */
    const PERIOD_RESTRICTION_MANUAL = 2;

    // Агрегатные состояния курса
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    static public function getLearningStatuses()
    {
        return array(
            self::STATUS_CLAIMANT  => _('Заявка подана'),
            self::STATUS_PARTICIPANT   => _('В процессе'),
            self::STATUS_GRADUATED => _('Пройден')
        );
    }

    /**
     * Return type of project
     *
     * @return int
     */
    public function getBaseType()
    {
        return $this->base;
    }

    // только для учебных курсов, содержащих сессии
    public static function getTrainingProcessIds()
    {
        return array(6);

    }

    // для остальных уч.курсов и сессий
    public static function getSessionProcessIds()
    {
        return array(5);

    }



    /**
     * Return True if there is a base project
     * @return bool
     */
    public function isBase()
    {
        if($this->base == self::BASETYPE_BASE)
        {
            return true;
        }
    }

    /*public function isSession()
    {
        if($this->base == self::BASETYPE_SESSION)
        {
            return true;
        }
    } */


    static public function getPeriodTypes()
    {
        return array(
            self::PERIOD_FREE  => _('Без ограничений'),
            self::PERIOD_DATES => _('Диапазон дат'),
        );
    }

    public function getPeriod()
    {
        $periods = self::getPeriodTypes();
        return $periods[$this->period];
    }

    public function getLongtime()
    {
        return sprintf(_('%s дней'), $this->longtime);
    }

    public function getPriceWithCurrency()
    {
        return ($this->price)? number_format($this->price, 2, '.', ' ') . ' ' . $this->price_currency : '' ;
    }

    public function getClassifierLinks()
    {
        if (isset($this->classifierlinks)) {
            return $this->classifierlinks;
        }
        return new HM_Collection();
    }

    public function isClassified($classifierId)
    {
        foreach($this->getClassifiers() as $classifier) {
            if ($classifier->classifier_id == $classifierId) {
                return true;
            }
        }
        return false;
    }

    public function getLessons()
    {

        $result = array();
        if (isset($this->lessons))
        {
            $result = $this->lessons;
        }
        return $result;
    }

    public function getParticipants()
    {

        $result = array();
        if (isset($this->participants))
        {
            $result = $this->participants;
        }
        return $result;
    }

    public function getClaimants()
    {

        $result = array();
        if (isset($this->claimants))
        {
            $result = $this->claimants;
        }
        return $result;
    }

    public function getGraduated()
    {

        $result = array();
        if (isset($this->graduated))
        {
            $result = $this->graduated;
        }
        return $result;
    }

    public function getModerators($list=false)
    {
        $result = array();
        if (!isset($this->moderators))
        {
            $this->moderators=Zend_Registry::get('serviceContainer')->getService('Project')->getModerators($this->projid);
        }
        if ($list && count($this->moderators)){
            foreach($this->moderators as $user) {
                $users[] = Zend_Registry::get('view')->cardLink(Zend_Registry::get('view')->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => $user->MID))).$user->getName();
            }
            $result=implode('<br>',$users);
        }
        else{
            $result = $this->moderators;
        }
        return $result;
    }

    public function isParticipant($participantId)
    {

        $participants = $this->getParticipants();
        if (count($participants))
        {
            foreach ( $participants as $participant )
            {
                if ($participantId == $participant->MID)
                    return true;
            }
        }
        return false;

    }

    public function isModerator($moderatorId)
    {

        $moderators = $this->getModerators();
        if (count($moderators))
        {
            foreach ($moderators as $moderator)
            {
                if ($moderatorId == $moderator->user_id)
                    return true;
            }
        }
        return false;

    }

    public function isClaimant($participantId)
    {

        $claimants = $this->getClaimants();
        if (count($claimants))
        {
            foreach ( $claimants as $claimant )
            {
                if (($claimant->MID == $participantId) && ($claimant->status == HM_Role_ClaimantModel::STATUS_NEW)) // чтоб можно было подавать повторно
                    return true;
            }
        }
        return false;
    }

    public function isGraduated($participantId)
    {

        $graduated = $this->getGraduated();
        if (count($graduated))
        {
            foreach ( $graduated as $user )
            {
                if ($user->MID == $participantId)
                    return true;
            }
        }
        return false;
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_DEFAULT => _('Тип конкурса по умолчанию'),
        );
    }

    public function getType($type  = null)
    {

        $types = $this->getTypes();
        if($type==NULL){
            $type= $this->type;
        }

        return $types[$type];
    }

    static public function getRegTypes()
    {

        return array(
            //self::REGTYPE_FREE  => _('Открытая'),
            self::REGTYPE_SELF_ASSIGN => _('Подача заявки или назначение'),
            self::REGTYPE_ASSIGN_ONLY   => _('Только назначение')
        );
    }

    public function getRegType($regtype = null)
    {

        $regtypes = $this->getRegTypes();


        if($regtype==NULL){
            $regtype= $this->reg_type;
        }


        return $regtypes[$regtype];
    }

    public function getCourses()
    {
        if (isset($this->courses)) {
            return $this->courses;
        }
        return array();
    }

    public function isCourseExists($courseId)
    {
        $courses = $this->getCourses();
        if (count($courses)) {
            foreach($courses as $course) {
                if (isset($course->course_id)) { // Это нужно для проверки в подгрузке только назначений
                    if ($course->course_id == $courseId) return true;
                }
                if (isset($course->CID)) {
                    if ($course->CID == $courseId) return true;
                }
            }

        }
        return false;
    }

    public function getEventTypes()
    {
        $types = HM_Event_EventModel::getMeetingTypes();
//        if ($this->services) {
//            foreach(HM_Activity_ActivityModel::getMeetingActivities() as $id => $name) {
//                if (($this->services & $id) || in_array($id, HM_Activity_ActivityModel::getFreeEventActivities())) {
//                    $types[$id] = $name;
//                }
//            }
//        } else {
//            foreach(array_intersect_key(HM_Activity_ActivityModel::getMeetingActivities(), array_flip(HM_Activity_ActivityModel::getFreeEventActivities())) as $id => $name) {
//                $types[$id] = $name;
//            }
//        }

        // Добавляем Custom Events
        $events = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll("tool in (1000, 2052)", 'title');
        if (count($events)) {
            $types[999] = '---';
            foreach($events as $event) {
                $types[-$event->event_id] = $event->title;
            }

        }

        return $types;
    }

    public function getBegin()
    {
        return (strtotime($this->begin)) ? $this->date($this->begin) : '';
    }

    public function getEnd()
    {
        return (strtotime($this->end)) ? $this->date($this->end) : '';
    }

    public function getBeginForParticipant()
    {
        return $this->getBegin();
		/*$participantCourseData = Zend_Registry::get('serviceContainer')->getService('Participant')->getOne(
			Zend_Registry::get('serviceContainer')->getService('Participant')->fetchAll(
				array(
					'CID = ?' => $this->projid,
					'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
				)
			)
		);
    	return (strtotime($participantCourseData->time_registered)) ? $this->date($participantCourseData->time_registered) : '';*/
    }

    public function getEndForParticipant()
    {
        return $this->getEnd();
		/*$participantCourseData = Zend_Registry::get('serviceContainer')->getService('Participant')->getOne(
			Zend_Registry::get('serviceContainer')->getService('Participant')->fetchAll(
				array(
					'CID = ?' => $this->projid,
					'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
				)
			)
		);
    	return (strtotime($participantCourseData->end_personal)) ? $this->date($participantCourseData->end_personal) : '';*/
    }

    /**
     * Сообщение о статусе курса
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->isExpired() ? _(self::MSG_STATUS_END) : _(self::MSG_STATUS_ACTIVE);
    }

    /**
     * Истёк ли срок курса ?
     *
     * @return boolean
     */
    public function isExpired()
    {
        switch ($this->period) {
        	case self::PERIOD_FREE:
        	case self::PERIOD_FIXED: // невозможно определить, считаем что нет
        		return false;
        	case self::PERIOD_DATES:
        		if ($this->period_restriction_type == self::PERIOD_RESTRICTION_STRICT) {
        		return time() > strtotime($this->end);
        		} elseif ($this->period_restriction_type == self::PERIOD_RESTRICTION_MANUAL) {
        		    if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
                        return $this->state != self::STATE_ACTUAL;
        }
        		}
        return false;
    		break;
    }
        return false;
    }

    public function getName(){
        return $this->name;
    }

    public function getIcon()
    {
        if ($icon = $this->getUserIcon()) {
            return $icon;
        } else {
            return $this->getDefaultIcon();
        }
    }

    public function getUserIcon()
    {
        $path = $path = Zend_Registry::get('config')->path->upload->project . $this->projid . '.jpg';
        if(is_file($path)){
            return preg_replace('/^.*public\//', Zend_Registry::get('config')->url->base, $path);
        }
        return null;
    }

    public function getDefaultIcon() {
        return Zend_Registry::get('config')->url->base.'images/project-icons/default.png';
    }

    public function getIconHtml()
    {
        $defaultIconClass = $defaultIconStyle = '';
        if (!$icon = $this->getUserIcon()) {
            $icon = $this->getDefaultIcon();
            $defaultIconClass = 'hm-project-icon-default';
            $defaultIconStyle = sprintf('background-color: #%s;', $this->base_color ? $this->base_color : '555');
        } else {
            $defaultIconClass = 'hm-project-icon-custom';
            $defaultIconStyle = sprintf('background-color: #%s;', $this->base_color ? $this->base_color : '555');
        }

        return sprintf('<div style="background-image: url(%s); %s; background-repeat: no-repeat; background-size: cover;   background-position: center;" class="hm-project-icon %s" title="%s"></div>', $icon, $defaultIconStyle, $defaultIconClass, $this->name);
    }

    public static function getIconFolder($projectId = 0)
    {

        $folder = Zend_Registry::get('config')->path->upload->project;

        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;

        $folder = $folder . floor($projectId / $maxFilesPerFolder) . '/';

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }
        return $folder;
    }

    public static function getModes(){
        return array(
            self::MODE_FREE      => _('Свободный'),
            self::MODE_REGULATED => _('С планом занятий')
        );
    }


    public function getModeName(){
        $modes = self::getModes();
        return $modes[$this->access_mode];
    }

    public function getModeSwitcher()
    {
        $container = Zend_Registry::get('serviceContainer');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $mca = $request->getModuleName() . ':' .
               $request->getControllerName() . ':' .
               $request->getActionName();

        if (  !in_array($container->getService('User')->getCurrentUserRole(),
                       array(HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                             HM_Role_Abstract_RoleModel::ROLE_CURATOR)) ||
              !in_array($mca, array('project:index:card'))) {
            return $this->getModeName();
        }

        $modes  = self::getModes();
        $select = new Zend_Form_Element_Select('projectsetmode_new_mode',
                                               array('multiOptions'=> $modes,
                                                     'value'       => $this->access_mode));
        $select->removeDecorator('Label')
               ->removeDecorator('HtmlTag');

        return $select->render();
    }

    public function getStateSwitcher()
    {
        $container = Zend_Registry::get('serviceContainer');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $mca = $request->getModuleName() . ':' .
               $request->getControllerName() . ':' .
               $request->getActionName();

        if (  !in_array($container->getService('User')->getCurrentUserRole(),
                       array(/*HM_Role_Abstract_RoleModel::ROLE_MODERATOR,*/
                             HM_Role_Abstract_RoleModel::ROLE_CURATOR)) ||
              !in_array($mca, array('project:index:card'))) {
            return self::getStateTitle($this->state);
        }

        $states  = self::getAvailableStates();
        $select = new Zend_Form_Element_Select('projectsetstate_new_mode',
                                               array('multiOptions'=> $states,
                                                     'value'       => $this->state));
        $select->removeDecorator('Label')
               ->removeDecorator('HtmlTag');

        return $select->render();
    }

    public static function getFreeAccessElements(){
        return array(
            self::MODE_FREE_COURSES   => _('Свободный доступ к учебным модулям'),
            self::MODE_FREE_RESOURCES => _('Свободный доступ к информационным ресурсам'),
            self::MODE_FREE_TESTS     => _('Свободный доступ к тестам')
        );

    }

    public function isSession()
    {
        return ($this->base == self::BASETYPE_SESSION);
    }

    public function getBaseTypeTitle()
    {
        if ($this->isSession()) {
            return _('Учебная сессия');
        }

        return _('Учебный курс');
    }

    public function getColorField()
    {
        if (!$this->base_color) return _('по умолчанию');
        return '<div class="color_field" style="background-color: #' . $this->base_color . '"></div>';
    }

    static public function getPeriodRestrictionTypes()
    {
        return array(
            self::PERIOD_RESTRICTION_STRICT=> _('Строгое ограничение'),
            self::PERIOD_RESTRICTION_DECENT   => _('Нестрогое ограничение'),
//            self::PERIOD_RESTRICTION_MANUAL   => _('Начало и окончание конкурса только по факту подтверждения менеджером'),
        );
    }

    public function getPeriodRestrictionType($type  = null)
    {

        $types = $this->getPeriodTypes();
        if($type==NULL){
            $type= $this->type;
        }

        return $types[$type];
    }

    public function isAccessible()
    {
        if ($this->period == self::PERIOD_DATES) {
    	    $now = time();
            switch ($this->period_restriction_type) {
            	case self::PERIOD_RESTRICTION_DECENT:
            		return true;
            		break;
            	case self::PERIOD_RESTRICTION_STRICT:
            		return ($now < strtotime($this->end)) && ($now > strtotime($this->begin));
            		break;
            	case self::PERIOD_RESTRICTION_MANUAL:
            	    return ($this->state == self::STATE_ACTUAL);
            		break;
            }
        }
        return true;
    }

    static public function getStates()
    {
        return array(
            self::STATE_PENDING  => _('Не начат'),
            self::STATE_ACTUAL   => _('Идёт'),
            self::STATE_CLOSED => _('Закончен'),
        );
    }

    static public function getStateTitle($state)
    {
        $states = self::getStates();
        return $states[$state];
    }

    public function getAvailableStates()
    {
        if ($this->state == self::STATE_PENDING) {
            return array(
                self::STATE_PENDING  => self::getStateTitle(self::STATE_PENDING),
                self::STATE_ACTUAL   => self::getStateTitle(self::STATE_ACTUAL)
            );
        } elseif ($this->state == self::STATE_ACTUAL) {
            return array(
                self::STATE_ACTUAL => self::getStateTitle(self::STATE_ACTUAL),
                self::STATE_CLOSED => self::getStateTitle(self::STATE_CLOSED)
            );
        }
        return array(
            self::STATE_CLOSED => self::getStateTitle(self::STATE_CLOSED)
        );
    }

    public function isStateAllowed($state)
    {
        switch ($this->state) {
        	case self::STATE_PENDING:
        		return $state == self::STATE_ACTUAL;
        		break;
        	case self::STATE_ACTUAL:
        		return $state == self::STATE_CLOSED;
        		break;
        }
        return false;
    }

    public function getGraduatedMsg()
    {
        return _('Курс завершён');
    }

    public function getScale()
    {
        return $this->scale_id ? $this->scale_id : HM_Scale_ScaleModel::TYPE_CONTINUOUS; // default
    }

    public function getDescription(){
        return $this->description;
    }
}