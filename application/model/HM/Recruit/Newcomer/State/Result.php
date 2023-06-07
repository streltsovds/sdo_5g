<?php

class HM_Recruit_Newcomer_State_Result extends HM_Recruit_Newcomer_State_Abstract
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
                    'action' => 'complete',
                    'state' => HM_Recruit_Newcomer_NewcomerModel::STATE_ACTUAL,
                    'newcomer_id' => $params['newcomer_id']
                ),
                'title' => _('Завершить сессию адаптации')
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
            ),
                $this,
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'recruit',
                    'module' => 'newcomer',
                    'controller' => 'list',
                    'action' => 'abort',
                    'state' => HM_State_Abstract::STATE_STATUS_FAILED,
                    'newcomer_id' => $params['newcomer_id']
                ),
                'title' => _('Отменить сессию адаптации')
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
        return _('На этом этапе подводятся итоги программы адаптации и принимается решение о завершении испытательного срока.');
    }

}
