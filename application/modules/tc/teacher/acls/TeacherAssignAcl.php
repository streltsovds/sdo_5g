<?php

use HM_Role_Abstract_RoleModel as Role;

class HM_Acl_TeacherAssignAcl extends HM_ControllerAcl
{
    protected $_module = 'teacher';
    protected $_controller = 'assign';

    protected function _init()
    {
        $this->_allow('index', array(
            Role::ROLE_DEAN,
            Role::ROLE_DEAN_LOCAL,
        ));
    }
}