<?php

class HM_Role_Claimant_State_Abstract extends HM_State_Programm_Abstract
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
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->findDependence('Agreement', $this->getProgrammEventId()))) {
            $programmEvent = $collection->current();
        }
        
        if (
            (!in_array($this->_process->getStatus(), array(HM_Process_Abstract::PROCESS_STATUS_FAILED, HM_Process_Abstract::PROCESS_STATUS_COMPLETE))) &&
            (($this->_processAbstract->isStrict() && $this->isCurrent()) ||
            (!$this->_processAbstract->isStrict()) && ($this->getStatus() != HM_State_Abstract::STATE_STATUS_PASSED))) {

            $claimant = Zend_Registry::get('serviceContainer')->getService('Claimant')->find($params['claimant_id'])->current();
            $userId = $claimant->MID;

            $agreements = $programmEvent->agreement;
            $agreement = $agreements->current();
            $userIds = array();
            if ($agreement->agreement_type == HM_Agreement_AgreementModel::AGREEMENT_TYPE_SUPERVISOR) {

                $org = $this->getService('Orgstructure')->fetchAll(
                    $this->getService('Orgstructure')->quoteInto('mid = ?', $userId))->current();

                $collection = $this->getService('Orgstructure')->fetchAll(array(
                        'owner_soid = ?' => $org->owner_soid,
                        'is_manager = ?' => 1,
                    ));

                foreach($collection as $position) {
                    $userIds[] = $position->mid;
                }
            }
            if ($agreement->agreement_type == HM_Agreement_AgreementModel::AGREEMENT_TYPE_SUPERSUPERVISOR) {

                $parents = $this->getService('Orgstructure')->fetchAllDependence('Parent',
                    $this->getService('Orgstructure')->quoteInto('mid = ?', $userId))->current();

                $org = $parents->parent->current();

                $collection = $this->getService('Orgstructure')->fetchAll(array(
                    'owner_soid = ?' => $org->owner_soid,
                    'is_manager = ?' => 1,
                ));

                foreach($collection as $position) {
                    $userIds[] = $position->mid;
                }
            }
            if ($agreement->agreement_type == HM_Agreement_AgreementModel::AGREEMENT_TYPE_DEAN) {
                $select =  $this->getService('Dean')->getSelect();
                $select->from(
                    array('d' => 'deans'),
                    array('DISTINCT(MID)')
                );
                $select->joinLeft(
                    array('r' => 'responsibilities'),
                    'd.MID = r.user_id',
                    array()

                );
                $select->where('r.item_id = ?', $params['subject_id']);
                $select->where('r.item_type = ?', HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT );
                $select->orWhere('r.responsibility_id IS NULL');

                $deans = $select->query()->fetchAll();
                foreach ($deans as $deanId) {
                    $userIds[] = $deanId['MID'];
                }
            }

            if ($agreement->position_id) {
                $position = $this->getService('Orgstructure')->fetchAll(array(
                    'soid = ?' => $agreement->position_id,
                ))->current();
                $userIds[] = $position->mid;

            }

            if ($userIds) {
                $actions[] = new HM_State_Action_Link(array(
                        'url' => array('module' => 'message', 'controller' => 'send', 'action' => 'index', 'MID' => implode(',',$userIds), 'baseUrl' => ''),
                        'title' => _('Отправить сообщение')
                    ), array(
                        'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN)
                    ),
                    $this
                );

            }

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'module' => 'order',
                    'controller' => 'index',
                    'action' => 'skip-event',
                    'subject_id' => $params['subject_id'],
                    'claimant_id' => $params['claimant_id'],
                    'programm_event_id' => $this->_programmEventId
                ),
                'title' => _('Пропустить этап')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );
                        
            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'module' => 'order', 
                    'controller' => 'index', 
                    'action' => 'fail', 
                    'subject_id' => $params['subject_id'],
                    'claimant_id' => $params['claimant_id'],
            ), 
                'title' => _('Отклонить заявку')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_DEAN)
            ), 
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }
                
        return $actions;
    }    
    
    public function getDates()
    {
        $return = array();
        
//         if (isset($this->_params['session_events'])) {
//             foreach ($this->_params['session_events'] as $event) {
//                 if ($event['date_begin']) {
//                     $date = new HM_Date($event['date_begin']);
//                     $return[] = $date->toString('dd.MM.Y');
//                 }
//                 if ($event['date_end']) {
//                     $date = new HM_Date($event['date_end']);
//                     $return[] = $date->toString('dd.MM.Y');
//                 }
//                 break; // у всех even'ов одинаковые даты
//             }
//         }
        return $return;
    }    
}
