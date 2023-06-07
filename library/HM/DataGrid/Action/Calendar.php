<?php

/**
 *
 */
class HM_DataGrid_Action_Calendar extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name);

        $self->setName($name);

        $self->setUrl([
            'module'     => 'assign',
            'controller' => 'teacher',
            'action'     => 'calendar',
            'switcher'   => 'calendar',
        ]);

        $self->setParams(array('MID'));

        $serviceContainer = Zend_Registry::get('serviceContainer');
        if ($serviceContainer->getService('Acl')->inheritsRole(
            $serviceContainer->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_DEAN) && $options['switcher'])
            return $self;
    }
}