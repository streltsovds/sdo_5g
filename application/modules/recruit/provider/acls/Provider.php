<?php
class HM_Acl_Provider extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'provider', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
    }
}
?>