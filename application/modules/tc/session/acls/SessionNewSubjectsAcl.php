<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_SessionNewSubjectsAcl extends HM_ControllerAcl
{
    protected $_module     = 'session';
    protected $_controller = 'new-subjects';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('concatenation', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));
    }
}
