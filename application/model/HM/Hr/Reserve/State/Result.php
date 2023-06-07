<?php

class HM_Hr_Reserve_State_Result extends HM_Hr_Reserve_State_Abstract
{
    public function isNextStateAvailable() { return true; }
    
    public function onNextState(){ return true; }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                'url' => array('baseUrl' => 'hr', 'module' => 'reserve', 'controller' => 'list', 'action' => 'complete', 'state_id' => HM_Hr_Reserve_ReserveModel::STATE_ACTUAL, 'reserve_id' => $params['reserve_id']),
                'title' => _('Завершить сессию КР')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                'url' => array('baseUrl' => 'hr', 'module' => 'reserve', 'controller' => 'list', 'action' => 'abort', 'state_id' => HM_Hr_Reserve_ReserveModel::STATE_CLOSED, 'reserve_id' => $params['reserve_id']),
                'title' => _('Отменить сессию КР')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
            ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }
        return $actions;

    }

    public function getDescription()
    {
        return _('На этом этапе подводятся итоги программы КР.');
    }

    public function initMessage() {}

    public function onNextMessage() {}

    public function onErrorMessage()
    {
        return _("При создании программы КР возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Программа КР отменена');
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
