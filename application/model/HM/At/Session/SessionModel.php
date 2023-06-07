<?php
class HM_At_Session_SessionModel extends HM_Model_Abstract implements HM_Process_Model_Interface
{
    // Агрегатные состояния сессии - по аналогии с курсами
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    const ASSIGN_USERS_KEY = 'users';
    const ASSIGN_RESPONDENTS_KEY = 'respondents';

    const MSG_NO_ANY_POSITIONS = 0;
    const MSG_NO_ANY_PROFILES = 1;
    const MSG_VACANCT_POSITION = 2;
    const MSG_INVALID_PROFILE = 3;
    const MSG_INVALID_EVALUATION = 4;
    const MSG_INVALID_POSITION_DATE = 5;
    const MSG_MAIN_WORK_PLACE = 7;
    const MSG_TOP_MANAGERS = 8;
    const MSG_BLOCKED_USER = 9;
    const MSG_BLOCKED_POSITION = 10;
    
//    const MIN_RECORD_OF_SERVICE = 15552000; // полгода - минимальный стаж для участия в оц.сессии (и как участник, и как респондент); дата берётся из People
    
    protected $_primaryName = 'session_id';
    
    public function getServiceName()
    {
        return 'AtSession';
    }
    
    public function isAccessible()
    {
        return $this->state == self::STATE_ACTUAL;
    }    
    
    public function getIcon()
    {
        // @todo: рефакторить в css
        if (array_key_exists($this->programm_type, HM_Programm_ProgrammModel::getTypes())) {
            return Zend_Registry::get('config')->url->base . "images/session-icons/session-{$this->programm_type}.png";
        }
        return Zend_Registry::get('config')->url->base . "images/session-icons/session-1.png";
    }

    public function getIconHtml()
    {
        $icon = $this->getIcon();
        $defaultIconClass = 'hm-subject-icon-default';
        $defaultIconStyle = sprintf('background-color: #%s;', $this->base_color ? $this->base_color : '555');

        return sprintf('<div style="background-image: url(%s); %s; background-repeat: no-repeat; background-size: cover;   background-position: center; height: 120px; " class="hm-subject-icon %s" title="%s"></div>', $icon, $defaultIconStyle, $defaultIconClass, $this->name);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getType()
    {
        switch ($this->programm_type) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                return _('Сессия подбора');
            break;
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                return _('Сессия адаптации');
            break;
            default:
                return _('Сессия оценки');
            break;
        }
    }
    
    public function getBegin()
    {
        return (strtotime($this->begin_date)) ? $this->date($this->begin_date) : '';
    }

    public function getEnd()
    {
        return (strtotime($this->end_date)) ? $this->date($this->end_date) : '';
    }
    
    public function getStateSwitcher()
    {
        $container = Zend_Registry::get('serviceContainer');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $mca = $request->getModuleName() . ':' .
               $request->getControllerName() . ':' .
               $request->getActionName();

        if (  !(in_array(
                $container->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL)) &&
              in_array($mca, array('session:index:card')))
            && !(
                $container->getService('Acl')->inheritsRole($container->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))
                && $mca == 'recruit:vacancy:index'
            )
        ) {
            return self::getStateTitle($this->state);
        }

        foreach ($this->getAvailableStates() as $state) {
            $states[$state] = self::getStateTitle($state);
        };
        $select = new Zend_Form_Element_Select(
            'sessionsetstate_new_mode',
            array(
                'multiOptions' => $states,
                'value'        => $this->state
            )
        );
        $select->removeDecorator('Label')
               ->removeDecorator('HtmlTag');

        return $select->render();
    }
    
    public function getAvailableStates()
    {
        if ($this->state == self::STATE_PENDING) {
            return array(
                self::STATE_PENDING,
                self::STATE_ACTUAL
            );
        } elseif ($this->state == self::STATE_ACTUAL) {
            return array(
                self::STATE_ACTUAL,
                self::STATE_CLOSED
            );
        }
        return array(
            self::STATE_CLOSED
        );
    }
    
    static public function getStates()
    {
        return array(
            self::STATE_PENDING => _('Не начата'),
            self::STATE_ACTUAL => _('Идёт'),
            self::STATE_CLOSED => _('Закончена'),
        );
    } 
    
    static public function getStateTitle($state) 
    {
        $states = self::getStates();
        return $states[$state];
    }

    public function getState() 
    {
        $states = self::getStates();
        return $states[$this->state];
    }

    public function isStateAllowed($state)
    {
        switch ($this->state) {
        	case self::STATE_PENDING:
        		return in_array($state, array(self::STATE_ACTUAL, self::STATE_CLOSED)); // разрешаем досрочное завершение
        		break;
        	case self::STATE_ACTUAL:
        		return $state == self::STATE_CLOSED;
        		break;
        }
        return false;
    }
    
    public function getTimeProgress()
    {
        // Чтобы не больше 100
        return min(
            100,
            ceil(100 * (time() - strtotime($this->begin_date)) / (strtotime($this->end_date) - strtotime($this->begin_date) + 86400)) // end_date - включительно
        );
    }
    
    // @todo: вообще-то ему здесь не место; должно быть 2 метода в AtSessionUser и AtSessionREspondent 
    public function getProgress($participantType = null)
    {
        if (empty($this->events)) {
            $this->events = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->fetchAll(array('session_id = ?' => $this->session_id));
        }
        $countUser = $countRespondent = $totalUser = $totalRespondent = 0;
        $currentUserId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(); 
        foreach ($this->events as $event) {
            if ($event->user_id == $currentUserId) {
                if ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) $countUser++;
                $totalUser++;
            }
            if ($event->respondent_id == $currentUserId) {
                if ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) $countRespondent++;
                $totalRespondent++;
            }
        }
        $progressUser = ($totalUser) ? ceil(100 * $countUser/$totalUser) : false;
        $progressRespondent = ($totalRespondent) ? ceil(100 * $countRespondent/$totalRespondent) : false;
        switch ($participantType) {
            case HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_USER:
                return $progressUser;
            case HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_RESPONDENT:
                return $progressRespondent;
            default:
                return array(
                    HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_USER => $progressUser,
                    HM_At_Session_Event_EventModel::PARTICIPANT_TYPE_RESPONDENT => $progressRespondent,
                );
        }
    }

    public function getCycleTitle()
    {
        if (count($this->cycle)) {
            return $this->cycle->current()->name;
        }
        return '';
    }
    
    public function getOptionsModifier()
    {
        switch ($this->programm_type) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                return HM_Option_OptionModel::MODIFIER_AT;
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                return HM_Option_OptionModel::MODIFIER_RECRUIT;
        }
    }

    /**
     * @return HM_Date|string
     * @throws Zend_Date_Exception
     */
    public function _getBeginPlanify()
    {
        $begin = new HM_Date($this->begin_date);
        $begin = $begin->toString(Zend_Date::DATES);

        if (!empty($this->begin_date)) {
            $begin = sprintf(_('Дата начала: %s'), $begin);
        }
        return $begin;
    }

    /**
     * @return HM_Date|mixed|string
     * @throws Zend_Date_Exception
     */
    public function _getEndPlanify()
    {
        $end = new HM_Date($this->end_date);
        $end = $end->toString(Zend_Date::DATES);

        if (!empty($this->end_date)) {
            // уже стартовала
            if ($this->state != HM_At_Session_SessionModel::STATE_CLOSED) {
                $end = sprintf(_('Дата окончания определяется менеджером (ориентировочно: %s)'), $end);
            } // уже закончилась
            else {
                $end = _('Дата окончания: %s', $end);
            }
        }

        return $end;
    }

    public static function getErrorMessage($code)
    {
        $codes = [
            self::MSG_NO_ANY_POSITIONS => _(''),
            self::MSG_NO_ANY_PROFILES => _(''),
            self::MSG_VACANCT_POSITION => _('Свободная должность у следующих работников'),
            self::MSG_INVALID_PROFILE => _('Отсутствует профиль должности у следующих работников'),
            self::MSG_INVALID_EVALUATION => _(''),
            self::MSG_INVALID_POSITION_DATE => _('Не достигнут минимальный стаж у следующих работников'),
            self::MSG_MAIN_WORK_PLACE => _(''),
            self::MSG_TOP_MANAGERS => _('Следющие частники добавлены в исключения'),
            self::MSG_BLOCKED_USER => _('Следующие ользователи заблокированы'),
            self::MSG_BLOCKED_POSITION => _('Должность заблокирована у следующих работников')
        ];

        return $codes[$code];
    }
}