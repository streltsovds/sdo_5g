<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 29.10.2014
 * Time: 16:30
 */

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_ApplicationListAcl extends HM_ControllerAcl {

    protected $_module     = 'application';
    protected $_controller = 'list';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

        $this->_allow('delete', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

        $this->_allow('delete-by', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('set-cost-item-by', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));
    }
} 