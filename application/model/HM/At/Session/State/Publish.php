<?php

class HM_At_Session_State_Publish extends HM_State_Abstract
{
    public function isNextStateAvailable() { return true; }
    
    public function onNextState(){ return true; }

    public function getActions()
    {
        $actions = [];
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $controlUrl = ['module' => 'session', 'controller' => 'monitoring', 'action' => 'index', 'session_id' => $params['session_id']];

            if (Zend_Registry::get('view')->url($controlUrl) != $_SERVER['REQUEST_URI']) {
                $actions[] = new HM_State_Action_Link([
                    'url' => $controlUrl,
                    'title' => _('Контроль за прохождением')
                ],
                    [],
                    $this
                );
            }

            $actions[] = new HM_State_Action_Link([
                'url' => ['module' => 'session', 'controller' => 'index', 'action' => 'change-state', 'state' => HM_At_Session_SessionModel::STATE_CLOSED, 'session_id' => $params['session_id']],
                'title' => _('Завершить программу оценки и закрыть доступ к оценочным мероприятиям')
            ], [
                'roles' => [HM_Role_Abstract_RoleModel::ROLE_ATMANAGER]
            ],
                $this,
                HM_State_Action::DECORATE_NEXT
            );
        }
        return $actions;

    }

    public function getDescription() 
    {
        return _('На этом этапе пользователи проходят мероприятия, включенные в программы регулярной оценки соответствующих профилей.');
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
