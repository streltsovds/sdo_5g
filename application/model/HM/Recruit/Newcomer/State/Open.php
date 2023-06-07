<?php
class HM_Recruit_Newcomer_State_Open extends HM_Recruit_Newcomer_State_Abstract
{
    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {
            $newcomerFileService = $this->getService('RecruitNewcomerFile');
            $newcomerFiles = $newcomerFileService->fetchAll(array(
                'newcomer_id = ?' => $params['newcomer_id'],
                'state_type = ?' => HM_Recruit_Newcomer_File_FileModel::STATE_TYPE_OPEN
            ));

            $confirm = false;
            if (!count($newcomerFiles)) {
                $confirm = _('В рамках этапа нет прикрепленных файлов. Хотите закрыть этап без прикреплённого плана адаптации?');
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
                'title' => _('Зафиксировать план адаптации и перейти к его выполнению'),
                'confirm' => $confirm
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
        return _('На этом этапе пользователь совместно с руководителем составляет план адаптации и сдают его менеджеру по адаптации.');
    }

    public function onNextState()
    {
        $params = $this->getParams();

        $newcomerService = Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer');
        $newcomer = $newcomerService->findOne($params['newcomer_id']);

        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $user = $userService->findOne($newcomer->user_id);

        $href = Zend_Registry::get('view')->serverUrl() .
            Zend_Registry::get('view')->url(array(
                'baseUrl' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'print-forms',
                'newcomer_id' => $newcomer->newcomer_id,
            ), null, true);

        $url = '<a href="'.$href.'">'.$href.'</a>';

        $messenger = Zend_Registry::get('serviceContainer')->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_ADAPTING_KPIS,
            array(
                'name_patronymic' => $user->FirstName . ' ' . $user->Patronymic,
                'url' => $url,
                'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
            ),
            'newcomer',
            $newcomer->newcomer_id
        );
        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);

        return true;
    }

}