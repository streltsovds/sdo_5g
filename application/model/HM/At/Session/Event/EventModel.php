<?php
/**
 * Старый код из pm
 * Общая логика для простых видов оценки, которые запускаются через event/index/run
 */
class HM_At_Session_Event_EventModel extends HM_Model_Abstract implements HM_Quest_Context_Interface
{
    const STATUS_PLANNED   = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_IN_PROGRESS   = 2;
    
    const PARTICIPANT_TYPE_USER = 1;
    const PARTICIPANT_TYPE_RESPONDENT = 2;
    
    // возможные формы представления event'ов
    const FORM_SCREEN = 'screen'; // экранная форма, куда вводят данные; её видит только сам респондент
    const FORM_REPORT = 'report'; // экранная форма, показывает прогресс и текущий результат; её видят все кроме респондента; после финализации - и респондент тоже 
    const FORM_SCREEN_READONLY = 'screen-readonly'; // привет, филиппморис
    const FORM_PRINT = 'print'; // печатная форма, подробный отчет о заполнении 
    const FORM_PAPER = 'paper'; // пустая печатная форма, чотбы провести анкетирование на бумаге
    
    public $results;

    protected $_primaryName = 'session_event_id';
    
    public function getServiceName()
    {
        return 'AtSessionEvent';
    } 
        
    /**
     * Возвращает массив текстовых обозначений статусов
     * @static
     * @return array
     */
    static public function getStatuses()
    {
        return array(
            self::STATUS_PLANNED   => _('Не заполнена'),
            self::STATUS_IN_PROGRESS => _('В процессе'),
            self::STATUS_COMPLETED => _('Заполнена'),
        );
    }

    /**
     * Возвращает наименование статуса по его ключу
     * Если ключ некорректный - возвращает пустую строку
     * @static
     * @param $statusKey
     * @return string
     */
    static public function getStatusName($statusKey)
    {
        $statuses = self::getStatuses();
        if (!array_key_exists($statusKey,$statuses)) return '';
        return $statuses[$statusKey];
    }

    static public function factory($data, $default = 'HM_At_Session_Event_EventModel')
    {

        if (isset($data['method']))
        {
            switch($data['method']) {
                case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_CompetenceModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_KPI:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_KpiModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_RATING:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_RatingModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_AUDIT:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_AuditModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_FIELD:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_FieldModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_TestModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_PsychoModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_FORM:
                    $event = parent::factory($data, 'HM_At_Session_Event_Method_FormModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE:
                    // @todo: нехорошо здесь лезть в базу
                    $programmType = HM_Programm_ProgrammModel::TYPE_ADAPTING;
                    $programmEventUserService = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser');
                    if ($collection = $programmEventUserService->findDependence('Programm', $data['programm_event_user_id'])) {
                        $programmEventUser = $collection->current();
                        if (!empty($programmEventUser->programm) and count($programmEventUser->programm)) {
                            $programmType = $programmEventUser->programm->current()->programm_type;
                        }
                    }
                    switch ($programmType) {
                        case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                            $event = parent::factory($data, 'HM_At_Session_Event_Method_Form_Finalize_RecruitModel');
                        break;
                        case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                            $event = parent::factory($data, 'HM_At_Session_Event_Method_Form_Finalize_AdaptingModel');
                        break;
                        case HM_Programm_ProgrammModel::TYPE_RESERVE:
                            $event = parent::factory($data, 'HM_At_Session_Event_Method_Form_Finalize_ReserveModel');
                        break;
                    }
                    break;
            }
            $event->init();
            return $event;
        }
        return parent::factory($data, $default);        
    }

    public function getMemo($memoInternalId)
    {
        $memos = $this->getMemos();
        if (array_key_exists($memoInternalId, $memos)) {
            return $memos[$memoInternalId];
        }
        return '';
    }

    public function getMemoValue($memoInternalId)
    {
        if (!count($this->evaluationMemos)) return false;
		foreach ($this->evaluationMemos as $memo) {
            if ($memo->memo_internal_id == $memoInternalId) return $memo->value;
        }
        return false;
    }

    public function getsubmethod($key)
    {
        if (!empty($this->submethod)) {
            $data = unserialize($this->submethod);
            if (isset($data[$key])) {
                return $data[$key];
            }
        }
        return false;
    }
    
    public function isMultipage()
    {
        return false;
    }
    
    public function getIcon()
    {
        if (isset($this->user) && count($this->user)) {
            return $this->user->current()->getPhoto();
        }
        return false;
    }

    protected function _savesubmethod($data)
    {
        Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->updateWhere(array(
            'submethod' => serialize($data)
        ), array('session_event_id = ?' => $this->session_event_id));
        return true;
    }
    
    public function isExecutable()
    {
        $return = true;
        $programm = count($this->programm)? $this->programm->current() : false;
        $programmEventUser = count($this->programmEventUser)? $this->programmEventUser->current() : false;
        $session = count($this->session)? $this->session->current() : false;
        $sessionUser = count($this->sessionUser)? $this->sessionUser->current() : false;

        if ($session->programm_type==HM_Programm_ProgrammModel::TYPE_ADAPTING) {

            // Исключение для сессии адаптации - здесь своя логика разрешений
            // Методов для процесса толковых не нашел - забираем прямо с базы
            $sc = Zend_Registry::get('serviceContainer');
            $result = $sc->getService('State')->getOne($sc->getService('State')->fetchAll(array(
                'process_type = ?'=>HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING,
                'item_id = ?'=>$sessionUser->newcomer_id
            )));

            switch($result->current_state){
                case 'HM_Recruit_Newcomer_State_Publish':
                    return get_class($this) == 'HM_At_Session_Event_Method_KpiModel';
                    break;
                case 'HM_Recruit_Newcomer_State_Result':
                    return get_class($this) == 'HM_At_Session_Event_Method_Form_Finalize_AdaptingModel' &&
                        $sc->getService('Acl')->inheritsRole($sc->getService('User')->getCurrentUserRole(), array(
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                        ));
                    break;
                default:
                    return false;
            }
        } elseif ($session->programm_type==HM_Programm_ProgrammModel::TYPE_RESERVE) {

            // Исключение для сессии КР - здесь своя логика разрешений
            // Методов для процесса толковых не нашел - забираем прямо с базы
            $sc = Zend_Registry::get('serviceContainer');
            $result = $sc->getService('State')->getOne($sc->getService('State')->fetchAll(array(
                'process_type = ?'=>HM_Process_ProcessModel::PROCESS_PROGRAMM_RESERVE,
                'item_id = ?'=>$sessionUser->reserve_id
            )));

            switch($result->current_state){
                case 'HM_Hr_Reserve_State_Publish':
                    return true;
                    break;
                case 'HM_Hr_Reserve_State_Result':
                    return get_class($this) == 'HM_At_Session_Event_Method_Form_Finalize_ReserveModel' &&
                        $sc->getService('Acl')->inheritsRole($sc->getService('User')->getCurrentUserRole(), array(
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                        ));
                    break;
                default:
                    return false;
            }
        } else {

            if (
                ($this->respondent_id != Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()) &&
                Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                    HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
                ))
            ) {
                // рядовые пользователи могут заполнять только свои анкет - где он указан в качестве respondent_id
                $return = false;
            } elseif (!$session || ($session->state != HM_At_Session_SessionModel::STATE_ACTUAL)) {
                // только если сессия (оценки, подбора, адаптации,..) активна
                $return = false;
            } elseif (!$sessionUser || ($sessionUser->status == HM_At_Session_User_UserModel::STATUS_COMPLETED)) {
                // только если учасник в процессе (может не совпадать со статусом сессии)
                // например, досрочное завершение рег.оценки или отклонённый кандидат,..
                $return = false;
            } elseif (!$programm || !$programmEventUser || (
                    ($programm->mode_strict == HM_Programm_ProgrammModel::MODE_STRICT_ON) &&
                    ($programmEventUser->status != HM_Programm_Event_User_UserModel::STATUS_CONTINUING)
                )) {
                // в случае последовательно режима прохождении программы -
                // только если это текущий этап (синий прямоугольник в БП)

                // кроме сессий адаптации, т.к. там нет программы вообще (статичный процесс вместо программы)
                if ($session->programm_type != HM_Programm_ProgrammModel::TYPE_ADAPTING) {
                    $return = false;
                }
            }

            if ($this->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED
                || $this->status == HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS
            ) {
                // если все проверки выше прошли успешно,
                // можно разрешить также повторное выполнение тестов
                // проверка количества попыток и т.п. происходит в HM_At_Session_Event_Method_TestModel
                return $return || ($this->method == HM_At_Evaluation_EvaluationModel::TYPE_TEST);
            } else {
                return $return;
            }
        }
    }
    
    public function getCurrentProgrammEvent()
    {
        if (
            ($this->respondent_id != Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()) &&
            Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))
        ) {
            return false;
        } elseif ($this->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) {
            return false;
        } elseif (count($this->programmEventUser)) {
            if (count($this->programm)) {
                $programm = $this->programm->current();
                $programmEventUser = $this->programmEventUser->current();
                if ($programm->mode_strict == HM_Programm_ProgrammModel::MODE_STRICT_ON) {
                    if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->fetchAllDependence('ProgrammEvent', array(
                        'programm_id = ?' => $programmEventUser->programm_id,       
                        'user_id = ?' => $programmEventUser->user_id,       
                        'status = ?' => HM_Programm_Event_User_UserModel::STATUS_CONTINUING,       
                    )))) {
                        $programmEventUser = $collection->current();
                        if (count($programmEventUser->programmEvent)) {
                            return $programmEventUser->programmEvent->current();
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function isReportAvailable()
    {
        if (
            Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) &&
            ($this->respondent_id != Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId())
        ) {
            return false;
        } 
        return true;
    }    
    
    public function getQuestContext()
    {
        return array(
            'context_type' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT, 
            'context_event_id' => $this->session_event_id
        );
    }
    
    public function saveQuestResults($questAttempt)
    {
        return true;
    }
    
    public function getMessages()
    {
        $return = [];
//        if (count($this->respondent) && ($this->respondent_id != Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId())) {
//                $return[] = _('Заполняет') . ': ' . $this->respondent->current()->getName();
//        }
        if (!$this->isExecutable() && ($currentEvent = $this->getCurrentProgrammEvent())) {
            // если пока нельзя заполнить - показываем почему нельзя
            $return[] = _('Предусловие') . ': ' . $currentEvent->name;
        }

        return $return;
    }

    public function getRedirectUrl()
    {
        $sc = Zend_Registry::get('serviceContainer');

        if (count($this->session)) $session = $this->session->current();
        // отдельный URL для сессий подбора
        if ($session && ($session->programm_type == HM_Programm_ProgrammModel::TYPE_RECRUIT)) {
            $collection = $sc->getService('RecruitVacancy')->fetchAll(array('session_id = ?' => $session->session_id));
            if (count($collection)) {
                $vacancy = $collection->current();
                return Zend_Registry::get('view')->url(array(
                    'module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'baseUrl' => 'recruit', 'vacancy_id' => $vacancy->vacancy_id)
                );
            }
        }

        // дефолтный URL для оценочных сессий
        $action = $sc->getService('Acl')->inheritsRole(
            $sc->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';
        return Zend_Registry::get('view')->url(array('module' => 'session', 'controller' => 'event', 'action' => $action, 'baseUrl' => 'at', 'session_id' => $this->session_id));
    }
}