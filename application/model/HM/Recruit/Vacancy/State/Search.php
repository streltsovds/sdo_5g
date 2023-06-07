<?php

class HM_Recruit_Vacancy_State_Search extends HM_State_Abstract
{
    public function isNextStateAvailable() 
    {
        return true;
    }
    
    public function onNextState()
    {
        return true;
    }

    public function getForms()
    {
        return $this->getDescriptionForm();
    }
    
    public function getActions() 
    {
        $actions = array();
        $params = $this->getParams();
        
        $actions[] = new HM_State_Action_Link(array(
                'url' => array('module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'), 
                'title' => _('Просмотреть список кандидатов ')
            ), 
            array(), 
            $this
        );
                
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'vacancy', 'controller' => 'index', 'action' => 'change-state', 'state' => HM_At_Session_SessionModel::STATE_ACTUAL, 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'), 
                    'title' => _('Стартовать программу и открыть доступ к оценочным формам')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                ), 
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'vacancy', 'controller' => 'index', 'action' => 'change-state', 'state' => HM_At_Session_SessionModel::STATE_CLOSED, 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'), 
                    'title' => _('Отменить сессию подбора')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                ), 
                $this,
                HM_State_Action::DECORATE_FAIL
            );            
        }        
        
        return $actions;
    }
    
    public function getDescription() 
    { 
        return _('На этом этапе происходит поиск кандидатов и их включение в программу подбора.'); 
    }
    
    public function getFailMessage()
    {
        return _('Сессия подбора отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function initMessage() { return 'initMessage'; }
    public function onNextMessage() { return 'onNextMessage'; }
    public function onErrorMessage() { return 'onErrorMessage'; }
    public function getCompleteMessage() { return 'getCompleteMessage'; }

}
