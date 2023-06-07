<?php
class HM_Event_EventModel extends HM_Model_Abstract
{
    const TYPE_EMPTY     = 'empty';
    /** @deprecated  */
    const TYPE_LECTURE   = 'lecture';
    const TYPE_TEST      = 'test';
    const TYPE_EXERCISE  = 'exercise';
    const TYPE_COURSE    = 'course';
    const TYPE_WEBINAR   = 'webinar';
    const TYPE_RESOURCE  = 'resource';
    const TYPE_POLL      = 'poll';
    const TYPE_TASK      = 'task';
    const TYPE_ECLASS    = 'eclass';
    const TYPE_FORUM     = 'forum';

    // DEPRECATED
    const TYPE_OLYMPOX_SELFSTUDY      = 'olympox_selfstudy';
    const TYPE_OLYMPOX_EXAM           = 'olympox_exam';
    const TYPE_OLYMPOX_INTRO          = 'olympox_intro';
    const TYPE_OLYMPOX_SELFSTUDY_DAYS = 'olympox_selfstudy_days'; //day

    //Dean's poll for leader
    const TYPE_DEAN_POLL_FOR_STUDENT = 'dean_poll_for_student';
    const TYPE_DEAN_POLL_FOR_LEADER  = 'dean_poll_for_leader ';
    const TYPE_DEAN_POLL_FOR_TEACHER = 'dean_poll_for_teacher'; // DEPRECATED!

    const TYPE_CURATOR_POLL_FOR_PARTICIPANT = 'curator_poll_for_participant';
    const TYPE_CURATOR_POLL_FOR_LEADER  = 'curator_poll_for_leader';
    const TYPE_CURATOR_POLL_FOR_MODERATOR = 'curator_poll_for_moderator';

    const WEIGHT_DEFAULT = 5;

    static public $events = null;

    static public function getTypes()
    {
        $types = array(
            self::TYPE_EMPTY     => _('Очное мероприятие'),
            self::TYPE_COURSE    => _('Учебный модуль'),
//            self::TYPE_LECTURE   => _('Раздел учебного модуля'),
            self::TYPE_RESOURCE  => _('Инфоресурс'),
            self::TYPE_TEST      => _('Тест'),
            self::TYPE_TASK      => _('Задание'),
            self::TYPE_POLL      => _('Опрос'),
            self::TYPE_FORUM      => _('Форум'),
//            self::TYPE_EXERCISE  => _('Упражнение'),
//            self::TYPE_WEBINAR   => _('Вебинар'),
            self::TYPE_ECLASS    => _('Вебинар'),
        );
        
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_LESSON_TYPES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $types);

        return $event->getReturnValue();
    }

    static function getTypeTitle($lesson)
    {
        $type = $lesson->getType();
        $types = self::getTypes();
        return isset($types[$type]) ? $types[$type] : '' ;
    }

    static public function getTypesAttributes()
    {
        $types = array(
            self::TYPE_COURSE    => 'CID',
            self::TYPE_LECTURE   => 'oid', // ?
            self::TYPE_RESOURCE  => 'resource_id',
            self::TYPE_TEST      => 'quest_id',
            self::TYPE_TASK      => 'task_id',
            self::TYPE_POLL      => 'quest_id',
        );

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_LESSON_TYPES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $types);

        return $event->getReturnValue();
    }

    public static function getEventAndSphinxTypesMap($eventType = null)
    {
        $types = [
            self::TYPE_RESOURCE => HM_Search_Sphinx::TYPE_RESOURCE,
            self::TYPE_COURSE => HM_Search_Sphinx::TYPE_COURSE,
            self::TYPE_POLL => HM_Search_Sphinx::TYPE_POLL,
            self::TYPE_TASK => HM_Search_Sphinx::TYPE_TASK,
            self::TYPE_TEST => HM_Search_Sphinx::TYPE_TEST,
        ];

        if(!empty($eventType)) {
            return $types[$eventType];
        } else {
            return $types;
        }
    }

    static public function getMeetingTypes()
    {
        $types = array(
            self::TYPE_EMPTY    => 'Очное мероприятие',
           //self::TYPE_COURSE   => _('Учебный модуль'),
           // self::TYPE_LECTURE  => _('Раздел учебного модуля'),
//            self::TYPE_RESOURCE => _('Информационный ресурс'),
            self::TYPE_TEST     => _('Тест'),
            //self::TYPE_TASK     => _('Задание'),
//            self::TYPE_EXERCISE => _('Упражнение'),
//            self::TYPE_POLL     => _('Опрос'),
//            self::TYPE_WEBINAR  => _('Вебинар'),
        );

        return $types;
    }

        /**
     * Return array of elements, which would't be in schedule list
     * @return string[]
     */
    static function getExcludedTypes()
    {
        return array(
                    self::TYPE_DEAN_POLL_FOR_STUDENT => _('Опрос слушателей'),
                    self::TYPE_DEAN_POLL_FOR_LEADER  => _('Опрос руководителей'),
                    self::TYPE_DEAN_POLL_FOR_TEACHER => _('Опрос тьюторов')
                );
    }

    static function getDeanPollTypes()
    {
        return self::getExcludedTypes();
    }

    static function getFeedbackPollTypes()
    {
        $types = self::getTypes();
        $result = self::getExcludedTypes();
        $result[self::TYPE_POLL] = $types[self::TYPE_POLL];
        return $result;
    }

    static function getFeedbackPollTypesShort()
    {
        $types = self::getTypes();
        $result = array(
                    self::TYPE_DEAN_POLL_FOR_STUDENT => _('Слушателя'),
                    self::TYPE_DEAN_POLL_FOR_LEADER  => _('Руководителя'),
                    self::TYPE_DEAN_POLL_FOR_TEACHER => _('Тьютора')
                );
        $result[self::TYPE_POLL] = $types[self::TYPE_POLL];
        return $result;
    }

    /**
     * Return all types including self types
     * Activities, and custom events
     *
     * @param bool $returnEmptyType999 default="true" return delimiter between self types and custom types as [999] => '---'
     *
     * @return array of events
     */
    static public function getAllTypes($returnEmptyType999 = true)
    {
        $types = self::getTypes();
//        $activities = HM_Activity_ActivityModel::getEventActivities();
//        foreach($activities as $id => $activity) {
//            $types[$id] = $activity;
//        }

        // Добавляем Custom Events
        if (self::$events === null) {
            self::$events = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll(null, 'title');
        }

        if (count(self::$events)) {
            if($returnEmptyType999){
                $types[999] = '---';
            }
            foreach(self::$events as $event) {
                $types[-$event->event_id] = $event->title;
            }

        }

        return $types;
    }
    static public function getAllMeetingTypes($returnEmptyType999 = true)
    {
        $types = self::getTypes();
        $activities = HM_Activity_ActivityModel::getEventActivities();
        $types[HM_Activity_ActivityModel::ACTIVITY_FORUM] = $activities[HM_Activity_ActivityModel::ACTIVITY_FORUM];
        /*foreach($activities as $id => $activity) {
            $types[$id] = $activity;
        }

        // Добавляем Custom Events
        if (self::$events === null) {
            self::$events = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll(null, 'title');
        }

        if (count(self::$events)) {
            if($returnEmptyType999){
                $types[999] = '---';
            }
            foreach(self::$events as $event) {
                $types[-$event->event_id] = $event->title;
            }

        }*/

        return $types;
    }

    public function getIcon()
    {
        if (file_exists(Zend_Registry::get('config')->path->upload->event.$this->event_id.'.jpg')) {
            return Zend_Registry::get('config')->url->base.'upload/events/'.$this->event_id.'.jpg';
        }
        return false;
    }

    static function getCuratorPollTypes()
    {
        return array(
            self::TYPE_CURATOR_POLL_FOR_PARTICIPANT => _('Опрос участников'),
            self::TYPE_CURATOR_POLL_FOR_LEADER      => _('Опрос руководителей'),
            self::TYPE_CURATOR_POLL_FOR_MODERATOR   => _('Опрос модераторов')
        );
    }

    static function getTypeConstant($type)
    {
        $class = new ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());

        return $constants[$type];
    }

    //для совместимости с мобильным приложением
    static public function convertType($newType)
    {
        $map = array(
            self::TYPE_LECTURE   => 1024,
            self::TYPE_TEST      => 2048,
            self::TYPE_EXERCISE  => 2049,
            self::TYPE_COURSE    => 2050,
            self::TYPE_WEBINAR   => 2051,
            self::TYPE_RESOURCE  => 2052,
            self::TYPE_POLL      => 2053,
            self::TYPE_TASK      => 2054,
            self::TYPE_ECLASS    => 2061,
        );
        return $map[$newType];
    }
}