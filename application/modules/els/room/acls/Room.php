<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_Room extends HM_ControllerAcl
{
    protected $_module = 'room';
    protected $_controller = 'list';

    protected function _init()
    {
        $this->_allow('new', array(
            Roles::ROLE_DEAN
        ));

        $this->_allow('edit', array(
            Roles::ROLE_DEAN
        ));

        $this->_allow('delete', array(
            Roles::ROLE_DEAN
        ));

        $this->_allow('delete-by', array(
            Roles::ROLE_DEAN
        ));
    }
}