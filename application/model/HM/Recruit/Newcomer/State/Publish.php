<?php

class HM_Recruit_Newcomer_State_Publish extends HM_Recruit_Newcomer_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();

        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $newcomerFileService = $this->getService('RecruitNewcomerFile');
            $newcomerFiles = $newcomerFileService->fetchAll(array(
                'newcomer_id = ?' => $params['newcomer_id'],
                'state_type = ?' => HM_Recruit_Newcomer_File_FileModel::STATE_TYPE_PUBLISH
            ));

            $confirm = false;
            if (!count($newcomerFiles)) {
                $confirm = _('В рамках этапа нет прикрепленных файлов. Хотите закрыть этап без скана плана адаптации?');
            }

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
                'confirm' => $confirm
            ), array(
                'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR)
            ),
                $this,
                HM_State_Action::DECORATE_SUCCESS
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
        return _('На этом этапе руководитель пользователя оценивает достижение задач плана адаптации; план с отметками об исполнении предоставляется менеджеру по адаптации.');
    }
}
