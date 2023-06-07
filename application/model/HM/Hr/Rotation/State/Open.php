<?php
class HM_Hr_Rotation_State_Open extends HM_Hr_Rotation_State_Abstract
{
    public function isNextStateAvailable()
    {
        return true;
    }

    public function onNextState()
    {
        return true;
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {
            $rotationFileService = $this->getService('HrRotationFile');
            $rotationFiles = $rotationFileService->fetchAll(array(
                'rotation_id = ?' => $params['rotation_id'],
                'state_type = ?' => HM_Hr_Rotation_File_FileModel::STATE_TYPE_OPEN
            ));

            $confirm = false;
            if (!count($rotationFiles)) {
                $confirm = _('В рамках этапа нет прикрепленных файлов. Хотите закрыть этап без прикреплённой программы ротации?');
            }

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('baseUrl' => 'hr', 'module' => 'rotation', 'controller' => 'list', 'action' => 'plan', 'state' => HM_Hr_Rotation_RotationModel::STATE_ACTUAL, 'rotation_id' => $params['rotation_id']),
                    'title' => _('Зафиксировать план ротации и перейти к его выполнению'),
                    'confirm' => $confirm
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

    public function getForms()
    {
        return array($this->getFilesForm(), $this->getDescriptionForm());
    }

    public function getDescription()
    {
        return _('На этом этапе пользователь совместно с руководителем целевого подразделения составляют план ротации и сдают его менеджеру по персоналу.');
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

}