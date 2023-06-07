<?php
class HM_Acl_Option extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'option', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'option', 'ad', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'option', 'upload-templates', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);
    }
}
?>