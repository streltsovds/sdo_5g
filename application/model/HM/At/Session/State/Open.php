<?php
class HM_At_Session_State_Open extends HM_State_Abstract
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

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'session', 'controller' => 'index', 'action' => 'change-state', 'state' => HM_At_Session_SessionModel::STATE_ACTUAL, 'session_id' => $params['session_id']), 
                    'title' => _('Стартовать программу и открыть доступ к оценочным формам')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)
                ), 
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'session', 'controller' => 'index', 'action' => 'change-state', 'state' => HM_At_Session_SessionModel::STATE_CLOSED, 'session_id' => $params['session_id']), 
                    'title' => _('Отменить сессию оценки')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)
                ), 
                $this,
                HM_State_Action::DECORATE_FAIL
            );            
        }         
        return $actions;
    }

    public function getDescription()
    {
        return _('На этом этапе уточняются параметры оценки, редактируются методики, участвующие в программе регулярной оценки; доступ к оценочной сессии закрыт для пользователей.');
    }

    public function initMessage() {}

    public function onNextMessage() {}

    public function onErrorMessage() 
    {
        return _("При создании сессии возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Сессия оценки отменена');
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