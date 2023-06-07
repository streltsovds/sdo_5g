<?php

class HM_Recruit_Vacancy_State_Assessment extends HM_State_Abstract
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
                
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Text(array(
                    'title' => _('Переход на следующий этап происходит автоматически при выборе одного из кандидатов')
                ), 
                array(), 
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
        return _('На этом этапе все отобранные кандидаты проходят оценочные мероприятия, включенные в программу подбора; не исключается поиск и  включение  новых кандидатов.'); 
    }
    
    public function getFailMessage()
    {
        return _('Сессия подбора отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }
    
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

}
