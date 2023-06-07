<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_SessionQuarterSubjectAcl extends HM_ControllerAcl
{
    protected $_module     = 'session-quarter';
    protected $_controller = 'subject';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

    }
}
