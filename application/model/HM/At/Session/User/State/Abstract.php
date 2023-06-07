<?php

class HM_At_Session_User_State_Abstract extends HM_State_Programm_Abstract
{
    public function isNextStateAvailable() { return true; }
    public function onNextState() { return true; }
    public function getDescription() {}
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}
    
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
        
        if (
            (!in_array($this->_process->getStatus(), array(HM_Process_Abstract::PROCESS_STATUS_FAILED, HM_Process_Abstract::PROCESS_STATUS_COMPLETE))) &&
            (($this->_processAbstract->isStrict() && $this->isCurrent()) || 
            (!$this->_processAbstract->isStrict()) && ($this->getStatus() != HM_State_Abstract::STATE_STATUS_PASSED))) {

//             $actions[] = new HM_State_Action_Link(array(
//                     'url' => array('module' => 'message', 'controller' => 'send', 'action' => 'index', 'MID' => $params['user_id'], 'baseUrl' => ''), 
//                     'title' => _('Отправить сообщение')
//                 ), array(
//                     'roles' => array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL)
//                 ), 
//                 $this
//             );
            
            if ($method != HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE) {
                
                $actions[] = new HM_State_Action_Link(array(
                        'url' => array(
                            'module' => 'session', 
                            'controller' => 'user', 
                            'action' => 'skip-event', 
                            'session_id' => $params['session_id'], 
                            'session_user_id' => $params['session_user_id'], 
                            'programm_event_id' => $this->_programmEventId
                        ), 
                        'title' => _('Завершить этот этап')
                    ), array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL)
                    ), 
                    $this,
                    HM_State_Action::DECORATE_NEXT
                );
            }
                        
            // $actions[] = new HM_State_Action_Link(array(
            //         'url' => array('module' => 'candidate', 'controller' => 'assign', 'action' => 'unassign', 'vacancy_id' => $params['vacancy_id'], 'vacancy_candidate_id' => $params['vacancy_candidate_id']), 
            //         'title' => _('Удалить из списка кандидатов')
            //     ), array(
            //         'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
            //     ), 
            //     $this,
            //     HM_State_Action::DECORATE_FAIL
            // );
        } elseif ($this->getStatus() == HM_State_Abstract::STATE_STATUS_PASSED) {
            
        }
        
        return $actions;
    }    
    
    
    public function isImpossible()
    {
        // если этот programmEvent не передали в params при старте
        // значит нужно пропустить шаг
        return empty($this->_params['session_events']); 
    }
    
    // дублируется в HM_Recruit_Vacancy_Assign_State_Abstract    
    public function getDates()
    {
        $return = array();
        
        if (isset($this->_params['session_events'])) {
            foreach ($this->_params['session_events'] as $event) {
                if ($event['date_begin']) {
                    $date = new HM_Date($event['date_begin']);
                    $return[] = $date->toString('dd.MM.Y');
                }
                if ($event['date_end']) {
                    $date = new HM_Date($event['date_end']);
                    $return[] = $date->toString('dd.MM.Y');
                }
                break; // у всех even'ов одинаковые даты
            }
        }
        return $return;
    }    
}
