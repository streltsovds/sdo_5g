<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_ProviderListAcl extends HM_ControllerAcl
{
    protected $_module     = 'provider';
    protected $_controller = 'list';

    protected function _init()
    {
//        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'index');
//        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow();
        $this->_allow('index', array(
            Roles::ROLE_SUPERVISOR,
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_HR_LOCAL,
        ));

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

        $this->_allow('approve', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));
    }
}
?>