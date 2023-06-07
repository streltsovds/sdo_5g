<?php

/**
 *
 */
class HM_DataGrid_Action_LoginAs extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        if ($serviceContainer->getService('Acl')->inheritsRole(
            $serviceContainer->getService('User')->getCurrentUserRole(),
            [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_ADMIN])) {

            $self = parent::create($dataGrid, $name);

            $self->setName($name);

            $self->setUrl($options['url']);

            $self->setParams(array('MID'));

            $self->setConfirm(_('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?'));

            return $self;
        }
    }
}