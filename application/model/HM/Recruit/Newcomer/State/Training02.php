<?php
class HM_Recruit_Newcomer_State_Training02 extends HM_Recruit_Newcomer_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'recruit',
                    'module' => 'newcomer',
                    'controller' => 'list',
                    'action' => 'change-state',
                    'state' => HM_State_Abstract::STATE_STATUS_CONTINUING,
                    'newcomer_id' => $params['newcomer_id']
                ),
                'title' => _('Перейти к следующему этапу'),
                'confirm' => _('Вы действительно подтверждаете факт прохождения первичного инструктажа пользователем?'),
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'recruit',
                    'module' => 'newcomer',
                    'controller' => 'list',
                    'action' => 'change-state',
                    'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                    'newcomer_id' => $params['newcomer_id']
                ),
                'title' => _('Отменить сессию адаптации')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL)
            ),
                $this,
                HM_State_Action::DECORATE_FAIL
            );

        }
        return $actions;
    }

    public function getDescription()
    {
        return _('На этом этапе пользователь проходит первичный инструктаж на рабочем месте.');
    }
}