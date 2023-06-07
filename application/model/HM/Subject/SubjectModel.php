<?php
class HM_Subject_SubjectModel extends HM_Model_Abstract implements HM_Model_ContextInterface, HM_Rest_Interface
{
    // Тип обучения
    const TYPE_FULLTIME = 0; //Очное
    const TYPE_DISTANCE = 1; // Дистанционное

    // Тип курса
    const BASETYPE_PRACTICE = 0; // Учебный
    const BASETYPE_BASE     = 1; // Базовый
    const BASETYPE_SESSION  = 2; // Сессия

    // Тип регистрации
    /** @deprecated */
    const REGTYPE_FREE  = 0; // deprecated
    const REGTYPE_SELF_ASSIGN = 1; // самостоятельная регистация и назначение
    const REGTYPE_ASSIGN_ONLY   = 2; // только назначение

    // Работа с заявками (claimant_process_id (?))
    const APPROVE_NONE  = 0; // без согласования
    const APPROVE_MANAGER = 1; // согласование менеджером
    const APPROVE_PROGRAMM = 2; // согласование по программе

    const ICON_NORMAL = 0;
    const ICON_FULL = 1;

    // Ограничение времени обучения
    const PERIOD_DATES = 0; // Диапазон дат
    const PERIOD_FREE  = 1; // Без ограничений
    const PERIOD_FIXED = 2; // Фиксированная длительность

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

    // Перевод в прошедшие обучение
    const MODE_SET_GRADUATE_STATUS_MODER = 0;
    const MODE_SET_GRADUATE_STATUS_AUTO  = 1;

    // Статус курсов
    const MSG_STATUS_ACTIVE	= 'Идёт';
    const MSG_STATUS_END	= 'Курс завершён';

    // Тип ограничения времени
    const PERIOD_RESTRICTION_STRICT = 0; // Строгое ограничение
    const PERIOD_RESTRICTION_DECENT = 1; // Нестрогое ограничение
    const PERIOD_RESTRICTION_MANUAL = 2; // Ручной старт

    // Агрегатные состояния курса
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    const THUMB_WIDTH = 640;
    const THUMB_HEIGHT = 320;

    const BUILTIN_COURSE_LABOR_SAFETY   = 1;
    const BUILTIN_COURSE_FIRE_SAFETY    = 2;
    const BUILTIN_COURSE_ELECTRO_SAFETY = 3;
    const BUILTIN_COURSE_INDUSTRIAL_SAFETY = 4;

    const SUBJECT_CATALOG_FILTER_NAMESPACE = 'subject-catalog';

    protected $_primaryName = 'subid';

    //теперь сервис Subject будет норм модели возвращать для  всех курсов
    static public function factory($data, $default = 'HM_Subject_SubjectModel')
    {
        if (isset($data['provider_type'])) {
            switch ($data['provider_type']) {
                case HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER:
                    return parent::factory($data, 'HM_Tc_Subject_StudyCenter_SubjectModel');
                case HM_Tc_Provider_ProviderModel::TYPE_PROVIDER:
                    return parent::factory($data, 'HM_Tc_Subject_SubjectModel');
                /*default:
                    return parent::factory($data, $default);*/
            }
        }
        return parent::factory($data, $default);
    }

    static public function getBuiltInCourses()
    {
        return [];
    }

    /**
     * Return type of subject
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
     * Return True if there is a base subject
     * @return bool
     */
    public function isBase()
    {
        if($this->base == self::BASETYPE_BASE)
        {
            return true;
        }
    }

    public function isNew()
    {
        return (strtotime(date('Y-m-d') . ' -2 month') < strtotime($this->created)) ? 1 : 0;
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
            self::PERIOD_FIXED => _('Фиксированная длительность')
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

    public function getStudents()
    {

        $result = array();
        if (isset($this->students))
        {
            $result = $this->students;
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

    public function getTeachers()
    {
        $result = array();
        if (isset($this->teachers))
        {
            $result = $this->teachers;
        }

        return $result;

    }

    public function isStudent($studentId)
    {

        $students = $this->getStudents();
        if (count($students))
        {
            foreach ( $students as $student )
            {
                if ($studentId == $student->MID)
                    return $student;
            }
        }
        return false;

    }

    public function isClaimant($studentId)
    {

        $claimants = $this->getClaimants();
        if (count($claimants))
        {
            foreach ( $claimants as $claimant )
            {
                if (($claimant->MID == $studentId) && ($claimant->status == HM_Role_ClaimantModel::STATUS_NEW)) // чтоб можно было подавать повторно
                    return true;
            }
        }
        return false;
    }

    public function isGraduated($studentId)
    {

        $graduated = $this->getGraduated();
        if (count($graduated))
        {
            foreach ( $graduated as $user )
            {
                if ($user->MID == $studentId)
                    return true;
            }
        }
        return false;
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_FULLTIME => _('Очный'),
            self::TYPE_DISTANCE => _('Дистанционный'));

    }

    public function getType($type  = null)
    {
        switch ($this->provider_type)
        {
            case HM_Tc_Provider_ProviderModel::TYPE_NONE:
                return $this->type == self::TYPE_FULLTIME ? _('Очный') : _('Дистанционный');
                break;
            case HM_Tc_Provider_ProviderModel::TYPE_PROVIDER:
                return _('Внешний');
                break;
        }

        return null;
    }

    private function getEducationType()
    {
        switch ($this->provider_type) {
            case HM_Tc_Provider_ProviderModel::TYPE_NONE:
                return $this->is_fulltime == self::TYPE_FULLTIME ? _('Очный') : _('Дистанционный');
                break;
            case HM_Tc_Provider_ProviderModel::TYPE_PROVIDER:
                return _('Внешний');
                break;
        }

        return null;
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

        if ($regtype == NULL) {
            $regtype = $this->reg_type;
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
        $types = HM_Event_EventModel::getTypes();
        if ($this->services) {
            foreach(HM_Activity_ActivityModel::getEventActivities() as $id => $name) {
                if (($this->services & $id) || in_array($id, HM_Activity_ActivityModel::getFreeEventActivities())) {
                    $types[$id] = $name;
                }
            }
        } else {
            foreach(array_intersect_key(HM_Activity_ActivityModel::getEventActivities(), array_flip(HM_Activity_ActivityModel::getFreeEventActivities())) as $id => $name) {
                $types[$id] = $name;
            }
        }

        // Добавляем Custom Events
        $events = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll(null, 'title');
        if (count($events)) {
            $types[999] = '---';
            foreach($events as $event) {
                $types[-$event->event_id] = $event->title;
            }

        }

        return $types;
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

    public function getRoom()
    {
        /** @var HM_Subject_Room_RoomService $subjRoomService */
        $subjRoomService = Zend_Registry::get('serviceContainer')->getService('SubjectRoom');
        $item =  $subjRoomService->fetchAllDependence('Room',
            $subjRoomService->quoteInto('cid = ?', $this->subid))->current();

        if ($item && $item->room) {
            return $item->room->current()->name;
        }
        return '';
    }

    public function getProvider()
    {
        if (!$this->supplier_id) {
            return '';
        }

        /** @var HM_Supplier_SupplierService $suppService */
        $suppService = Zend_Registry::get('serviceContainer')->getService('Supplier');
        $item =  $suppService->fetchAll('supplier_id='.$this->supplier_id)->current();

        return $item ? $item->title : '';
    }

    /**
     * Дата начала курса для студента.
     *
     * В зависимости от типа ограничения прохождения курса по времени
     * возвращается актуальная дата начала курса.
     */
    public function getBeginForStudent($fromProgram = false)
    {
        if ($fromProgram) {
            // Относительно зачисления студента на курс
            $studentCourseData = Zend_Registry::get('serviceContainer')->getService('Student')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
                    array(
                        'CID = ?' => $this->subid,
                        'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
                    )
                )
            );
            $beginDate = $studentCourseData->time_registered;
            return (strtotime($beginDate)) ? $this->date($beginDate) : '';
        }

        switch($this->period){
            case self::PERIOD_FIXED:
                // Относительно зачисления студента на курс
                $studentCourseData = Zend_Registry::get('serviceContainer')->getService('Student')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
                        array(
                            'CID = ?' => $this->subid,
                            'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
                        )
                    )
                );
                $beginDate = $studentCourseData->time_registered;
                break;
            case self::PERIOD_DATES:
                // Время действия самого курса
                $beginDate = $this->getBegin();
                break;
            case self::PERIOD_FREE:
                // Без ограничений
            default:
                $beginDate = '';
        }
        return (strtotime($beginDate)) ? $this->date($beginDate) : '';
    }

    /**
     * Дата окончания курса для студента.
     *
     * В зависимости от типа ограничения прохождения курса по времени
     * возвращается актуальная дата окончания курса.
     */
    public function getEndForStudent($fromProgram = false)
    {
        if ($fromProgram) {
            // Относительно зачисления студента на курс
            $studentCourseData = Zend_Registry::get('serviceContainer')->getService('Student')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
                    array(
                        'CID = ?' => $this->subid,
                        'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
                    )
                )
            );
            $getEnd = $studentCourseData->end_personal;
            return (strtotime($getEnd)) ? $this->date($getEnd) : '';
        }

        switch($this->period){
            case self::PERIOD_FIXED:
                // Относительно зачисления студента на курс
                $studentCourseData = Zend_Registry::get('serviceContainer')->getService('Student')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
                        array(
                            'CID = ?' => $this->subid,
                            'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
                        )
                    )
                );
                $getEnd = $studentCourseData->end_personal;
                break;
            case self::PERIOD_DATES:
                // Время действия самого курса
                $getEnd = $this->getEnd();
                break;
            case self::PERIOD_FREE:
                // Без ограничений
            default:
                $getEnd = '';
        }
        return (strtotime($getEnd)) ? $this->date($getEnd) : '';
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

    public function getName()
    {
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

    public function getUserIcon($full = false)
    {
        $full = $full ? '-full' : '';
        $path = HM_Subject_SubjectModel::getIconFolder($this->subid) . $this->subid . $full . '.jpg';
        if(is_file($path)){
            return preg_replace('/^.*public\//', Zend_Registry::get('config')->url->base, $path) . '?_=' . @filemtime($path);
        }
        return null;
    }


    public function getIconBanner()
    {
        $path = $this->banner_url;
        return $path;
    }


    public function getDefaultIcon() {
        if($this->type == self::TYPE_DISTANCE){
            return ($this->isSession()) ?
                Zend_Registry::get('config')->url->base.'images/icons/academic-hat.svg' :
                Zend_Registry::get('config')->url->base.'images/icons/academic-hat.svg';
        }else{
            return ($this->isSession()) ?
                Zend_Registry::get('config')->url->base.'images/icons/academic-hat.svg' :
                Zend_Registry::get('config')->url->base.'images/icons/academic-hat.svg';
        }
    }

    public function getIconHtml()
    {
        $result = '';
        if (!$icon = $this->getUserIcon()) {
            $icon = $this->getDefaultIcon();
            $result.= '<v-img src="'.$icon.'" :width="\'100%\'" :height="\'150\'" color="primary" class="primary default-subject-icon"></v-img>';
        } else {
            $result.='<v-img src="'.$icon.'" :width="\'100%\'" :height="\'150\'"></v-img>';
        }

        return $result;
    }

    public static function getIconFolder($subjectId = 0)
    {

        $folder = Zend_Registry::get('config')->path->upload->subject;

        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;

        $folder = $folder . floor($subjectId / $maxFilesPerFolder) . '/';

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }
        return $folder;
    }

    public static function getIconBannerFolder($subjectId = 0)
    {
        $folder = static::getIconFolder($subjectId);
        $folder = $folder . 'banner/';
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
                             HM_Role_Abstract_RoleModel::ROLE_HR,
                             HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                             HM_Role_Abstract_RoleModel::ROLE_DEAN)) ||
              !in_array($mca, array('subject:index:card'))) {
            return $this->getModeName();
        }

        $modes  = self::getModes();
        $select = new Zend_Form_Element_Select('subjectsetmode_new_mode',
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
                       array(HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                             HM_Role_Abstract_RoleModel::ROLE_HR,
                             HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                             HM_Role_Abstract_RoleModel::ROLE_DEAN)) ||
              !in_array($mca, array('subject:index:card'))) {
            return self::getStateTitle($this->state);
        }

        $states  = self::getAvailableStates();
        $select = new Zend_Form_Element_Select('subjectsetstate_new_mode',
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
        return '<div class="color_field" style="width: 113px; height: 22px; border-radius: 2px; background-color: #' . $this->base_color . '"></div>';
    }

    static public function getPeriodRestrictionTypes()
    {
        return array(
            self::PERIOD_RESTRICTION_STRICT=> _('Строгое ограничение'),
            self::PERIOD_RESTRICTION_DECENT   => _('Нестрогое ограничение'),

// этот режим нам нужен при назначении курсов из программы. поэтому здесь запретим
//            self::PERIOD_RESTRICTION_MANUAL   => _('Начало и окончание обучения только по факту подтверждения тьютором'),
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

    // DEPRECATED!!!
    // используйте $subjectUser->isSubjectUnaccessible()
    public function isAccessible($fromProgram = false)
    {
        $userId  = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();

        $student = Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
            Zend_Registry::get('serviceContainer')->getService('Student')->quoteInto(
                array(
                    ' CID = ? AND ',
                    ' MID = ? '
                ),
                array(
                    $this->subid,
                    $userId,
                )
            )
        )->current();

        $now = time();
        $begin = $student->time_registered;
        $end   = $student->time_ended_planned;

        if ($fromProgram) {
            switch ($this->period_restriction_type) {
                case self::PERIOD_RESTRICTION_DECENT:
                    return true;
                    break;
                case self::PERIOD_RESTRICTION_STRICT:
                    return ($now < strtotime($end)) && ($now > strtotime($begin));
                    break;
                case self::PERIOD_RESTRICTION_MANUAL:
                    return
                        (new HM_Date($this->begin) >= new HM_Date()) ?
                            true :
                            ($this->state == self::STATE_ACTUAL);
                    break;
            }
        } else {
            if ($this->period == self::PERIOD_DATES) {
                switch ($this->period_restriction_type) {
                    case self::PERIOD_RESTRICTION_DECENT:
                        return true;
                        break;
                    case self::PERIOD_RESTRICTION_STRICT:
                        return ($now < strtotime($end)) && ($now > strtotime($begin));
                        break;
                    case self::PERIOD_RESTRICTION_MANUAL:
                        return
                            ((new HM_Date($begin) >= new HM_Date()) || $this->is_labor_safety) ?
                                true :
                                ($this->state == self::STATE_ACTUAL);
                        break;
                }
            }
        }

        return true;
    }

    static public function getStates()
    {
        return array(
            self::STATE_PENDING  => _('Не начато'),
            self::STATE_ACTUAL   => _('Идёт'),
            self::STATE_CLOSED => _('Закончено'),
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

    public function getShortDescription(){
        return $this->short_description;
    }

    public function getIconClass()
    {
        return 'subject';
    }
    public function getCardUrl()
    {
        if(!$this->subid) return false;
        return array(
            'module' => 'subject',
            'controller' => 'list',
            'action' => 'card',
            'course_id' => $this->subid,
        );
    }

    public function getDescriptionUrl()
    {
        if(!$this->subid) return false;
        return array(
            'module' => 'subject',
            'controller' => 'index',
            'action' => 'description',
            'subject_id' => $this->subid,
        );
    }

    public function getCreateUpdateDate()
    {
        $return = sprintf(_('Создан: %s'), $this->dateTime($this->created));
        if ($this->created != $this->last_updated) {
            $return .= ', ' . sprintf(_('обновлён: %s'), $this->dateTime($this->last_updated));
        }
        return $return;
    }

    public function getViewUrl()
    {
        if(!$this->subid) return false;
        return $this->getService()->getViewUrl($this->subid);
    }

    public function getDefaultUri($force = false)
    {
        if (!empty($this->default_uri) && ($force || Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {

            // что это??
            $uri = str_replace(array(
                    'lesson/list/index',
            ), array(
                    'subject/lessons/my',
            ), $this->default_uri);

            return $uri;

        } else {
            $view = Zend_Registry::get('view');
            return $view->url(array('module' => 'subject', 'controller' => 'lessons', 'action' => 'index', 'subject_id' => $this->subid), null, true);
        }
    }

    public function getServiceName()
    {
        return 'Subject';
	}

    static public function getClaimantProcessTitles()
    {
        return array(
            self::APPROVE_NONE => _('Без согласования'),
            self::APPROVE_MANAGER => _('Согласование менеджером по обучению'),
//            self::APPROVE_PROGRAMM => _('Программа согласования'),
        );
    }
    public function getAbsenceUsers($userIds)
    {
        if ($this->absenceUsers === null) {
            switch($this->period) {
                case HM_Subject_SubjectModel::PERIOD_FREE:
                if (!$this->lessons) {
                        $this->lessons = $this->getService()->getService('Lesson')->fetchAll(
                            $this->getService()->quoteInto('CID = ?', $this->subid)
                        );
                    }

                    /** @var HM_Lesson_LessonModel $lesson */
                    foreach ($this->lessons as $lesson) {
                        switch ($lesson->timetype) {
                            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                                continue 2;
                                break;
                            case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                            case HM_Lesson_LessonModel::TIMETYPE_DATES:
                                $lessonBegin = new HM_Date($lesson->getBeginDate());
                                $lessonEnd = new HM_Date($lesson->getEndDate());
                                break;
                            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                                $lessonBegin = new HM_Date();
                                $lessonEnd = new HM_Date();
                                // в стартдей и стопдей хранятся секунды, хоть и ограничение по дням
                                $lessonBegin = $lessonBegin->addDay($lesson->startday / 86400);
                                $lessonEnd = $lessonEnd->addDay($lesson->stopday / 86400);
                                break;
                        }

                        if(isset($lessonBegin)) {//почему-то не срабатывал continie в случае ::TIMETYPE_FREE!
                            if (!isset($begin)) {
                                $begin = new HM_Date($lessonBegin);
                            } else {
                                $begin = ($lessonBegin < $begin) ? $lessonBegin : $begin;
                            }
                            if (!isset($end)) {
                                $end = new HM_Date($lessonEnd);
                            } else {
                                $end = ($lessonEnd > $end) ? $lessonEnd : $end;
                            }
                        }
                    }
                    break;
                case HM_Subject_SubjectModel::PERIOD_DATES:
                    $begin = new HM_Date($this->getBegin());
                    $end = new HM_Date($this->getEnd());
                    break;
                case HM_Subject_SubjectModel::PERIOD_FIXED:
                    $begin = new HM_Date();
                    $end = new HM_Date();
                    $end = $end->addDay($this->longtime);
                    break;
            }
            $this->absenceUsers = array();
            if (isset($begin) && isset($end)) {
                $this->absenceUsers = $this->getService()->getService('User')->fetchAllJoinInner('Absence',
                    $this->getService()->quoteInto(
                        array('MID IN (?) ', 'AND Absence.absence_begin <= ? ', ' AND Absence.absence_end >= ?'),
                        array($userIds, $begin->toString(HM_Date::SQL), $end->toString(HM_Date::SQL))
                    )
                )->asArrayOfObjects();
            }
        }
        return $this->absenceUsers;
    }

    public function getCachedSubjectTeachers()
    {
        if ($collection = $this->getCachedValue('subjectId2TeacherUser', $this->subid)) {
            return $collection;
        }
        return array();
    }

    public function getCachedSubjectAvailableLessons()
    {
        if ($collection = $this->getCachedValue('subjectId2AvailableLessons', $this->subid)) {
            return $collection;
        }
        return array();
    }

    public function getCardFieldsValue($fromProgramArray)
    {
        $fields = [];

        if ($shortDescription = $this->short_description) {
            $fields[] = ['key' => _('Краткое описание'), 'value' => $shortDescription];
        }
        // ограничение времени обучения
        $fromProgram = in_array($this->subid, $fromProgramArray);
        if ($this->period == HM_Subject_SubjectModel::PERIOD_FREE && !$fromProgram) {
            $fields[] = ['key' =>  _('Ограничение времени обучения'), 'value' =>  $this->getPeriod()];
        } else {
            if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),  HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
                $fields[] = ['key' => _('Дата начала обучения'), 'value' => $this->getBeginForStudent($fromProgram)];
                $fields[] = ['key' => _('Дата окончания обучения, не позднее'), $this->getEndForStudent($fromProgram)];
            } else {
                if ($this->period == HM_Subject_SubjectModel::PERIOD_FIXED) {
                    $fields[] = ['key' => _('Ограничение времени обучения'), 'value' => $this->getLongtime()];
                } else { // PERIOD_DATES
                    $fields[] = ['key' => _('Дата начала'), 'value' => $this->getBegin()];
                    $fields[] = ['key' => _('Дата окончания'), 'value' => $this->getEnd()];
                }
            }
        }

        if ($provider = $this->getProvider()) {
            $fields[] = ['key' => _('Провайдер обучения'), 'value' => $provider];
        }

        if ($room = $this->getRoom()) {
            $fields[] = ['key' => _('Место проведения'), 'value' => $room];
        }

        if ($type = $this->getEducationType()) {
            $fields[] = ['key' => _('Тип'), 'value' => $type];
        }

        if ($price = $this->getPriceWithCurrency()) {
            $fields[] = ['key' => _('Цена'), 'value' => $price];
        }

        if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:calendar')) {
            $fields[] = ['key' => _('Цвет в календаре'), 'value' => $this->getColorField()];
        }

        $teachers = $this->getService('Subject')->getAssignedTeachers($this->subid);
        foreach($teachers as $teacher) {
            $fields[] = ['key' => _('Тьютор'), 'value' => $teacher->getName()];
        }

        return $fields;
    }

    public function getRestDefinition()
    {
        return [
            'id' => (int)$this->subid,
            'externalId' => (string)$this->external_id,
            'title' => (string)$this->name,
            'description' => (string)$this->description,
            'image_url' => (string)$this->getUserIcon(),
            'assignment_type' => (string)$this->getRestAssignmentType(),
            'application_type' => (string)$this->getRestApplicationType(),
            'date_type' => (string)$this->getRestDateType(),
            'date_begin' => (string)$this->getBegin(true),
            'date_end' => (string)$this->getEnd(true),
            'duration' => (int)$this->longtime,
        ];
    }


    /**
     * assignment_type == Тип регистрации
     *
     * @param null $type
     * @return string
     */
    private function getRestAssignmentType($type = null)
    {
        $assignmentTypes = self::getRestAssignmentTypes();

        if ($type == NULL) {
            $type = $this->reg_type;
        }

        return $assignmentTypes[$type];
    }

    static function getRestAssignmentTypes()
    {
        return [
            self::REGTYPE_SELF_ASSIGN => 'apply-or-assign',
            self::REGTYPE_ASSIGN_ONLY => 'assign-only'
        ];
    }

    /**
     * @param null $type
     * @return string
     */
    private function getRestApplicationType($type = null)
    {
        $regtypes = $this->getRestApplicationTypes();

        if ($type == NULL) {
            $type = $this->claimant_process_id;
        }

        return $regtypes[$type];
    }

    static public function getRestApplicationTypes()
    {
        return [
            self::APPROVE_NONE  => 'free',
            self::APPROVE_MANAGER  => 'moderated',
        ];
    }

    /**
     * date_type == Ограничение времени обучения
     *
     * @param null $type
     * @return string
     */
    public function getRestDateType($type = null)
    {
        $periods = self::getRestDateTypes();

        if ($type == NULL) {
            $type = $this->period;
        }

        return $periods[$type];
    }

    static public function getRestDateTypes()
    {
        return [
            self::PERIOD_FREE  => 'unlimited',
            self::PERIOD_DATES => 'range',
            self::PERIOD_FIXED => 'duration'
        ];
    }

}
