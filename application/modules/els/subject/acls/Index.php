<?php

class HM_Acl_Index extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        foreach (['index', 'courses'] as $action) {

            $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', $action);
            if (!$acl->has($resource)) {
                $acl->addResource(new Zend_Acl_Resource($resource));
            }

            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        }

        foreach (['changemode', 'change-state', 'edit-services', 'course-delete', 'course-delete-by'] as $action) {

            $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', $action);
            if (!$acl->has($resource)) {
                $acl->addResource(new Zend_Acl_Resource($resource));
            }

            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        }

        foreach (['assign', 'unassign'] as $action) {

            $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', $action);
            if (!$acl->has($resource)) {
                $acl->addResource(new Zend_Acl_Resource($resource));
            }

            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

            $acl->deny(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        }
    }
}