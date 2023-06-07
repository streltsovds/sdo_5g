<?php

/**
 *
 */
class HM_User_DataGrid_MassAction_AssignRole extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(false,true);
        unset($roles[HM_Role_Abstract_RoleModel::ROLE_USER]);
        unset($roles[HM_Role_Abstract_RoleModel::ROLE_STUDENT]);

        $assignableRoles = array();
        foreach ($roles as $key => $role) {
            if (($key !== 'ISNULL') && !$dataGrid->getServiceContainer()->getService('Acl')->inheritsRole($key, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
                if (in_array($key, array(HM_Role_Abstract_RoleModel::ROLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN)) &&
                    $dataGrid->getServiceContainer()->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN) {
                    continue;
                }
                $assignableRoles[$key] = $role;
            }
        }

        $self = parent::create($dataGrid, $name, $options);
        $self->setName($name);
        $self->setUrl(['action' => 'assign']);
        $self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        $self->setSub(array(
            'function' => self::SUB_MASS_ACTION_SELECT,
            'params'   => array(
                'url'     => $dataGrid->getView()->url($self->getUrl()),
                'name'    => 'role',
                'options' => $assignableRoles
            )
        ));

        return $self;
    }
}