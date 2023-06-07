<?php
class HM_Acl_Offline extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'offline', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'offline', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'offline', 'list', 'import');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'offline', 'list', 'download');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'offline', 'export', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_STUDENT, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_USER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'offline', 'export', 'download');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_STUDENT, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_USER, $resource);

    }
}