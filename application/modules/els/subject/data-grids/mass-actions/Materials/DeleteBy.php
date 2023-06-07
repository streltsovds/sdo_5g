<?php

/**
 *
 */
class HM_Subject_DataGrid_MassAction_Materials_DeleteBy extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $serviceContainer = $dataGrid->getServiceContainer();

        /**
         * todo: какие роли?
         * Или лучше это вынести в @see HM_Subject_DataGrid_MaterialsDataGrid::setRoleRestrictions ?
         */
        if ($serviceContainer->getService('Acl')->inheritsRole(
            $serviceContainer->getService('User')->getCurrentUserRole(),
            array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                HM_Role_Abstract_RoleModel::ROLE_CURATOR)
        )) {

            $url = array('module' => 'subject', 'controller' => 'material', 'action' => 'delete-by');

            // если будет грид материалов вне курса - удалить
            $url['subject_id'] = $options['subject_id'];
            $self = parent::create($dataGrid, $name, $options);
            $self->setUrl($url);
            $self->setConfirm(_('Вы действительно желаете удалить отмеченные материалы? Если на их основе созданы занятия, они будут автоматически преобразованы в занятия без материала (очные); оценки слушателей сохранятся. Продолжить?'));

            return $self;
        }
    }
}