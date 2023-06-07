<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_DocumentsListAcl extends HM_ControllerAcl
{
    protected $_module     = 'documents';
    protected $_controller = 'list';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_ADMIN,
            Roles::ROLE_ATMANAGER,
            Roles::ROLE_ATMANAGER_LOCAL,
        ));

        $this->_allow('new', array(
            Roles::ROLE_ADMIN,
            Roles::ROLE_ATMANAGER,
            Roles::ROLE_ATMANAGER_LOCAL,
        ));

        $this->_allow('edit', array(
            Roles::ROLE_ADMIN,
            Roles::ROLE_ATMANAGER,
            Roles::ROLE_ATMANAGER_LOCAL,
        ));

        $this->_allow('delete', array(
            Roles::ROLE_ADMIN,
            Roles::ROLE_ATMANAGER,
            Roles::ROLE_ATMANAGER_LOCAL,
        ));

        $this->_allow('delete-by', array(
            Roles::ROLE_ADMIN,
            Roles::ROLE_ATMANAGER,
            Roles::ROLE_ATMANAGER_LOCAL,
        ));

    }
}
?>