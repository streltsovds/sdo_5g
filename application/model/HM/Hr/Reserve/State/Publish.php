<?php

class HM_Hr_Reserve_State_Publish extends HM_Hr_Reserve_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {
            $reserveFileService = $this->getService('HrReserveFile');
            $reserveFiles = $reserveFileService->fetchAll(array(
                'reserve_id = ?' => $params['reserve_id'],
                'state_type = ?' => HM_Hr_Reserve_File_FileModel::STATE_TYPE_PUBLISH
            ));

            $confirm = false;
            if (!count($reserveFiles)) {
                $confirm = _('В рамках этапа нет прикрепленных файлов. Хотите закрыть этап без скана плана КР?');
            }

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'hr',
                    'module' => 'reserve',
                    'controller' => 'list',
                    'action' => 'result',
                    'state' => HM_Hr_Reserve_ReserveModel::STATE_ACTUAL,
                    'reserve_id' => $params['reserve_id']
                ),
                'title' => _('Перейти к подведению итогов'),
                'confirm' => $confirm
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
            ),
                $this,
                HM_State_Action::DECORATE_SUCCESS
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
        return _('На этом этапе куратор оценивает достижение задач плана КР; план с отметками об исполнении предоставляется менеджеру по персоналу.');
    }
}
