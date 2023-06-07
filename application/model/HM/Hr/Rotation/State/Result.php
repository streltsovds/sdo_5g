<?php

class HM_Hr_Rotation_State_Result extends HM_Hr_Rotation_State_Abstract
{
    public function isNextStateAvailable() { return true; }
    
    public function onNextState(){ return true; }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                'url' => array('baseUrl' => 'hr', 'module' => 'rotation', 'controller' => 'list', 'action' => 'complete', 'state' => HM_Hr_Rotation_RotationModel::STATE_ACTUAL, 'rotation_id' => $params['rotation_id']),
                'title' => _('Завершить сессию ротации')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                'url' => array('baseUrl' => 'hr', 'module' => 'rotation', 'controller' => 'list', 'action' => 'abort', 'state' => HM_Hr_Rotation_RotationModel::STATE_CLOSED, 'rotation_id' => $params['rotation_id']),
                'title' => _('Отменить сессию ротации')
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
        return _('На этом этапе подводятся итоги сессии ротации.');
    }

    public function initMessage() {}

    public function onNextMessage() {}

    public function onErrorMessage()
    {
        return _("При создании программы ротации возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Программа ротации отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function getCurrentStateMessage()
    {
        return _('В процессе');
    }

    public function getForms()
    {
        return $this->getDescriptionForm();
    }
}
