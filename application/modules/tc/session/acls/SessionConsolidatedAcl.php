<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_SessionConsolidatedAcl extends HM_ControllerAcl
{
    protected $_module     = 'session';
    protected $_controller = 'consolidated';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('change-state', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR
        ));

        $this->_allow('change-state-by', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('agreement-by', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('rollback-by', array(
            Roles::ROLE_DEAN,
        ));
    }
}
