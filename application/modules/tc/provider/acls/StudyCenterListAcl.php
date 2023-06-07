<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_StudyCenterListAcl extends HM_ControllerAcl
{
    protected $_module     = 'provider';
    protected $_controller = 'study-center';

    protected function _init()
    {
//        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'index');
//        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow();
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
        ));

        $this->_allow('new', array(
            Roles::ROLE_DEAN,
        ));

        $this->_allow('edit', array(
            Roles::ROLE_DEAN,
        ));

        $this->_allow('delete', array(
            Roles::ROLE_DEAN,
        ));

        $this->_allow('delete-by', array(
            Roles::ROLE_DEAN,
        ));
    }
}
?>