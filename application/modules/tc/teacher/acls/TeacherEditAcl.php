<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_TeacherEditAcl extends HM_ControllerAcl
{
    protected $_module     = 'teacher';
    protected $_controller = 'edit';

    protected function _init()
    {
        $this->_allow('new', array(
            Roles::ROLE_DEAN,
        ));

        $this->_allow('edit', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('delete', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('delete-by', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

    }
}