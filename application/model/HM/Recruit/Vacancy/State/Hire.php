<?php

class HM_Recruit_Vacancy_State_Hire extends HM_State_Abstract
{
    public function isNextStateAvailable() 
    {
        return true;
    }
    
    public function onNextState()
    {
        return true;
    }

    public function getActions() 
    {
        $actions = array();
        $params = $this->getParams();
        
        if (in_array($this->getStatus(), array(HM_State_Abstract::STATE_STATUS_CONTINUING, HM_State_Abstract::STATE_STATUS_PASSED))) {
            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'vacancy', 'controller' => 'report', 'action' => 'user-selected', 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'), 
                    'title' => _('Просмотреть индивидуальный отчет  выбранного кандидата')
                ), 
                array(), 
                $this
            );
        }
                
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'vacancy', 'controller' => 'index', 'action' => 'hire', 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'), 
                    'title' => _('Завершить сессию подбора')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                ), 
                $this,
                HM_State_Action::DECORATE_SUCCESS
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
        return _('На этом этапе выбранный кандидат проходит процедуру трудоустройства.'); 
    }
    
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

}
