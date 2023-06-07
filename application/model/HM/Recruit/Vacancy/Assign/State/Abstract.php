<?php

class HM_Recruit_Vacancy_Assign_State_Abstract extends HM_State_Programm_Abstract
{
    public function onNextState() { return true; }
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

    public function isNextStateAvailable() { return true; }

    public function getForms()
    {
        return $this->getDescriptionForm();
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->findDependence('Evaluation', $this->getProgrammEventId()))) {
            $programmEvent = $collection->current();
            if (count($programmEvent->evaluation)) {
                $method = $programmEvent->evaluation->current()->method;
            }
        }
        
//         $actions[] = new HM_State_Action_Link(array(
//                 'url' => array('module' => 'vacancy', 'controller' => 'report', 'action' => 'user', 'vacancy_id' => $params['vacancy_id'], 'vacancy_candidate_id' => $params['vacancy_candidate_id']),  
//                 'title' => _('Просмотреть индивидуальный отчет')
//             ), 
//             array(), // всем
//             $this
//         );
        
        if (
            (!in_array($this->_process->getStatus(), array(HM_Process_Abstract::PROCESS_STATUS_FAILED, HM_Process_Abstract::PROCESS_STATUS_COMPLETE))) &&
            (($this->_processAbstract->isStrict() && $this->isCurrent()) || 
            (!$this->_processAbstract->isStrict()) && ($this->getStatus() != HM_State_Abstract::STATE_STATUS_PASSED))) {

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'candidate', 'controller' => 'assign', 'action' => 'message', 'vacancy_id' => $params['vacancy_id'], 'vacancy_candidate_id' => $params['vacancy_candidate_id'], 'programm_event_id' => $this->_programmEventId), 
                    'title' => _('Отправить уведомление [респондентам]')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                ), 
                $this,
                HM_State_Action::DECORATE_INLINE
            );
            
            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'candidate', 'controller' => 'assign', 'action' => 'message', 'initiator' => true, 'vacancy_id' => $params['vacancy_id'], 'vacancy_candidate_id' => $params['vacancy_candidate_id'], 'programm_event_id' => $this->_programmEventId),
                    'title' => _('[инициатору подбора]')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                ),
                $this,
                HM_State_Action::DECORATE_INLINE
            );


            if (is_array($this->_params['session_events']) && count($this->_params['session_events'])) {
                $event = current($this->_params['session_events']);
                if (!$event['is_empty_quest']) {
                    $sessionEventId = $event['session_event_id'];
                }
            }

            if ($sessionEventId && ($event['respondent_id'] == $this->getService('User')->getCurrentUserId())) {
                $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'event', 'controller' => 'index', 'action' => 'index', 'session_event_id' => $sessionEventId, 'baseUrl' => 'at'),
                    'title' => _('Заполнить форму')
                ),
                    array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                    ),
                    $this
                );
            }

            if ($method != HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE) {

                $title = $sessionEventId ? _('Завершить этап без заполнения формы') : _('Завершить этап');
                if ($this->_processAbstract->isStrict()) {
                    if ($isLastState = ('HM_Recruit_Vacancy_Assign_State_Complete' == $this->getNextState())) {
                        $title = _('Рекомендован к зачислению в должность');
                    }
                }
                
                $actions[] = new HM_State_Action_Link(array(
                        'url' => array(
                            'module' => 'candidate', 
                            'controller' => 'assign', 
                            'action' => 'skip-event', 
                            'vacancy_id' => $params['vacancy_id'], 
                            'vacancy_candidate_id' => $params['vacancy_candidate_id'], 
                            'programm_event_id' => $this->_programmEventId
                        ), 
                        'title' => $title
                    ), array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                    ), 
                    $this,
                    $isLastState ? HM_State_Action::DECORATE_SUCCESS : HM_State_Action::DECORATE_NEXT
                );

                $actions[] = new HM_State_Action_Link(array(
                        'url' => array(
                            'module' => 'candidate',
                            'controller' => 'assign',
                            'action' => 'deny',
                            'vacancy_id' => $params['vacancy_id'],
                            'vacancy_candidate_id' => $params['vacancy_candidate_id'],
                            'programm_event_id' => $this->_programmEventId
                        ),
                        'title' => _('Отклонить кандидата [без статуса]')
                    ), array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                    ),
                    $this,
                    HM_State_Action::DECORATE_FAIL_INLINE
                );
                $actions[] = new HM_State_Action_Link(array(
                        'url' => array(
                            'module' => 'candidate',
                            'controller' => 'assign',
                            'action' => 'deny',
                            'vacancy_id' => $params['vacancy_id'],
                            'vacancy_candidate_id' => $params['vacancy_candidate_id'],
                            'programm_event_id' => $this->_programmEventId,
                            'result' => HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_BLACKLIST,
                        ),
                        'title' => _('[в чёрный список]')
                    ), array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                    ),
                    $this,
                    HM_State_Action::DECORATE_INLINE
                );

                $actions[] = new HM_State_Action_Link(array(
                        'url' => array(
                            'module' => 'candidate',
                            'controller' => 'assign',
                            'action' => 'deny',
                            'vacancy_id' => $params['vacancy_id'],
                            'vacancy_candidate_id' => $params['vacancy_candidate_id'],
                            'programm_event_id' => $this->_programmEventId,
                            'result' => HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_RESERVE,
                        ),
                        'title' => _('[в резерв]')
                    ), array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                    ),
                    $this,
                    HM_State_Action::DECORATE_INLINE
                );
            }
                        
//            $actions[] = new HM_State_Action_Link(array(
//                    'url' => array('module' => 'candidate', 'controller' => 'assign', 'action' => 'unassign', 'vacancy_id' => $params['vacancy_id'], 'vacancy_candidate_id' => $params['vacancy_candidate_id']),
//                    'title' => _('Удалить из списка кандидатов')
//                ), array(
//                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
//                ),
//                $this,
//                HM_State_Action::DECORATE_FAIL
//            );
        } elseif ($this->getStatus() == HM_State_Abstract::STATE_STATUS_PASSED) {
            
        }
        
        return $actions;
    }    
    
    // DEPRECATED! все даты хранятся в state_of_process_data
    public function getDates()
    {
        $return = $eventIds = array();

        if (isset($this->_params['session_events'])) {
            foreach ($this->_params['session_events'] as $event) {
                $eventIds[] = $event['session_event_id'];
            }
        }

        // нужно заново взять, т.к. закэшированы даты на момент сооздания
        if (count($eventIds)) {

            $events = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')
                ->fetchAll(array('session_event_id IN (?)' => $eventIds))
                ->asArrayOfArrays();

            foreach ($events as $event) {
                if ($event['date_begin']) {
                    $date = new HM_Date($event['date_begin']);
                    $return[] = $date->toString('dd.MM.Y');
                }
                if ($event['date_end']) {
                    $date = new HM_Date($event['date_end']);
                    $return[] = $date->toString('dd.MM.Y');
                }
            }
        }
        return $return;
    }  

    public function getDescription() 
    {
        if (is_array($this->_params['session_events']) && count($this->_params['session_events'])) {
            $event = current($this->_params['session_events']);
            return $event['description'];
        } 
        return '';
    }

    public function isImpossible()
    {
        // если этот programmEvent не передали в params при старте
        // значит нужно пропустить шаг
        return empty($this->_params['session_events']); 
    }

    public function getFailMessage()
    {
        return _('Кандидат отклонен');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function getCurrentStateMessage()
    {
        return _('В процессе');
    }
}
