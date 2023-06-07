<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_SessionEducationAcl extends HM_ControllerAcl
{
    protected $_module     = 'session';
    protected $_controller = 'education';

    protected function _init()
    {
        $this->_allow('required', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('recomended', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('additional', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

        $this->_allow('edit', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('apply', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));
    }
}
