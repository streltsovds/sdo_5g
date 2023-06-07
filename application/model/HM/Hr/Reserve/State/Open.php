<?php
class HM_Hr_Reserve_State_Open extends HM_Hr_Reserve_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {
            $reserveFileService = $this->getService('HrReserveFile');
            $reserveFiles = $reserveFileService->fetchAll(array(
                'reserve_id = ?' => $params['reserve_id'],
                'state_type = ?' => HM_Hr_Reserve_File_FileModel::STATE_TYPE_OPEN
            ));

            $confirm = false;
            if (!count($reserveFiles)) {
                $confirm = _('В рамках этапа нет прикрепленных файлов. Хотите закрыть этап без прикреплённого плана КР?');
            }

            $actions[] = new HM_State_Action_Link(array(
                'url' => array(
                    'baseUrl' => 'hr',
                    'module' => 'reserve',
                    'controller' => 'list',
                    'action' => 'plan',
                    'state' => HM_Hr_Reserve_ReserveModel::STATE_ACTUAL,
                    'reserve_id' => $params['reserve_id']
                ),
                'title' => _('Зафиксировать план КР и перейти к его выполнению'),
                'confirm' => $confirm
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
        return _('На этом этапе пользователь совместно с куратором составляет план ИПР и сдают его менеджеру по персоналу.');
    }
}