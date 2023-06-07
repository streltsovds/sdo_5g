<?php

class HM_Acl_Subject extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('privileges:%s', 'gridswitcher');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        }
    }
}