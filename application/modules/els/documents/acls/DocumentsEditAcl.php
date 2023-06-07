<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl_DocumentsEditAcl extends HM_ControllerAcl
{
    protected $_module     = 'documents';
    protected $_controller = 'edit';

    protected function _init()
    {
        $this->_allow('activities-assessment', array(
            Roles::ROLE_ATMANAGER,
            //Roles::ROLE_ATMANAGER_LOCAL,
        ));

    }
}
?>