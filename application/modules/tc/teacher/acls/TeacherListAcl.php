<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_TeacherListAcl extends HM_ControllerAcl
{
    protected $_module     = 'teacher';
    protected $_controller = 'list';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_SUPERVISOR,
            Roles::ROLE_DEAN_LOCAL,
        ));
    }
}