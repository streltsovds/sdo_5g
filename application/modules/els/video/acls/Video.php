<?php
class HM_Acl_Video extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'get-video');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'get-embedded');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
    }
}