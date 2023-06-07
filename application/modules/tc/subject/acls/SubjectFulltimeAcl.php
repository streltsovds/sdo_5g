<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_SubjectFulltimeAcl extends HM_ControllerAcl
{
    protected $_module = 'subject';
    protected $_controller = 'fulltime';

    protected function _init()
    {
        $this->_allow('index', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

        $this->_allow('new', array(
            Roles::ROLE_DEAN,
        ));

        $this->_allow('edit', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
        ));

        $this->_allow('view', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_TEACHER,
            Roles::ROLE_SUPERVISOR,
            Roles::ROLE_ENDUSER,
        ));

        $this->_allow('delete', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

        $this->_allow('delete-by', array(
            Roles::ROLE_DEAN,
            Roles::ROLE_DEAN_LOCAL,
            Roles::ROLE_SUPERVISOR,
        ));

        $this->_acl->addModuleResources('teacher', 'tc');

    }
}