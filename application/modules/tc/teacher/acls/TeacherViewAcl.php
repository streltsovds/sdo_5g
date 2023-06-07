<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_TeacherViewAcl extends HM_ControllerAcl
{
    protected $_module     = 'teacher';
    protected $_controller = 'view';

    protected function _init()
    {
        $this->_allow('card', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));
    }
}