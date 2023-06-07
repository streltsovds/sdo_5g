<?php

class HM_Hr_Reserve_State_Plan extends HM_Hr_Reserve_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'hr',
                    'module' => 'reserve',
                    'controller' => 'list',
                    'action' => 'publish',
                    'state' => HM_Hr_Reserve_ReserveModel::STATE_ACTUAL,
                    'reserve_id' => $params['reserve_id']
                ),
                'title' => _('Перейти к оценке выполнения задач'),
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'hr',
                    'module' => 'reserve',
                    'controller' => 'list',
                    'action' => 'abort',
                    'state' => HM_Hr_Reserve_ReserveModel::STATE_CLOSED,
                    'reserve_id' => $params['reserve_id']
                ),
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
        return _('На этом этапе пользователь выполняет задачи из ИПР.');
    }
}
