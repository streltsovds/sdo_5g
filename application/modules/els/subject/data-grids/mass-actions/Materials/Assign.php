<?php

/**
 * DEPRECATED!!!
 */
class HM_Subject_DataGrid_MassAction_Materials_Assign extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $serviceContainer = $dataGrid->getServiceContainer();
        $switchers = $dataGrid->getSwitcher();

        // Только для "ресурсов из БЗ"
        if (isset($switchers['all']) && ($switchers['all'] == HM_Subject_DataGrid_MaterialsDataGrid::SWITCHER_ALL)) {

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

                $url = array('module' => 'subject', 'controller' => 'materials', 'action' => 'assign');
                $url['subject_id'] = $options['subject_id'];

                $self = parent::create($dataGrid, $name, $options);
                $self->setUrl($url);
                return $self;
            }
        }
    }
}