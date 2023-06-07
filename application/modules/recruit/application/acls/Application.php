<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_Application extends HM_ControllerAcl
{
    protected $_module = 'application';
    protected $_controller = 'list';

    protected function _init()
    {
        $this->_allow('new', array(
            Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('edit', array(
            Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('delete', array(
            Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR
        ));

        $this->_allow(array(
            'vacancy',
            'list',
            'create-from-application',
        ), array(
            Roles::ROLE_HR, Roles::ROLE_HR_LOCAL
        ));

        $this->_allow('take-to-work', array(
            Roles::ROLE_HR, Roles::ROLE_HR_LOCAL
        ));
    }
}